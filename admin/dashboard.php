<?php
require_once '../session.php';
requireLogin('../login.php');
require_once '../controllers/DashboardController.php';

$controller = new DashboardController($conn);
$controller->adminDashboard();
