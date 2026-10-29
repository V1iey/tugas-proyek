<?php
require '../config.php'; require_login(['admin']);
$a = $_GET['a'] ?? 'list'; $id = (int)($_GET['id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $do = $_POST['do'] ?? '';
  if ($do === 'delete') {
    $i = (int)$_POST['id']; $g = q("SELECT user_id FROM guru WHERE id=?", [$i])->fetch();
    if (q("SELECT 1 FROM mapel WHERE guru_id=?", [$i])->fetch()) flash('Guru masih mengampu mata pelajaran. Ubah/hapus mapel terlebih dahulu.', 'err');
    elseif ($g) {
      $pdo->beginTransaction();
      q("DELETE FROM guru WHERE id=?", [$i]); q("DELETE FROM users WHERE id=?", [$g['user_id']]);
      $pdo->commit(); flash('Guru dihapus');
    }
    redirect('admin/guru.php');
  }
  $nip = trim($_POST['nip'] ?? ''); $nama = trim($_POST['nama'] ?? ''); $jk = $_POST['jenis_kelamin'] ?? '';
  $un = trim($_POST['username'] ?? ''); $pw = $_POST['password'] ?? '';
  try {
    if (!$nip || !$nama || !$un || !in_array($jk, ['L','P'])) throw new Exception('Data tidak lengkap');
    $pdo->beginTransaction();
    if ($do === 'add') {
      if (strlen($pw) < 6) throw new Exception('Password minimal 6 karakter');
      q("INSERT INTO users(username,password,role) VALUES(?,?,'guru')", [$un, password_hash($pw, PASSWORD_DEFAULT)]);
      q("INSERT INTO guru(nip,nama,jenis_kelamin,user_id) VALUES(?,?,?,?)", [$nip,$nama,$jk,$pdo->lastInsertId()]);
      flash('Guru ditambahkan');
    } else {
      $i = (int)$_POST['id']; $g = q("SELECT user_id FROM guru WHERE id=?", [$i])->fetch();
      if (!$g) throw new Exception('Data guru tidak ditemukan');
      q("UPDATE guru SET nip=?,nama=?,jenis_kelamin=? WHERE id=?", [$nip,$nama,$jk,$i]);
      q("UPDATE users SET username=? WHERE id=?", [$un, $g['user_id']]);
      if ($pw !== '') q("UPDATE users SET password=? WHERE id=?", [password_hash($pw, PASSWORD_DEFAULT), $g['user_id']]);
      flash('Guru diperbarui');
    }
    $pdo->commit();
  } catch (Throwable $ex) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    flash($ex instanceof PDOException ? 'NIP atau username sudah dipakai' : $ex->getMessage(), 'err');
  }
  redirect('admin/guru.php');
}
$title = 'Kelola Guru'; require '../inc/header.php';
if ($a === 'add' || $a === 'edit') {
  $r = $a === 'edit' ? q("SELECT g.*, u.username FROM guru g JOIN users u ON u.id=g.user_id WHERE g.id=?", [$id])->fetch()
     : ['id'=>0,'nip'=>'','nama'=>'','jenis_kelamin'=>'L','username'=>''];
if (!$r) { echo "<div class='alert err'>Data tidak ditemukan.</div>"; require '../inc/footer.php'; exit; }
?>
<form method="post" class="card"><?= csrf_field() ?><input type="hidden" name="do" value="<?= $a ?>"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
<label>NIP</label><input name="nip" value="<?= e($r['nip']) ?>" maxlength="20" required>
<label>Nama</label><input name="nama" value="<?= e($r['nama']) ?>" maxlength="100" required>
<label>Jenis Kelamin</label><?= jk_select($r['jenis_kelamin']) ?>
<label>Username akun</label><input name="username" value="<?= e($r['username']) ?>" maxlength="50" required>
<label>Password <?= $a === 'edit' ? '(kosongkan jika tidak diubah)' : '' ?></label><input type="password" name="password" <?= $a === 'add' ? 'required minlength="6"' : '' ?>><br><br>
<button class="btn">Simpan</button> <a class="btn gray" href="guru.php">Batal</a></form>
<?php } else { ?>
<div class="bar"><a class="btn green" href="?a=add">+ Tambah Guru</a></div>
<div class="tbl"><table><tr><th>No</th><th>NIP</th><th>Nama</th><th>JK</th><th>Username</th><th>Aksi</th></tr>
<?php foreach (q("SELECT g.*, u.username FROM guru g JOIN users u ON u.id=g.user_id ORDER BY g.nama")->fetchAll() as $i=>$r): ?>
<tr><td><?= $i+1 ?></td><td><?= e($r['nip']) ?></td><td><?= e($r['nama']) ?></td><td><?= e($r['jenis_kelamin']) ?></td><td><?= e($r['username']) ?></td>
<td><a class="btn" href="?a=edit&id=<?= $r['id'] ?>">Edit</a> <?= del_btn($r['id']) ?></td></tr>
<?php endforeach; ?></table></div>
<?php } require '../inc/footer.php';
