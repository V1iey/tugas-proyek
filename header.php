<?php
if (empty($_SESSION['user'])) redirect('login.php');
$u = $_SESSION['user'];
$menus = [
 'admin'=>['dashboard.php'=>'Dashboard','admin/users.php'=>'User','admin/siswa.php'=>'Siswa','admin/guru.php'=>'Guru','admin/mapel.php'=>'Mata Pelajaran','admin/mengajar.php'=>'Mengajar','profile.php'=>'Profile'],
 'guru'=>['dashboard.php'=>'Dashboard','guru/nilai.php'=>'Nilai Siswa','profile.php'=>'Profile'],
 'siswa'=>['dashboard.php'=>'Dashboard','siswa/nilai.php'=>'Nilai Saya','profile.php'=>'Profile'],
];
?><!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title ?? 'SIPN') ?> - SIPN</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css"></head><body>
<nav><b>SIPN</b>
<?php foreach (($menus[$u['role']] ?? []) as $p=>$l): ?><a href="<?= BASE_URL.'/'.$p ?>"><?= $l ?></a><?php endforeach; ?>
<a class="out" href="<?= BASE_URL ?>/logout.php">Logout (<?= e($u['username']) ?>)</a></nav>
<main><h2><?= e($title ?? '') ?></h2><?= flash() ?>
