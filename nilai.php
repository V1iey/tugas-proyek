<?php
require '../config.php'; require_login(['siswa']);
$title = 'Nilai Saya'; require '../inc/header.php';
$s = q("SELECT * FROM siswa WHERE user_id=?", [$_SESSION['user']['id']])->fetch();
if (!$s) { echo "<div class='alert err'>Data siswa tidak ditemukan.</div>"; require '../inc/footer.php'; exit; }
$rows = q("SELECT n.*, m.kode_mapel, m.nama_mapel, g.nama guru FROM nilai_rapor n JOIN mapel m ON m.id=n.mapel_id LEFT JOIN guru g ON g.id=m.guru_id WHERE n.siswa_id=? ORDER BY m.nama_mapel", [$s['id']])->fetchAll();
?>
<div class="card"><b><?= e($s['nama']) ?></b><br>NIS: <?= e($s['nis']) ?> | Kelas: <?= e($s['kelas']) ?></div>
<div class="tbl"><table><tr><th>No</th><th>Kode</th><th>Mata Pelajaran</th><th>Guru</th><th>KKM</th><th>Pengetahuan</th><th>Keterampilan</th><th>Keterangan</th></tr>
<?php foreach ($rows as $i=>$r): ?>
<tr><td><?= $i+1 ?></td><td><?= e($r['kode_mapel']) ?></td><td><?= e($r['nama_mapel']) ?></td><td><?= e($r['guru']) ?></td>
<td><?= e($r['nilai_kkm']) ?></td><td><?= e($r['nilai_pengetahuan']) ?></td><td><?= e($r['nilai_keterampilan']) ?></td><td><?= e($r['Keterangan']) ?></td></tr>
<?php endforeach; if (!$rows) echo "<tr><td colspan='8'>Belum ada nilai.</td></tr>"; ?></table></div>
<?php require '../inc/footer.php';
