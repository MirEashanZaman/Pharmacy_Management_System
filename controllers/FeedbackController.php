<?php
require_once __DIR__ . '/../models/FeedbackModel.php';
require_once __DIR__ . '/../models/UserModel.php';

class FeedbackController {
    private $db;
    private $feedbackModel;
    private $userModel;

    public function __construct($db) {
        $this->db = $db;
        $this->feedbackModel = new FeedbackModel($db);
        $this->userModel = new UserModel($db);
    }

    public function submitFeedback() {
        $user = isset($_SESSION['user_id']) ? $this->userModel->getUserById($_SESSION['user_id']) : null;
        $success = '';
        $error = '';

        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $name = trim($_POST['name']??'');
            $email = trim($_POST['email']??'');
            $subject = trim($_POST['subject']??'');
            $message = trim($_POST['message']??'');
            if (empty($name)||empty($email)||empty($message)) {
                $error = 'Name, email and message are required.';
            } else {
                $uid = $user ? $user['id'] : null;
                if ($this->feedbackModel->insertFeedback($uid, $name, $email, $subject, $message)) {
                    $success = 'Thank you for your feedback! We will get back to you soon.';
                } else {
                    $error = 'Failed to submit feedback. Please try again.';
                }
            }
        }

        $viewFile = __DIR__ . '/../views/feedback.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "Feedback View not found.";
        }
    }

    public function viewFeedbackAdmin() {
        $user = $this->userModel->getUserById($_SESSION['user_id']);
        if ($user['role']!=='admin') { header("Location: ../index.php"); exit; }

        $success = '';
        if (isset($_GET['delete'])) {
            $fid = intval($_GET['delete']);
            if ($this->feedbackModel->deleteFeedback($fid)) {
                $success = 'Feedback deleted.';
                header("Location: feedback.php?deleted=1");
                exit;
            }
        }

        $feedbacks = $this->feedbackModel->getAllFeedback();

        $viewFile = __DIR__ . '/../views/admin/feedback.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "Admin Feedback View not found.";
        }
    }
}
