<?php
require '../config.php'; require_login(['admin']);
$a = $_GET['a'] ?? 'list'; $id = (int)($_GET['id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $do = $_POST['do'] ?? '';
  if ($do === 'delete') {
    $i = (int)$_POST['id'];
    if ($i == $_SESSION['user']['id']) flash('Tidak bisa menghapus akun sendiri', 'err');
    elseif (q("SELECT 1 FROM siswa WHERE user_id=?", [$i])->fetch() || q("SELECT 1 FROM guru WHERE user_id=?", [$i])->fetch())
      flash('User terhubung data siswa/guru. Hapus lewat menu Siswa/Guru.', 'err');
    else { q("DELETE FROM users WHERE id=?", [$i]); flash('User dihapus'); }
    redirect('admin/users.php');
  }
  $un = trim($_POST['username'] ?? ''); $pw = $_POST['password'] ?? ''; $role = $_POST['role'] ?? '';
  try {
    if (!$un || !in_array($role, ['admin','guru','siswa'])) throw new Exception('Data tidak lengkap');
    if ($do === 'add') {
      if (strlen($pw) < 6) throw new Exception('Password minimal 6 karakter');
      q("INSERT INTO users(username,password,role) VALUES(?,?,?)", [$un, password_hash($pw, PASSWORD_DEFAULT), $role]);
      flash('User ditambahkan');
    } else {
      $i = (int)$_POST['id'];
      if ($i == $_SESSION['user']['id'] && $role !== 'admin') throw new Exception('Tidak bisa mengubah role akun sendiri');
      q("UPDATE users SET username=?, role=? WHERE id=?", [$un, $role, $i]);
      if ($pw !== '') q("UPDATE users SET password=? WHERE id=?", [password_hash($pw, PASSWORD_DEFAULT), $i]);
      if ($i == $_SESSION['user']['id']) { $_SESSION['user']['username'] = $un; $_SESSION['user']['role'] = $role; }
      flash('User diperbarui');
    }
  } catch (PDOException $ex) { flash('Username sudah dipakai', 'err'); }
  catch (Exception $ex) { flash($ex->getMessage(), 'err'); }
  redirect('admin/users.php');
}
$title = 'Kelola User'; require '../inc/header.php';
if ($a === 'add' || $a === 'edit') {
  $r = $a === 'edit' ? q("SELECT * FROM users WHERE id=?", [$id])->fetch() : ['username'=>'','role'=>'siswa','id'=>0];
if (!$r) { echo "<div class='alert err'>Data tidak ditemukan.</div>"; require '../inc/footer.php'; exit; }
?>
<form method="post" class="card"><?= csrf_field() ?><input type="hidden" name="do" value="<?= $a ?>"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
<label>Username</label><input name="username" value="<?= e($r['username']) ?>" maxlength="50" required>
<label>Password <?= $a === 'edit' ? '(kosongkan jika tidak diubah)' : '' ?></label><input type="password" name="password" <?= $a === 'add' ? 'required minlength="6"' : '' ?>>
<label>Role</label><select name="role"><?php foreach (['admin','guru','siswa'] as $x) echo "<option".($r['role']===$x?' selected':'').">$x</option>"; ?></select><br><br>
<button class="btn">Simpan</button> <a class="btn gray" href="users.php">Batal</a></form>
<?php } else { ?>
<div class="bar"><a class="btn green" href="?a=add">+ Tambah User</a></div>
<div class="tbl"><table><tr><th>No</th><th>Username</th><th>Role</th><th>Dibuat</th><th>Aksi</th></tr>
<?php foreach (q("SELECT * FROM users ORDER BY id")->fetchAll() as $i=>$r): ?>
<tr><td><?= $i+1 ?></td><td><?= e($r['username']) ?></td><td><?= e($r['role']) ?></td><td><?= e($r['created_at']) ?></td>
<td><a class="btn" href="?a=edit&id=<?= $r['id'] ?>">Edit</a> <?= del_btn($r['id']) ?></td></tr>
<?php endforeach; ?></table></div>
<?php } require '../inc/footer.php';
