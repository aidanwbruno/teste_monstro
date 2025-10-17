<?php
if (!function_exists('ensure_session')) {
  function ensure_session() {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  }
}

function csrf_token(): string {
  ensure_session();
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf'];
}

function csrf_input(): string {
  return '<input type="hidden" name="csrf" value="'.htmlspecialchars(csrf_token(), ENT_QUOTES).'">';
}

function csrf_check(): void {
  ensure_session();
  $ok = isset($_POST['csrf']) && hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf']);
  if (!$ok) {
    http_response_code(400);
    exit('CSRF inválido.');
  }
}
