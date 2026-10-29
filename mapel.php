<?php
require '../config.php'; require_login(['admin']);
$a = $_GET['a'] ?? 'list'; $id = (int)($_GET['id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $do = $_POST['do'] ?? '';
  if ($do === 'delete') {
    $i = (int)$_POST['id'];
    $pdo->beginTransaction();
    q("DELETE FROM nilai_rapor WHERE mapel_id=?", [$i]); q("DELETE FROM mengajar WHERE mapel_id=?", [$i]); q("DELETE FROM mapel WHERE id=?", [$i]);
    $pdo->commit(); flash('Mata pelajaran dihapus'); redirect('admin/mapel.php');
  }
  $kode = trim($_POST['kode_mapel'] ?? ''); $nama = trim($_POST['nama_mapel'] ?? ''); $gid = (int)($_POST['guru_id'] ?? 0);
  if (!$kode || !$nama || !q("SELECT 1 FROM guru WHERE id=?", [$gid])->fetch()) flash('Data tidak lengkap / guru tidak valid', 'err');
  elseif ($do === 'add') { q("INSERT INTO mapel(kode_mapel,nama_mapel,guru_id) VALUES(?,?,?)", [$kode,$nama,$gid]); flash('Mapel ditambahkan'); }
  else { q("UPDATE mapel SET kode_mapel=?,nama_mapel=?,guru_id=? WHERE id=?", [$kode,$nama,$gid,(int)$_POST['id']]); flash('Mapel diperbarui'); }
  redirect('admin/mapel.php');
}
$title = 'Kelola Mata Pelajaran'; require '../inc/header.php';
if ($a === 'add' || $a === 'edit') {
  $r = $a === 'edit' ? q("SELECT * FROM mapel WHERE id=?", [$id])->fetch() : ['id'=>0,'kode_mapel'=>'','nama_mapel'=>'','guru_id'=>0];
if (!$r) { echo "<div class='alert err'>Data tidak ditemukan.</div>"; require '../inc/footer.php'; exit; }
?>
<form method="post" class="card"><?= csrf_field() ?><input type="hidden" name="do" value="<?= $a ?>"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
<label>Kode Mapel</label><input name="kode_mapel" value="<?= e($r['kode_mapel']) ?>" maxlength="10" required>
<label>Nama Mapel</label><input name="nama_mapel" value="<?= e($r['nama_mapel']) ?>" maxlength="100" required>
<label>Guru Pengampu</label><select name="guru_id" required><option value="">-- pilih guru --</option>
<?php foreach (q("SELECT id,nama FROM guru ORDER BY nama")->fetchAll() as $g) echo "<option value='{$g['id']}'".($g['id']==$r['guru_id']?' selected':'').">".e($g['nama'])."</option>"; ?></select><br><br>
<button class="btn">Simpan</button> <a class="btn gray" href="mapel.php">Batal</a></form>
<?php } else { ?>
<div class="bar"><a class="btn green" href="?a=add">+ Tambah Mapel</a></div>
<div class="tbl"><table><tr><th>No</th><th>Kode</th><th>Nama Mapel</th><th>Guru</th><th>Aksi</th></tr>
<?php foreach (q("SELECT m.*, g.nama guru FROM mapel m LEFT JOIN guru g ON g.id=m.guru_id ORDER BY m.kode_mapel")->fetchAll() as $i=>$r): ?>
<tr><td><?= $i+1 ?></td><td><?= e($r['kode_mapel']) ?></td><td><?= e($r['nama_mapel']) ?></td><td><?= e($r['guru']) ?></td>
<td><a class="btn" href="?a=edit&id=<?= $r['id'] ?>">Edit</a> <?= del_btn($r['id']) ?></td></tr>
<?php endforeach; ?></table></div>
<?php } require '../inc/footer.php';
