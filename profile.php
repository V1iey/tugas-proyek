<?php
require 'config.php'; require_login();
$u = $_SESSION['user']; $role = $u['role'];
$prof = $role === 'siswa' ? q("SELECT * FROM siswa WHERE user_id=?", [$u['id']])->fetch()
      : ($role === 'guru' ? q("SELECT * FROM guru WHERE user_id=?", [$u['id']])->fetch() : null);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    $pdo->beginTransaction();
    $un = trim($_POST['username']);
    if (!$un) throw new Exception('Username wajib diisi');
    q("UPDATE users SET username=? WHERE id=?", [$un, $u['id']]);
    if ($_POST['password'] !== '') {
      if (strlen($_POST['password']) < 6) throw new Exception('Password minimal 6 karakter');
      q("UPDATE users SET password=? WHERE id=?", [password_hash($_POST['password'], PASSWORD_DEFAULT), $u['id']]);
    }
    if ($role === 'siswa' && $prof)
      q("UPDATE siswa SET nis=?,nama=?,kelas=?,jenis_kelamin=? WHERE id=?", [trim($_POST['nis']),trim($_POST['nama']),trim($_POST['kelas']),$_POST['jenis_kelamin'],$prof['id']]);
    if ($role === 'guru' && $prof)
      q("UPDATE guru SET nip=?,nama=?,jenis_kelamin=? WHERE id=?", [trim($_POST['nip']),trim($_POST['nama']),$_POST['jenis_kelamin'],$prof['id']]);
    $pdo->commit(); $_SESSION['user']['username'] = $un; flash('Profile diperbarui');
  } catch (Throwable $ex) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    flash($ex instanceof PDOException ? 'Username/NIS/NIP sudah dipakai' : $ex->getMessage(), 'err');
  }
  redirect('profile.php');
}
$title = 'Profile'; require 'inc/header.php';
$cu = q("SELECT * FROM users WHERE id=?", [$u['id']])->fetch();
?>
<form method="post" class="card"><?= csrf_field() ?>
<label>Role</label><input value="<?= e($role) ?>" disabled>
<label>Username</label><input name="username" value="<?= e($cu['username']) ?>" maxlength="50" required>
<label>Password baru (kosongkan jika tidak diubah)</label><input type="password" name="password">
<?php if ($role === 'siswa' && $prof): ?>
<label>NIS</label><input name="nis" value="<?= e($prof['nis']) ?>" maxlength="10" required>
<label>Nama</label><input name="nama" value="<?= e($prof['nama']) ?>" required>
<label>Kelas</label><input name="kelas" value="<?= e($prof['kelas']) ?>" maxlength="10" required>
<label>Jenis Kelamin</label><?= jk_select($prof['jenis_kelamin']) ?>
<?php elseif ($role === 'guru' && $prof): ?>
<label>NIP</label><input name="nip" value="<?= e($prof['nip']) ?>" maxlength="20" required>
<label>Nama</label><input name="nama" value="<?= e($prof['nama']) ?>" required>
<label>Jenis Kelamin</label><?= jk_select($prof['jenis_kelamin']) ?>
<?php endif; ?><br><br><button class="btn">Simpan</button></form>
<?php require 'inc/footer.php';
