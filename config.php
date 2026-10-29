<?php
// ====== KONFIGURASI (sesuaikan) ======
define('DEBUG', true);   // ubah ke false kalau sudah dipakai sungguhan
define('BASE_URL', '/sipn');           // nama folder di htdocs
$DB = ['host'=>'localhost','name'=>'db_siacad_smk_2','user'=>'root','pass'=>''];
// =====================================
session_start();
if (DEBUG) { ini_set('display_errors', '1'); error_reporting(E_ALL); }

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function redirect($p){ header('Location: '.BASE_URL.'/'.$p); exit; }

try {
  $pdo = new PDO("mysql:host={$DB['host']};dbname={$DB['name']};charset=utf8mb4", $DB['user'], $DB['pass'],
    [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
} catch (PDOException $ex) { die('Koneksi database gagal: '.e($ex->getMessage())); }

function q($sql, $p = []){ global $pdo; $s = $pdo->prepare($sql); $s->execute($p); return $s; }

function flash($msg = null, $type = 'ok'){
  if ($msg !== null){ $_SESSION['flash'] = [$msg, $type]; return; }
  if (isset($_SESSION['flash'])){ [$m,$t] = $_SESSION['flash']; unset($_SESSION['flash']); return "<div class='alert $t'>".e($m)."</div>"; }
  return '';
}
function csrf(){ if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16)); return $_SESSION['csrf']; }
function csrf_field(){ return '<input type="hidden" name="csrf" value="'.csrf().'">'; }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals(csrf(), $_POST['csrf'] ?? '')) die('Token CSRF tidak valid');

function require_login($roles = []){
  if (empty($_SESSION['user'])) redirect('login.php');
  if ($roles && !in_array($_SESSION['user']['role'], $roles)){ http_response_code(403); die('Akses ditolak'); }
}
function del_btn($id){
  return '<form method="post" class="inline" onsubmit="return confirm(\'Yakin hapus data ini?\')">'.csrf_field().
    '<input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="'.(int)$id.'"><button class="btn red">Hapus</button></form>';
}
function jk_select($v = ''){
  return '<select name="jenis_kelamin" required><option value="L"'.($v==='L'?' selected':'').'>Laki-laki</option><option value="P"'.($v==='P'?' selected':'').'>Perempuan</option></select>';
}
