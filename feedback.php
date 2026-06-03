<?php
require_once 'session.php';
require_once 'controllers/FeedbackController.php';

$controller = new FeedbackController($conn);
$controller->submitFeedback();
