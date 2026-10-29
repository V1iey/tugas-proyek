<?php
require '../config.php'; require_login(['admin']);
$a = $_GET['a'] ?? 'list'; $id = (int)($_GET['id'] ?? 0);
$exists = fn($m, $s, $except = 0) => q("SELECT 1 FROM mengajar WHERE mapel_id=? AND siswa_id=? AND id<>?", [$m,$s,$except])->fetch();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $do = $_POST['do'] ?? '';
  if ($do === 'delete') { q("DELETE FROM mengajar WHERE id=?", [(int)$_POST['id']]); flash('Data dihapus'); redirect('admin/mengajar.php'); }
  $m = (int)$_POST['mapel_id'];
  if (!q("SELECT 1 FROM mapel WHERE id=?", [$m])->fetch()) { flash('Mapel tidak valid', 'err'); redirect('admin/mengajar.php'); }
  if ($do === 'add') {
    $n = 0;
    foreach ((array)($_POST['siswa'] ?? []) as $s) { $s = (int)$s; if (q("SELECT 1 FROM siswa WHERE id=?", [$s])->fetch() && !$exists($m, $s)) { q("INSERT INTO mengajar(mapel_id,siswa_id) VALUES(?,?)", [$m,$s]); $n++; } }
    flash("$n siswa ditambahkan ke mapel");
  } else {
    $i = (int)$_POST['id']; $s = (int)$_POST['siswa_id'];
    if (!q("SELECT 1 FROM siswa WHERE id=?", [$s])->fetch()) flash('Siswa tidak valid', 'err');
    elseif ($exists($m, $s, $i)) flash('Data sudah ada', 'err');
    else { q("UPDATE mengajar SET mapel_id=?, siswa_id=? WHERE id=?", [$m,$s,$i]); flash('Data diperbarui'); }
  }
  redirect('admin/mengajar.php');
}
$title = 'Kelola Mengajar'; require '../inc/header.php';
$mapels = q("SELECT m.id, m.kode_mapel, m.nama_mapel, g.nama guru FROM mapel m LEFT JOIN guru g ON g.id=m.guru_id ORDER BY m.nama_mapel")->fetchAll();
$siswas = q("SELECT id,nis,nama,kelas FROM siswa ORDER BY kelas,nama")->fetchAll();
$mapelOpt = function($sel) use ($mapels) { $o = '';
  foreach ($mapels as $m) $o .= "<option value='{$m['id']}'".($m['id']==$sel?' selected':'').">".e("{$m['kode_mapel']} - {$m['nama_mapel']} ({$m['guru']})")."</option>"; return $o; };
if ($a === 'add') { ?>
<form method="post" class="card"><?= csrf_field() ?><input type="hidden" name="do" value="add">
<label>Mata Pelajaran</label><select name="mapel_id" required><option value="">-- pilih --</option><?= $mapelOpt(0) ?></select>
<label>Siswa</label><div class="chk"><?php foreach ($siswas as $s) echo "<label><input type='checkbox' name='siswa[]' value='{$s['id']}'> ".e("{$s['nama']} ({$s['kelas']})")."</label>"; ?></div><br>
<button class="btn">Simpan</button> <a class="btn gray" href="mengajar.php">Batal</a></form>
<?php } elseif ($a === 'edit') { $r = q("SELECT * FROM mengajar WHERE id=?", [$id])->fetch(); if (!$r) { echo "<div class='alert err'>Data tidak ditemukan.</div>"; require '../inc/footer.php'; exit; } ?>
<form method="post" class="card"><?= csrf_field() ?><input type="hidden" name="do" value="edit"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
<label>Mata Pelajaran</label><select name="mapel_id" required><?= $mapelOpt($r['mapel_id']) ?></select>
<label>Siswa</label><select name="siswa_id" required><?php foreach ($siswas as $s) echo "<option value='{$s['id']}'".($s['id']==$r['siswa_id']?' selected':'').">".e("{$s['nama']} ({$s['kelas']})")."</option>"; ?></select><br><br>
<button class="btn">Simpan</button> <a class="btn gray" href="mengajar.php">Batal</a></form>
<?php } else { ?>
<div class="bar"><a class="btn green" href="?a=add">+ Tambah Data Mengajar</a></div>
<div class="tbl"><table><tr><th>No</th><th>Mata Pelajaran</th><th>Guru</th><th>Siswa</th><th>Kelas</th><th>Aksi</th></tr>
<?php foreach (q("SELECT mg.id, m.nama_mapel, g.nama guru, s.nama siswa, s.kelas FROM mengajar mg JOIN mapel m ON m.id=mg.mapel_id LEFT JOIN guru g ON g.id=m.guru_id JOIN siswa s ON s.id=mg.siswa_id ORDER BY m.nama_mapel, s.kelas, s.nama")->fetchAll() as $i=>$r): ?>
<tr><td><?= $i+1 ?></td><td><?= e($r['nama_mapel']) ?></td><td><?= e($r['guru']) ?></td><td><?= e($r['siswa']) ?></td><td><?= e($r['kelas']) ?></td>
<td><a class="btn" href="?a=edit&id=<?= $r['id'] ?>">Edit</a> <?= del_btn($r['id']) ?></td></tr>
<?php endforeach; ?></table></div>
<?php } require '../inc/footer.php';
