<?php
require_once __DIR__.'/../includes/helpers.php';
$token = $_GET['token'] ?? '';
if (!$token || !preg_match('/^[a-f0-9]{64}$/', $token)) { http_response_code(400); exit('Token inválido.'); }

$pdo = db();
$stmt = $pdo->prepare(
  'SELECT user_id 
     FROM magic_tokens 
    WHERE token = ?
      AND used_at IS NULL
      AND expires_at > NOW()
    LIMIT 1'
);
$stmt->execute([$token]);
$row = $stmt->fetch();

if (!$row) {
  // Descobrir motivo (debug opcional)
  $why = $pdo->prepare('SELECT used_at, expires_at FROM magic_tokens WHERE token=? LIMIT 1');
  $why->execute([$token]);
  $w = $why->fetch();
  if (!$w) { http_response_code(404); exit('Token não encontrado.'); }
  if (!empty($w['used_at'])) { http_response_code(410); exit('Token já usado.'); }
  if (strtotime($w['expires_at']) <= time()) { http_response_code(410); exit('Token expirado.'); }
  http_response_code(400); exit('Token inválido.');
}

$pdo->prepare('UPDATE magic_tokens SET used_at = NOW() WHERE token = ?')->execute([$token]);
login_user((int)$row['user_id']);
redirect(base_url('?page=test'));
