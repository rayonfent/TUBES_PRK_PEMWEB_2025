<?php
$id_saya = $_SESSION['konselor']['id'] ?? 0;
$sql = "SELECT u.user_id, u.name, u.email, s.q1, s.q2, s.q3, s.q4 FROM chat_session cs JOIN users u ON cs.user_id = u.user_id LEFT JOIN user_survey s ON u.user_id = s.user_id WHERE cs.konselor_id = ? GROUP BY u.user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_saya]);
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getPersonality($s) {
    if (!$s['q1']) return ['name' => 'Belum Survey', 'color' => 'bg-gray-100 text-gray-500'];
    $direct = ($s['q1']==1) + ($s['q2']==1) + ($s['q3']==1);
    $emosi  = ($s['q4']==1);
    if ($direct >= 2 && $emosi) return ['name' => 'Analytical Realist', 'color' => 'bg-blue-100 text-blue-700'];
    if ($direct >= 2 && !$emosi) return ['name' => 'Straightforward Feeler', 'color' => 'bg-purple-100 text-purple-700'];
    if ($direct < 2 && $emosi) return ['name' => 'Calm Rationalist', 'color' => 'bg-teal-100 text-teal-700'];
    return ['name' => 'Empathic Listener', 'color' => 'bg-pink-100 text-pink-700'];
}
?>
<div class="mb-8"><h2 class="text-2xl font-bold text-gray-800">Daftar Klien</h2></div>
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-left text-sm">
        <thead class="bg-gray-50 text-gray-600 border-b border-gray-100"><tr><th class="p-4">Nama Pasien</th><th class="p-4">Email</th><th class="p-4">Analisa AI</th><th class="p-4 text-center">Aksi</th></tr></thead>
        <tbody class="divide-y divide-gray-50">
            <?php foreach($clients as $c): $p = getPersonality($c); ?>
            <tr class="hover:bg-gray-50">
                <td class="p-4 font-bold text-gray-800"><?php echo htmlspecialchars($c['name']); ?></td>
                <td class="p-4 text-gray-600"><?php echo htmlspecialchars($c['email']); ?></td>
                <td class="p-4"><span class="<?php echo $p['color']; ?> px-2 py-1 rounded text-xs font-bold"><?php echo $p['name']; ?></span></td>
                <td class="p-4 text-center"><a href="index.php?p=konselor_dashboard&view=chat" class="text-blue-600 hover:underline">Chat</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>