<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/mailer_smtp.php';

if (!function_exists('ensure_session')) {
  function ensure_session() {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  }
}

function base_url(string $path=''): string {
  $cfg = require __DIR__ . '/config.php';
  $base = rtrim($cfg['app']['base_url'] ?? '', '/');
  return $base . '/' . ltrim($path, '/');
}

function sanitize_text($s): string { return trim(filter_var($s, FILTER_UNSAFE_RAW)); }
function required($v): bool { return isset($v) && trim($v) !== ''; }
function redirect(string $to): void { header('Location: ' . $to); exit; }

function current_user_id(): ?int { ensure_session(); return $_SESSION['uid'] ?? null; }
function login_user(int $id): void { ensure_session(); $_SESSION['uid'] = $id; }
function logout_user(): void { ensure_session(); session_destroy(); }

function now(): string { return date('Y-m-d H:i:s'); }

function send_magic_link(array $user, string $token): void {
  $cfg   = require __DIR__ . '/config.php';
  $brand = $cfg['app']['brand'] ?? 'O Monstro Que Te Habita';
  $min   = (int)($cfg['app']['magic_ttl_minutes'] ?? 30);
  $host  = $_SERVER['HTTP_HOST'] ?? 'localhost';

  $link    = base_url('?page=magic&token=' . urlencode($token));
  $subject = 'Seu link mágico — ' . $brand;

  ensure_session();
  $_SESSION['__last_magic_link'] = $link;

  $from      = $cfg['app']['mail']['from'] ?? ('no-reply@'.$host);
  $to        = $user['email'];
  $transport = $cfg['app']['mail']['transport'] ?? 'mail';

  // ===== Texto alternativo (com \r\n) =====
  $textLines = [
    "Olá {$user['name']},",
    "",
    "Seu link mágico para entrar no {$brand}:",
    $link,
    "",
    "Este link expira em {$min} minutos.",
    "Se não foi você, apenas ignore este e-mail.",
  ];
  $textBody = implode("\r\n", $textLines);
  $textEncoded = function_exists('quoted_printable_encode') ? quoted_printable_encode($textBody) : $textBody;

  // ===== HTML compatível com Gmail/Outlook =====
  $bg    = '#0b0f14';
  $card  = '#0f1720';
  $text  = '#e5eef5';
  $muted = '#9fb3c8';
  $btnBg = '#3b82f6';
  $btnTx = '#ffffff';

  $preheader = "Seu link mágico expira em {$min} minutos.";
  $brandSafe = htmlspecialchars($brand, ENT_QUOTES, 'UTF-8');
  $userName  = htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8');
  $linkEsc   = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');

  $htmlBody = <<<HTML
<!doctype html>
<html lang="pt-BR">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="color-scheme" content="light dark">
<meta name="supported-color-schemes" content="light dark">
<title>{$subject}</title>
<style>
body{margin:0;padding:0;background:{$bg};color:{$text};-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;}
a{color:{$btnBg};text-decoration:none}
@media (prefers-color-scheme: dark){
  body{background:{$bg} !important;color:{$text} !important;}
}
</style>
</head>
<body style="margin:0;padding:0;background:{$bg};color:{$text};">
  <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">
    {$preheader}&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
  </div>

  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:{$bg};">
    <tr>
      <td align="center" style="padding:24px;">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:560px;background:{$card};border-radius:14px;overflow:hidden;">
          <tr>
            <td style="padding:28px 28px 12px 28px; text-align:center;">
              <div style="font:700 20px/1.2 system-ui,-apple-system,Segoe UI,Roboto; letter-spacing:.3px;">{$brandSafe}</div>
              <div style="color:{$muted};font:400 12px/1.4 system-ui,-apple-system,Segoe UI,Roboto;margin-top:4px;">Atravessando o espelho emocional</div>
            </td>
          </tr>
          <tr>
            <td style="padding:8px 28px 0 28px;">
              <div style="font:600 18px/1.4 system-ui,-apple-system,Segoe UI,Roboto;">Seu link mágico chegou</div>
              <div style="color:{$muted};font:400 14px/1.6 system-ui,-apple-system,Segoe UI,Roboto;margin-top:6px;">
                Olá {$userName},<br>
                use o botão abaixo para entrar. O link expira em <strong>{$min} minutos</strong>.
              </div>
            </td>
          </tr>
          <tr>
            <td align="center" style="padding:20px 28px 10px 28px;">
              <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                <tr>
                  <td align="center" bgcolor="{$btnBg}" style="border-radius:10px;">
                    <a href="{$linkEsc}" target="_blank"
                       style="display:inline-block;padding:14px 22px;font:600 15px/1 system-ui,-apple-system,Segoe UI,Roboto;color:{$btnTx};background:{$btnBg};border-radius:10px;">
                      Entrar agora
                    </a>
                  </td>
                </tr>
              </table>
              <div style="color:{$muted};font:400 12px/1.6 system-ui,-apple-system,Segoe UI,Roboto;margin-top:10px;">
                Se o botão não funcionar, copie e cole este link no navegador:<br>
                <a href="{$linkEsc}" style="word-break:break-all;color:{$text};">{$linkEsc}</a>
              </div>
            </td>
          </tr>
          <tr>
            <td style="padding:12px 28px 22px 28px;">
              <hr style="border:none;border-top:1px solid #213144;margin:0 0 12px 0;">
              <div style="color:{$muted};font:400 12px/1.6 system-ui,-apple-system,Segoe UI,Roboto;">
                Se você não solicitou este acesso, apenas ignore. Ninguém conseguirá entrar sem este e-mail.
              </div>
            </td>
          </tr>
        </table>
        <div style="color:{$muted};font:400 11px/1.6 system-ui,-apple-system,Segoe UI,Roboto;margin-top:12px;max-width:560px;">
          © {$brandSafe}.
        </div>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;

  // ===== SMTP preferencial =====
  if ($transport === 'smtp') {
    // Usa a nova função HTML (abaixo)
    $ok = function_exists('send_smtp_html')
      ? send_smtp_html($from, $to, $subject, $htmlBody, $textBody, $cfg['app']['mail']['smtp'] ?? [])
      // fallback: envia como texto se não existir (raro, pois criamos agora)
      : send_smtp($from, $to, $subject, $textBody, $cfg['app']['mail']['smtp'] ?? []);
    if ($ok) return;
  }

  // ===== Fallback: mail() multipart/alternative =====
  $boundary = 'bnd_'.bin2hex(random_bytes(12));
  $headers = [
    'From: '.$from,
    'Reply-To: '.$from,
    'MIME-Version: 1.0',
    'Content-Type: multipart/alternative; boundary="'.$boundary.'"'
  ];
  $params = filter_var($from, FILTER_VALIDATE_EMAIL) ? ('-f '.$from) : '';

  $message =
    "--{$boundary}\r\n".
    "Content-Type: text/plain; charset=UTF-8\r\n".
    "Content-Transfer-Encoding: ".(function_exists('quoted_printable_encode') ? "quoted-printable" : "8bit")."\r\n\r\n".
    $textEncoded."\r\n".
    "--{$boundary}\r\n".
    "Content-Type: text/html; charset=UTF-8\r\n".
    "Content-Transfer-Encoding: 8bit\r\n\r\n".
    $htmlBody."\r\n".
    "--{$boundary}--\r\n";

  @mail($to, $subject, $message, implode("\r\n", $headers), $params);
}


function create_magic_token(int $user_id): string {
  $token = bin2hex(random_bytes(32));
  $cfg = require __DIR__ . '/config.php';
  $pdo = db();
  $stmt = $pdo->prepare('INSERT INTO magic_tokens (token, user_id, expires_at) VALUES (?, ?, FROM_UNIXTIME(UNIX_TIMESTAMP() + (? * 60)))');
$stmt->execute([$token, $user_id, (int)($cfg['app']['magic_ttl_minutes'] ?? 30)]);

  return $token;
}
