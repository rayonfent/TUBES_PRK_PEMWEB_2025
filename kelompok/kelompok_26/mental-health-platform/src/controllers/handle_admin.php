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
            echo json_encode(['success' => true, 'id' => intval($id)]);
            break;
        } else {
            $err = $stmt->error;
            $stmt->close();
            json_error(500, 'db_insert_failed');
        }
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

        if ($stmt->execute()) {
            $stmt->close();
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
        if ($stmt->execute()) {
            $stmt->close();
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