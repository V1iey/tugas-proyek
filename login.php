<?php
require 'config.php';
if (!empty($_SESSION['user'])) redirect('dashboard.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $u = q("SELECT * FROM users WHERE username=?", [trim($_POST['username'] ?? '')])->fetch();
  if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
    session_regenerate_id(true);
    $_SESSION['user'] = ['id'=>$u['id'],'username'=>$u['username'],'role'=>$u['role']];
    redirect('dashboard.php');
  }
  flash('Username atau password salah', 'err'); redirect('login.php');
}
?><!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login - SIPN</title><link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css"></head><body>
<div class="auth"><form method="post" class="card"><h2>Login SIPN</h2><?= flash() ?><?= csrf_field() ?>
<label>Username</label><input name="username" required autofocus>
<label>Password</label><input type="password" name="password" required><br><br>
<button class="btn">Login</button> <a href="register.php">Daftar sebagai siswa</a></form></div></body></html>
