<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require __DIR__.'/app/includes/helpers.php';
$page = $_GET['page'] ?? 'home';
$map = ['home','test','result','magic'];
if (!in_array($page, $map, true)) $page = 'home';
require __DIR__."/app/pages/{$page}.php";
