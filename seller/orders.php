<?php
require_once '../session.php';
requireLogin('../login.php');
require_once '../controllers/OrderController.php';

$controller = new OrderController($conn);
$controller->manageOrdersSeller();
