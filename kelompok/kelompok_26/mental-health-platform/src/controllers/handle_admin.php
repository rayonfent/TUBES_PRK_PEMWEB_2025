<?php
// Admin handlers used by admin dashboard
// Secure, consistent JSON responses, prepared statements for writes.
// Place this file in controllers/handle_admin.php (same location as your JS expects).
header('Content-Type: application/json; charset=utf-8');

session_start();

require_once dirname(__DIR__) . '/config/database.php';

// Ensure DB connection exists
if (!isset($conn) || !$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'db_connection_missing']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? null;
if (!$action) {
    http_response_code(400);
    echo json_encode(['error' => 'missing_action']);
    exit;
}

// Helper: only allow write actions for logged-in admins
function is_admin_session() {
    return isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'admin';
}

// Helper: safe output for debug (avoid leaking DB error in prod)
function json_error($code, $msg = null) {
    http_response_code($code);
    $out = ['error' => $msg ?? 'error'];
    echo json_encode($out);
    exit;
}

    // helper: insert activity log (best-effort, ignore failure)
    function insert_activity_log($conn, $actor_type, $actor_id, $action, $details = null) {
        if (!$conn) return false;
        $stmt = $conn->prepare("INSERT INTO activity_log (actor_type, actor_id, action, details) VALUES (?,?,?,?)");
        if (!$stmt) return false;
        $stmt->bind_param('siss', $actor_type, $actor_id, $action, $details);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

switch ($action) {
    case 'get_stats':
        // compute stats from the database (real values)
        $out = [
            'total_users' => 0,
            'active_sessions' => 0,
            'active_konselor' => 0,
            'total_messages' => 0,
            'db_connections' => null
        ];

        // total users (count all rows in users)
        if ($r = $conn->query("SELECT COUNT(*) AS cnt FROM users")) {
            $row = $r->fetch_assoc();
            $out['total_users'] = intval($row['cnt'] ?? 0);
            $r->free();
        }

        // active sessions (chat_session.status = 'active')
        if ($r = $conn->query("SELECT COUNT(*) AS cnt FROM chat_session WHERE status = 'active'")) {
            $row = $r->fetch_assoc();
            $out['active_sessions'] = intval($row['cnt'] ?? 0);
            $r->free();
        }

        // active konselor (konselor.online_status = 1)
        if ($r = $conn->query("SELECT COUNT(*) AS cnt FROM konselor WHERE online_status = 1")) {
            $row = $r->fetch_assoc();
            $out['active_konselor'] = intval($row['cnt'] ?? 0);
            $r->free();
        }

        // total messages (chat_message table)
        if ($r = $conn->query("SELECT COUNT(*) AS cnt FROM chat_message")) {
            $row = $r->fetch_assoc();
            $out['total_messages'] = intval($row['cnt'] ?? 0);
            $r->free();
        }

        // current DB connections (best-effort; may require privileges)
        $dbConns = null;
        if ($r = $conn->query("SHOW STATUS LIKE 'Threads_connected'")) {
            $row = $r->fetch_assoc();
            $dbConns = isset($row['Value']) ? intval($row['Value']) : (isset($row['Threads_connected']) ? intval($row['Threads_connected']) : null);
            $r->free();
        }
        $out['db_connections'] = $dbConns;

        echo json_encode($out);
        break;

    case 'get_users':
        // Return real users from DB (id, name, email, role)
        $out = ['users' => []];
        $sql = "SELECT user_id, name, email, role, created_at FROM users ORDER BY user_id DESC";
        if ($res = $conn->query($sql)) {
            while ($row = $res->fetch_assoc()) {
                $out['users'][] = [
                    'id' => intval($row['user_id']),
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'role' => $row['role'],
                    'type' => 'user',
                    'created_at' => $row['created_at']
                ];
            }
            $res->free();
            // also append konselors so admin sees them in the same list
            if ($r2 = $conn->query("SELECT konselor_id, name, email, created_at FROM konselor ORDER BY konselor_id DESC")) {
                while ($kr = $r2->fetch_assoc()) {
                    $out['users'][] = [
                        'id' => intval($kr['konselor_id']),
                        'name' => $kr['name'],
                        'email' => $kr['email'],
                        'role' => 'konselor',
                        'type' => 'konselor',
                        'created_at' => $kr['created_at']
                    ];
                }
                $r2->free();
            }
            echo json_encode($out);
            break;
        } else {
            json_error(500, 'db_error');
        }
        break;

    case 'get_logs':
        // Logs are admin-only
        if (!is_admin_session()) { json_error(403, 'forbidden'); }

        $out = ['logs' => []];
        $sql = "SELECT id, actor_type, actor_id, action, details, created_at FROM activity_log ORDER BY created_at DESC LIMIT 200";
        if ($res = $conn->query($sql)) {
            while ($row = $res->fetch_assoc()) {
                $out['logs'][] = [
                    'id' => intval($row['id']),
                    'actor_type' => $row['actor_type'],
                    'actor_id' => intval($row['actor_id']),
                    'action' => $row['action'],
                    'details' => $row['details'],
                    'created_at' => $row['created_at']
                ];
            }
            $res->free();
            echo json_encode($out);
            break;
        } else {
            json_error(500, 'db_error');
        }
        break;

    case 'ping':
        // lightweight health-check & session presence - useful for debugging
        $out = ['ok' => true, 'session' => null];
        if (isset($_SESSION['user'])) {
            $out['session'] = ['role' => $_SESSION['user']['role'] ?? null, 'user_id' => $_SESSION['user']['user_id'] ?? null, 'email' => $_SESSION['user']['email'] ?? null];
        } elseif (isset($_SESSION['konselor'])) {
            $out['session'] = ['konselor' => true, 'konselor_id' => $_SESSION['konselor']['konselor_id'] ?? null, 'email' => $_SESSION['konselor']['email'] ?? null];
        }
        echo json_encode($out);
        break;

    case 'create_user':
        if (!is_admin_session()) { json_error(403, 'forbidden'); }
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? null; // plain password expected; will be hashed server-side
        $role = $_POST['role'] ?? 'user';

        if ($name === '' || $email === '' || $password === null || $password === '') {
            json_error(400, 'missing_fields');
        }

        // basic email validation (light)
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_error(400, 'invalid_email');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            json_error(500, 'db_prepare_failed');
        }
        $stmt->bind_param('ssss', $name, $email, $hash, $role);
        if ($stmt->execute()) {
            $id = $stmt->insert_id;
            $stmt->close();
            // log admin activity (best-effort)
            $adminId = intval($_SESSION['user']['user_id'] ?? ($_SESSION['user']['id'] ?? 0));
            $details = json_encode(['user_id'=>intval($id),'email'=>$email,'role'=>$role]);
            insert_activity_log($conn, 'admin', $adminId, 'create_user', $details);
            echo json_encode(['success' => true, 'id' => intval($id), 'type' => 'user']);
            break;
        } else {
            $err = $stmt->error;
            $stmt->close();
            json_error(500, 'db_insert_failed');
        }
        break;

    case 'create_konselor':
        if (!is_admin_session()) { json_error(403, 'forbidden'); }
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? null;

        if ($name === '' || $email === '' || $password === null || $password === '') {
            json_error(400, 'missing_fields');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_error(400, 'invalid_email');

        // check exists
        $chk = $conn->prepare("SELECT konselor_id FROM konselor WHERE email = ? LIMIT 1");
        if ($chk) {
            $chk->bind_param('s', $email);
            $chk->execute();
            $res = $chk->get_result();
            if ($res && $res->num_rows > 0) { $chk->close(); json_error(409, 'email_exists'); }
            $chk->close();
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $ins = $conn->prepare("INSERT INTO konselor (name, email, password) VALUES (?,?,?)");
        if (!$ins) json_error(500, 'db_prepare_failed');
        $ins->bind_param('sss', $name, $email, $hash);
        if ($ins->execute()) {
            $kid = $ins->insert_id;
            $ins->close();
            $adminId = intval($_SESSION['user']['user_id'] ?? ($_SESSION['user']['id'] ?? 0));
            $details = json_encode(['konselor_id'=>intval($kid),'email'=>$email]);
            insert_activity_log($conn, 'admin', $adminId, 'create_konselor', $details);
            echo json_encode(['success' => true, 'konselor_id' => intval($kid), 'type' => 'konselor']);
            break;
        } else {
            $err = $ins->error; $ins->close(); json_error(500, 'db_insert_failed');
        }
        break;

    case 'update_konselor':
        if (!is_admin_session()) { json_error(403, 'forbidden'); }
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) json_error(400, 'missing_id');
        $name = isset($_POST['name']) ? trim($_POST['name']) : null;
        $email = isset($_POST['email']) ? trim($_POST['email']) : null;
        $password = isset($_POST['password']) ? $_POST['password'] : null;

        // build dynamic update
        $parts = [];
        $types = '';
        $vals = [];
        if ($name !== null) { $parts[] = 'name=?'; $types .= 's'; $vals[] = $name; }
        if ($email !== null) { if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) json_error(400,'invalid_email'); $parts[] = 'email=?'; $types .= 's'; $vals[] = $email; }
        if ($password !== null && $password !== '') { $parts[] = 'password=?'; $types .= 's'; $vals[] = password_hash($password, PASSWORD_DEFAULT); }
        if (count($parts) === 0) json_error(400,'nothing_to_update');

        $sql = "UPDATE konselor SET " . implode(',', $parts) . " WHERE konselor_id=?";
        $types .= 'i'; $vals[] = $id;
        $stmt = $conn->prepare($sql);
        if ($stmt === false) json_error(500,'db_prepare_failed');
        // bind
        $bind_names = [$types];
        foreach ($vals as $k => $v) { $bind_name = 'b'.$k; $$bind_name = $v; $bind_names[] = &$$bind_name; }
        call_user_func_array([$stmt, 'bind_param'], $bind_names);
        // fetch previous row for changes
        $prev = null; $pstmt = $conn->prepare("SELECT name,email FROM konselor WHERE konselor_id=? LIMIT 1");
        if ($pstmt) { $pstmt->bind_param('i',$id); $pstmt->execute(); $r = $pstmt->get_result(); if ($r && $r->num_rows===1) { $prev = $r->fetch_assoc(); } if ($r) $r->free(); $pstmt->close(); }

        if ($stmt->execute()) {
            $stmt->close();
            // build changes map
            $changes = [];
            if ($name !== null) $changes['name'] = $name;
            if ($email !== null) $changes['email'] = $email;
            if ($password !== null && $password !== '') $changes['password'] = 'updated';
            $adminId = intval($_SESSION['user']['user_id'] ?? ($_SESSION['user']['id'] ?? 0));
            $details = json_encode(['konselor_id'=>intval($id),'updated'=>$changes]);
            insert_activity_log($conn, 'admin', $adminId, 'update_konselor', $details);
            echo json_encode(['success'=>true]);
            break;
        } else {
            $err = $stmt->error; $stmt->close(); json_error(500,'db_update_failed');
        }
        break;

    case 'delete_konselor':
        if (!is_admin_session()) { json_error(403,'forbidden'); }
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) json_error(400,'missing_id');
        $stmt = $conn->prepare("DELETE FROM konselor WHERE konselor_id=?");
        if ($stmt === false) json_error(500,'db_prepare_failed');
        $stmt->bind_param('i',$id);
        if ($stmt->execute()) {
            $stmt->close();
            $adminId = intval($_SESSION['user']['user_id'] ?? ($_SESSION['user']['id'] ?? 0));
            $details = json_encode(['konselor_id'=>intval($id)]);
            insert_activity_log($conn, 'admin', $adminId, 'delete_konselor', $details);
            echo json_encode(['success'=>true]);
            break;
        } else { $err = $stmt->error; $stmt->close(); json_error(500,'db_delete_failed'); }
        break;

    case 'update_user':
        if (!is_admin_session()) { json_error(403, 'forbidden'); }

        $id = intval($_POST['id'] ?? 0);
        $name = isset($_POST['name']) ? trim($_POST['name']) : null;
        $email = isset($_POST['email']) ? trim($_POST['email']) : null;
        $role = isset($_POST['role']) ? $_POST['role'] : null;
        $password = isset($_POST['password']) ? $_POST['password'] : null;

        if ($id <= 0) json_error(400, 'missing_id');

        // build dynamic update
        $parts = [];
        $types = '';
        $vals = [];

        if ($name !== null) { $parts[] = 'name=?'; $types .= 's'; $vals[] = $name; }
        if ($email !== null) {
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) json_error(400, 'invalid_email');
            $parts[] = 'email=?'; $types .= 's'; $vals[] = $email;
        }
        if ($role !== null) { $parts[] = 'role=?'; $types .= 's'; $vals[] = $role; }
        if ($password !== null && $password !== '') {
            $parts[] = 'password=?';
            $types .= 's';
            $vals[] = password_hash($password, PASSWORD_DEFAULT);
        }

        if (count($parts) === 0) {
            json_error(400, 'nothing_to_update');
        }

        $sql = "UPDATE users SET " . implode(',', $parts) . " WHERE user_id=?";
        $types .= 'i';
        $vals[] = $id;

        $stmt = $conn->prepare($sql);
        if ($stmt === false) json_error(500, 'db_prepare_failed');

        // bind params dynamically
        $bind_names[] = $types;
        foreach ($vals as $k => $v) {
            $bind_name = 'bind' . $k;
            $$bind_name = $v;
            $bind_names[] = &$$bind_name;
        }
        call_user_func_array([$stmt, 'bind_param'], $bind_names);

        // fetch previous role/email for potential konselor handling
        $prevRole = null; $prevEmail = null;
        $prevStmt = $conn->prepare("SELECT role, email FROM users WHERE user_id=? LIMIT 1");
        if ($prevStmt) { $prevStmt->bind_param('i', $id); $prevStmt->execute(); $r = $prevStmt->get_result(); if ($r && $r->num_rows === 1) { $pr = $r->fetch_assoc(); $prevRole = $pr['role']; $prevEmail = $pr['email']; } if ($r) $r->free(); $prevStmt->close(); }

        if ($stmt->execute()) {
            $stmt->close();
            $adminId = intval($_SESSION['user']['user_id'] ?? ($_SESSION['user']['id'] ?? 0));
            // Build a changes map for logging using the provided field values
            $changes = [];
            if ($name !== null) $changes['name'] = $name;
            if ($email !== null) $changes['email'] = $email;
            if ($role !== null) $changes['role'] = $role;
            if ($password !== null && $password !== '') $changes['password'] = 'updated';
            $details = json_encode(['user_id'=>intval($id),'updated'=>$changes]);
                insert_activity_log($conn, 'admin', $adminId, 'update_user', $details);
                // If role changed to konselor, ensure konselor entry exists
                if ($role === 'konselor' && $prevRole !== 'konselor') {
                    // use provided email (if updated) or previous email
                    $targetEmail = $email ?: $prevEmail;
                    $targetName = $name ?: null;
                    if ($targetEmail) {
                        $kcheck = $conn->prepare("SELECT konselor_id FROM konselor WHERE email = ? LIMIT 1");
                        if ($kcheck) {
                            $kcheck->bind_param('s', $targetEmail);
                            $kcheck->execute();
                            $kres = $kcheck->get_result();
                            if ($kres && $kres->num_rows === 0) {
                                // if password updated we hashed it earlier; use new password if provided
                                $kpass = ($password !== null && $password !== '') ? password_hash($password, PASSWORD_DEFAULT) : null;
                                $kinsert = $conn->prepare("INSERT INTO konselor (name, email, password) VALUES (?,?,?)");
                                if ($kinsert) {
                                    $n = $targetName ?? ($name ?? '');
                                    $p = $kpass ?? '';
                                    $kinsert->bind_param('sss', $n, $targetEmail, $p);
                                    $kinsert->execute();
                                    $kinsert->close();
                                }
                            }
                            if ($kres) $kres->free();
                            $kcheck->close();
                        }
                    }
                }
            echo json_encode(['success' => true]);
            break;
        } else {
            $err = $stmt->error;
            $stmt->close();
            json_error(500, 'db_update_failed');
        }
        break;

    case 'delete_user':
        if (!is_admin_session()) { json_error(403, 'forbidden'); }
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) json_error(400, 'missing_id');

        $stmt = $conn->prepare("DELETE FROM users WHERE user_id=?");
        if ($stmt === false) json_error(500, 'db_prepare_failed');

        $stmt->bind_param('i', $id);
        // before deleting, we want email & role
        $prevStmt = $conn->prepare("SELECT email, role FROM users WHERE user_id=? LIMIT 1");
        $prevEmail = null; $prevRole = null;
        if ($prevStmt) { $prevStmt->bind_param('i', $id); $prevStmt->execute(); $prevRes = $prevStmt->get_result(); if ($prevRes && $prevRes->num_rows===1) { $prow = $prevRes->fetch_assoc(); $prevEmail = $prow['email']; $prevRole = $prow['role']; } if ($prevRes) $prevRes->free(); $prevStmt->close(); }

        if ($stmt->execute()) {
            $stmt->close();
            // if deleted user was konselor, remove konselor record with same email (best-effort)
            if ($prevRole === 'konselor' && $prevEmail) {
                $kdel = $conn->prepare("DELETE FROM konselor WHERE email = ? LIMIT 1");
                if ($kdel) { $kdel->bind_param('s',$prevEmail); $kdel->execute(); $kdel->close(); }
            }
                $adminId = intval($_SESSION['user']['user_id'] ?? ($_SESSION['user']['id'] ?? 0));
                $details = json_encode(['user_id'=>intval($id)]);
                insert_activity_log($conn, 'admin', $adminId, 'delete_user', $details);
            echo json_encode(['success' => true]);
            break;
        } else {
            $err = $stmt->error;
            $stmt->close();
            json_error(500, 'db_delete_failed');
        }
        break;

    default:
        json_error(400, 'unknown_action');
        break;
}