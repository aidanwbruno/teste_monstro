<?php
function _best_password_algo(): string|int {
  // Usa Argon2id se estiver realmente disponível
  if (defined('PASSWORD_ARGON2ID')) {
    // Algumas hospedagens expõem a constante mas não têm a lib; testaremos de fato
    return PASSWORD_ARGON2ID;
  }
  return PASSWORD_DEFAULT;
}

function hash_password_portable(string $plain): string|false {
  $plain = (string)$plain;
  if ($plain === '') return false;

  // 1) Tenta Argon2id (se existir)
  $algo = _best_password_algo();
  $hash = @password_hash($plain, $algo);
  if (is_string($hash) && $hash !== '') return $hash;

  // 2) Fallback explícito para PASSWORD_DEFAULT (bcrypt na maioria dos hosts)
  $hash = @password_hash($plain, PASSWORD_DEFAULT);
  if (is_string($hash) && $hash !== '') return $hash;

  // 3) Falhou geral
  return false;
}

function verify_password_portable(string $plain, string $hash): bool {
  if ($hash === '' || $hash === null) return false;
  return password_verify($plain, $hash);
}
