<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__.'/../vendor/PHPMailer/src/Exception.php';
require_once __DIR__.'/../vendor/PHPMailer/src/PHPMailer.php';
require_once __DIR__.'/../vendor/PHPMailer/src/SMTP.php';

/**
 * Envio SMTP texto puro (retrocompatível).
 */
function send_smtp(string $from, string $to, string $subject, string $body, array $cfg): bool {
  $m = new PHPMailer(true);
  try {
    $m->isSMTP();
    $m->Host       = $cfg['host']     ?? '';
    $m->Port       = (int)($cfg['port'] ?? 587);
    $m->SMTPAuth   = (bool)($cfg['auth'] ?? true);
    $m->Username   = $cfg['username'] ?? '';
    $m->Password   = $cfg['password'] ?? '';
    $secure        = $cfg['secure']   ?? 'tls';
    if ($secure) $m->SMTPSecure = $secure;

    $m->CharSet = 'UTF-8';
    $m->setFrom($from);
    $m->addAddress($to);
    $m->Subject = $subject;
    $m->isHTML(false);
    $m->Body    = $body;
    $m->AltBody = $body;

    return $m->send();
  } catch (Exception $e) {
    return false;
  }
}

/**
 * Envio SMTP com HTML + AltBody (recomendado).
 */
function send_smtp_html(string $from, string $to, string $subject, string $html, string $alt, array $cfg): bool {
  $m = new PHPMailer(true);
  try {
    $m->isSMTP();
    $m->Host       = $cfg['host']     ?? '';
    $m->Port       = (int)($cfg['port'] ?? 587);
    $m->SMTPAuth   = (bool)($cfg['auth'] ?? true);
    $m->Username   = $cfg['username'] ?? '';
    $m->Password   = $cfg['password'] ?? '';
    $secure        = $cfg['secure']   ?? 'tls';
    if ($secure) $m->SMTPSecure = $secure;

    $m->CharSet = 'UTF-8';
    $m->setFrom($from);
    $m->addAddress($to);
    $m->Subject = $subject;

    $m->isHTML(true);
    $m->Body    = $html;
    $m->AltBody = $alt ?: strip_tags($html);

    return $m->send();
  } catch (Exception $e) {
    return false;
  }
}
