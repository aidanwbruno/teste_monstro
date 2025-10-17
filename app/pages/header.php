<?php if (session_status() !== PHP_SESSION_ACTIVE) session_start(); ?>
<!doctype html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta property="og:title" content="Teste — O Monstro Que Te Habita" />
  <meta property="og:description" content="Descubra seu monstro dominante e leia a interpretação completa." />
  <meta property="og:url" content="https://wnortepsi.com" />
  <meta property="og:type" content="website" />
  <title>O Monstro Que Te Habita</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= base_url('public/css/custom.css') ?>" rel="stylesheet">
</head>
<body class="bg-black text-light">
<nav class="navbar navbar-expand-lg navbar-dark border-bottom border-secondary sticky-top monster-blur">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= base_url() ?>">Monstro</a>
    <span class="navbar-text small opacity-75">um rito de nomeação</span>
    <div class="ms-auto d-flex gap-2">
      <?php if (!empty($_SESSION['uid'])): ?>
        <a class="btn btn-sm btn-outline-light" href="<?= base_url('?action=logout') ?>">Sair</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
<main class="container py-4">
