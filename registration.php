<?php
require_once 'session.php';
if (isLoggedIn()) { header("Location: index.php"); exit; }
require_once 'controllers/AuthController.php';

$controller = new AuthController($conn);
$controller->register();
