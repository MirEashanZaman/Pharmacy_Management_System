<?php
require_once 'session.php';
requireLogin('login.php');
require_once 'controllers/AuthController.php';

$controller = new AuthController($conn);
$controller->profile();
