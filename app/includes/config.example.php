<?php
return [
  'site_url' => 'https://seusite.com',
  'db' => [
    'host' => '127.0.0.1',
    'port' => 3306,
    'name' => 'nome_do_banco',
    'user' => 'usuario_do_banco',
    'pass' => 'senha_do_banco',
    'charset' => 'utf8mb4'
  ],
  'app' => [
    'debug' => true,
    'base_url' => 'https://seusite.com/',
    'magic_ttl_minutes' => 30,
    'mail' => [
      'transport' => 'smtp',
      'from'      => 'no-reply@localhost',
      'smtp' => [
        'host'     => 'mail.host.com',
        'port'     => 587,
        'secure'   => 'tls',           // 'tls' ou 'ssl'
        'username' => 'usuario_do_email',
        'password' => 'senha_do_usuario',
        'auth'     => true
      ]
    ]
  ]
];

