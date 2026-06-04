<?php
require_once 'session.php';
require_once 'controllers/MedicineController.php';

$controller = new MedicineController($conn);
$controller->listMedicines();
