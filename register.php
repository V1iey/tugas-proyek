<?php
require 'config.php';
if (!empty($_SESSION['user'])) redirect('dashboard.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $un = trim($_POST['username']); $pw = $_POST['password']; $nis = trim($_POST['nis']);
  $nama = trim($_POST['nama']); $kelas = trim($_POST['kelas']); $jk = $_POST['jenis_kelamin'];
  if (!$un || strlen($pw) < 6 || !$nis || !$nama || !$kelas || !in_array($jk, ['L','P'])) {
    flash('Lengkapi semua data (password minimal 6 karakter)', 'err'); redirect('register.php');
  }
  try {
    $pdo->beginTransaction();
    q("INSERT INTO users(username,password,role) VALUES(?,?,'siswa')", [$un, password_hash($pw, PASSWORD_DEFAULT)]);
    q("INSERT INTO siswa(nis,nama,kelas,jenis_kelamin,user_id) VALUES(?,?,?,?,?)", [$nis,$nama,$kelas,$jk,$pdo->lastInsertId()]);
    $pdo->commit(); flash('Registrasi berhasil, silakan login'); redirect('login.php');
  } catch (PDOException $ex) { $pdo->rollBack(); flash('Username atau NIS sudah terdaftar', 'err'); redirect('register.php'); }
}
?><!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Register - SIPN</title><link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css"></head><body>
<div class="auth"><form method="post" class="card"><h2>Register Siswa</h2><?= flash() ?><?= csrf_field() ?>
<label>Username</label><input name="username" maxlength="50" required>
<label>Password</label><input type="password" name="password" minlength="6" required>
<label>NIS</label><input name="nis" maxlength="10" required>
<label>Nama Lengkap</label><input name="nama" maxlength="100" required>
<label>Kelas</label><input name="kelas" maxlength="10" required>
<label>Jenis Kelamin</label><?= jk_select() ?><br><br>
<button class="btn">Daftar</button> <a href="login.php">Kembali ke login</a></form></div></body></html>
