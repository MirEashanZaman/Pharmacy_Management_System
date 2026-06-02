<?php
require_once 'session.php';
if (isLoggedIn()) {
    $u = getUser();
    if ($u['role']==='admin') header("Location: admin/dashboard.php");
    elseif ($u['role']==='salesperson') header("Location: seller/dashboard.php");
    else header("Location: index.php");
    exit;
}
require_once 'controllers/AuthController.php';

$controller = new AuthController($conn);
$controller->login();
