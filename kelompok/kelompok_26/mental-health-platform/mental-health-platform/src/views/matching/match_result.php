<!-- src/views/matching/match_result.php -->
<?php
require_once __DIR__ . '/../../helpers/auth.php';
require_login();
$user = current_user();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Match Result</title></head>
<body>
<h2>Hasil Matching untuk <?=htmlspecialchars($user['name'])?></h2>

<?php if(empty($candidates)): ?>
    <p>Tidak ada konselor yang sesuai saat ini. Coba lagi nanti.</p>
<?php else: ?>
    <ul>
    <?php foreach($candidates as $c):
        $k = $c['konselor'];
    ?>
        <li>
            <strong><?=htmlspecialchars($k['name'])?></strong>
            (style: <?=htmlspecialchars($k['communication_style'])?>, approach: <?=htmlspecialchars($k['approach_style'])?>)
            - rating: <?=htmlspecialchars($k['rating'])?>
            <form action="controllers/handle_matching.php?action=start" method="post" style="display:inline">
                <input type="hidden" name="konselor_id" value="<?=$k['konselor_id']?>">
                <button type="submit">Mulai Chat Trial 1 Hari</button>
            </form>
        </li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>
</body>
</html>