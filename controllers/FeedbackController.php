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
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit;
        }
        $user = $this->userModel->getUserById($_SESSION['user_id']);
        $success = '';
        $error = '';
        $deliveredMedicines = [];
        $userReviews = [];

        if ($user['role'] !== 'customer') {
            $error = 'Only customers can leave reviews/feedback on products.';
        } else {
            // Fetch delivered medicines
            $res = $this->feedbackModel->getDeliveredMedicines($user['id']);
            while ($row = $res->fetch_assoc()) {
                $deliveredMedicines[] = $row;
            }

            // Fetch user reviews
            $resReviews = $this->feedbackModel->getUserReviews($user['id']);
            while ($row = $resReviews->fetch_assoc()) {
                $userReviews[] = $row;
            }

            // Delete Review Handler
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review'])) {
                if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
                    die("CSRF Token validation failed.");
                }
                $rid = intval($_POST['review_id'] ?? 0);
                if ($this->feedbackModel->deleteReview($rid, $user['id'])) {
                    $success = 'Your review has been deleted. You can now submit a new review for this product if you wish.';
                    
                    // Refresh lists
                    $deliveredMedicines = [];
                    $res = $this->feedbackModel->getDeliveredMedicines($user['id']);
                    while ($row = $res->fetch_assoc()) {
                        $deliveredMedicines[] = $row;
                    }
                    $userReviews = [];
                    $resReviews = $this->feedbackModel->getUserReviews($user['id']);
                    while ($row = $resReviews->fetch_assoc()) {
                        $userReviews[] = $row;
                    }
                } else {
                    $error = 'Failed to delete review.';
                }
            }

            // Edit review loader
            $editReview = null;
            if (isset($_GET['edit_review'])) {
                $rid = intval($_GET['edit_review']);
                foreach ($userReviews as $ur) {
                    if (intval($ur['id']) === $rid) {
                        $editReview = $ur;
                        break;
                    }
                }
            }

            // Update Review Handler
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_review'])) {
                if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
                    die("CSRF Token validation failed.");
                }
                $_POST = sanitizeInput($_POST);
                $rid = intval($_POST['review_id'] ?? 0);
                $rating = intval($_POST['rating'] ?? 0);
                $comment = trim($_POST['comment'] ?? '');

                if ($rating < 1 || $rating > 5) {
                    $error = 'Please select a valid rating (1-5 stars).';
                } else {
                    if ($this->feedbackModel->updateReview($rid, $user['id'], $rating, $comment)) {
                        $success = 'Your review has been updated.';
                        $editReview = null;
                        
                        // Refresh user reviews
                        $userReviews = [];
                        $resReviews = $this->feedbackModel->getUserReviews($user['id']);
                        while ($row = $resReviews->fetch_assoc()) {
                            $userReviews[] = $row;
                        }
                    } else {
                        $error = 'Failed to update review.';
                    }
                }
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
                if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
                    die("CSRF Token validation failed.");
                }
                $_POST = sanitizeInput($_POST);

                $medId = intval($_POST['medicine_id'] ?? 0);
                $rating = intval($_POST['rating'] ?? 0);
                $comment = trim($_POST['comment'] ?? '');

                // Find medicine in the list of delivered ones to verify eligibility and get order_id
                $eligible = null;
                foreach ($deliveredMedicines as $med) {
                    if (intval($med['id']) === $medId) {
                        $eligible = $med;
                        break;
                    }
                }

                if (!$eligible) {
                    $error = 'You can only review medicines you have purchased and received, and not reviewed yet.';
                } elseif ($rating < 1 || $rating > 5) {
                    $error = 'Please select a valid rating (1-5 stars).';
                } else {
                    $orderId = intval($eligible['order_id']);
                    if ($this->feedbackModel->insertReview($user['id'], $medId, $orderId, $rating, $comment)) {
                        // Log inside feedback table so it shows on the admin feedback page too
                        $subject = "Product Review: " . $eligible['name'];
                        $message = "Reviewed medicine: " . $eligible['name'] . "\nRating: " . $rating . " Stars\nComment: " . $comment;
                        $this->feedbackModel->insertFeedback($user['id'], $user['name'], $user['email'], $subject, $message);
                        
                        $success = 'Thank you! Your product review has been submitted.';
                        
                        // Refresh the delivered list and user reviews
                        $deliveredMedicines = [];
                        $res = $this->feedbackModel->getDeliveredMedicines($user['id']);
                        while ($row = $res->fetch_assoc()) {
                            $deliveredMedicines[] = $row;
                        }
                        $userReviews = [];
                        $resReviews = $this->feedbackModel->getUserReviews($user['id']);
                        while ($row = $resReviews->fetch_assoc()) {
                            $userReviews[] = $row;
                        }
                    } else {
                        $error = 'Failed to submit review. Please try again.';
                    }
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
        if ($user['role']!=='admin' && $user['role']!=='salesperson') { header("Location: ../index.php"); exit; }

        $success = '';
        if (isset($_GET['delete'])) {
            if ($user['role'] !== 'admin') {
                die("Access Denied: Only administrators can delete feedback.");
            }
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
