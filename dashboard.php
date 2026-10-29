<?php
require 'config.php'; require_login();
$u = $_SESSION['user']; $cards = [];

if ($u['role'] === 'admin') {
  foreach (['users' => 'User', 'siswa' => 'Siswa', 'guru' => 'Guru', 'mapel' => 'Mata Pelajaran'] as $t => $l)
    $cards[$l] = q("SELECT COUNT(*) FROM $t")->fetchColumn();

} elseif ($u['role'] === 'guru') {
  $gid = q("SELECT id FROM guru WHERE user_id=?", [$u['id']])->fetchColumn();
  if ($gid === false) {
    flash('Data guru Anda belum dibuat oleh admin.', 'err');
    $cards = ['Mapel Diampu' => 0, 'Nilai Diinput' => 0];
  } else {
    $cards['Mapel Diampu'] = q("SELECT COUNT(*) FROM mapel WHERE guru_id=?", [$gid])->fetchColumn();
    $cards['Nilai Diinput'] = q("SELECT COUNT(*) FROM nilai_rapor n JOIN mapel m ON m.id=n.mapel_id WHERE m.guru_id=?", [$gid])->fetchColumn();
  }

} else {
  $sid = q("SELECT id FROM siswa WHERE user_id=?", [$u['id']])->fetchColumn();
  if ($sid === false) {
    flash('Data siswa tidak ditemukan.', 'err');
    $cards = ['Mapel Bernilai' => 0, 'Rata-rata Nilai' => '-'];
  } else {
    [$jml, $avg] = q("SELECT COUNT(*), ROUND(AVG((nilai_pengetahuan+nilai_keterampilan)/2),1) FROM nilai_rapor WHERE siswa_id=?", [$sid])->fetch(PDO::FETCH_NUM);
    $cards['Mapel Bernilai'] = $jml;
    $cards['Rata-rata Nilai'] = $avg ?? '-';
  }
}

$title = 'Dashboard'; require 'inc/header.php'; ?>
<p>Selamat datang, <b><?= e($u['username']) ?></b> (<?= e($u['role']) ?>)</p>
<div class="grid">
<?php foreach ($cards as $l => $v): ?>
  <div class="stat"><b><?= e($v) ?></b><?= e($l) ?></div>
<?php endforeach; ?>
</div>
<?php require 'inc/footer.php';
