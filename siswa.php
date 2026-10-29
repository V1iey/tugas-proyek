<?php
require '../config.php'; require_login(['admin']);
$a = $_GET['a'] ?? 'list'; $id = (int)($_GET['id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $do = $_POST['do'] ?? '';
  if ($do === 'delete') {
    $i = (int)$_POST['id']; $s = q("SELECT user_id FROM siswa WHERE id=?", [$i])->fetch();
    if ($s) {
      $pdo->beginTransaction();
      q("DELETE FROM nilai_rapor WHERE siswa_id=?", [$i]); q("DELETE FROM mengajar WHERE siswa_id=?", [$i]);
      q("DELETE FROM siswa WHERE id=?", [$i]); q("DELETE FROM users WHERE id=?", [$s['user_id']]);
      $pdo->commit(); flash('Siswa dihapus');
    }
    redirect('admin/siswa.php');
  }
  $nis = trim($_POST['nis'] ?? ''); $nama = trim($_POST['nama'] ?? ''); $kelas = trim($_POST['kelas'] ?? '');
  $jk = $_POST['jenis_kelamin'] ?? ''; $un = trim($_POST['username'] ?? ''); $pw = $_POST['password'] ?? '';
  try {
    if (!$nis || !$nama || !$kelas || !$un || !in_array($jk, ['L','P'])) throw new Exception('Data tidak lengkap');
    $pdo->beginTransaction();
    if ($do === 'add') {
      if (strlen($pw) < 6) throw new Exception('Password minimal 6 karakter');
      q("INSERT INTO users(username,password,role) VALUES(?,?,'siswa')", [$un, password_hash($pw, PASSWORD_DEFAULT)]);
      q("INSERT INTO siswa(nis,nama,kelas,jenis_kelamin,user_id) VALUES(?,?,?,?,?)", [$nis,$nama,$kelas,$jk,$pdo->lastInsertId()]);
      flash('Siswa ditambahkan');
    } else {
      $i = (int)$_POST['id']; $s = q("SELECT user_id FROM siswa WHERE id=?", [$i])->fetch();
      if (!$s) throw new Exception('Data siswa tidak ditemukan');
      q("UPDATE siswa SET nis=?,nama=?,kelas=?,jenis_kelamin=? WHERE id=?", [$nis,$nama,$kelas,$jk,$i]);
      q("UPDATE users SET username=? WHERE id=?", [$un, $s['user_id']]);
      if ($pw !== '') q("UPDATE users SET password=? WHERE id=?", [password_hash($pw, PASSWORD_DEFAULT), $s['user_id']]);
      flash('Siswa diperbarui');
    }
    $pdo->commit();
  } catch (Throwable $ex) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    flash($ex instanceof PDOException ? 'NIS atau username sudah dipakai' : $ex->getMessage(), 'err');
  }
  redirect('admin/siswa.php');
}
$title = 'Kelola Siswa'; require '../inc/header.php';
if ($a === 'add' || $a === 'edit') {
  $r = $a === 'edit' ? q("SELECT s.*, u.username FROM siswa s JOIN users u ON u.id=s.user_id WHERE s.id=?", [$id])->fetch()
     : ['id'=>0,'nis'=>'','nama'=>'','kelas'=>'','jenis_kelamin'=>'L','username'=>''];
if (!$r) { echo "<div class='alert err'>Data tidak ditemukan.</div>"; require '../inc/footer.php'; exit; }
?>
<form method="post" class="card"><?= csrf_field() ?><input type="hidden" name="do" value="<?= $a ?>"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
<label>NIS</label><input name="nis" value="<?= e($r['nis']) ?>" maxlength="10" required>
<label>Nama</label><input name="nama" value="<?= e($r['nama']) ?>" maxlength="100" required>
<label>Kelas</label><input name="kelas" value="<?= e($r['kelas']) ?>" maxlength="10" required>
<label>Jenis Kelamin</label><?= jk_select($r['jenis_kelamin']) ?>
<label>Username akun</label><input name="username" value="<?= e($r['username']) ?>" maxlength="50" required>
<label>Password <?= $a === 'edit' ? '(kosongkan jika tidak diubah)' : '' ?></label><input type="password" name="password" <?= $a === 'add' ? 'required minlength="6"' : '' ?>><br><br>
<button class="btn">Simpan</button> <a class="btn gray" href="siswa.php">Batal</a></form>
<?php } else { ?>
<div class="bar"><a class="btn green" href="?a=add">+ Tambah Siswa</a></div>
<div class="tbl"><table><tr><th>No</th><th>NIS</th><th>Nama</th><th>Kelas</th><th>JK</th><th>Username</th><th>Aksi</th></tr>
<?php foreach (q("SELECT s.*, u.username FROM siswa s JOIN users u ON u.id=s.user_id ORDER BY s.kelas, s.nama")->fetchAll() as $i=>$r): ?>
<tr><td><?= $i+1 ?></td><td><?= e($r['nis']) ?></td><td><?= e($r['nama']) ?></td><td><?= e($r['kelas']) ?></td><td><?= e($r['jenis_kelamin']) ?></td><td><?= e($r['username']) ?></td>
<td><a class="btn" href="?a=edit&id=<?= $r['id'] ?>">Edit</a> <?= del_btn($r['id']) ?></td></tr>
<?php endforeach; ?></table></div>
<?php } require '../inc/footer.php';
