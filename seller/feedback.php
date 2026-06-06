<?php
require_once '../session.php';
requireLogin('../login.php');
require_once '../controllers/FeedbackController.php';

$controller = new FeedbackController($conn);
$controller->viewFeedbackAdmin();
