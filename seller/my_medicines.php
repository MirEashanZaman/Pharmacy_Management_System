<?php
require_once '../session.php';
requireLogin('../login.php');
require_once '../controllers/MedicineController.php';

$controller = new MedicineController($conn);
$controller->manageMedicinesSeller();
