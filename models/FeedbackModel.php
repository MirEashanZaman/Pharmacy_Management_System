<?php
class FeedbackModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function insertFeedback($userId, $name, $email, $subject, $message) {
        $stmt = $this->db->prepare("INSERT INTO feedback (user_id, name, email, subject, message) VALUES (?,?,?,?,?)");
        $stmt->bind_param("issss", $userId, $name, $email, $subject, $message);
        return $stmt->execute();
    }

    public function getAllFeedback() {
        return $this->db->query("SELECT f.*, u.name as user_name FROM feedback f LEFT JOIN users u ON f.user_id=u.id ORDER BY f.created_at DESC");
    }

    public function deleteFeedback($id) {
        $stmt = $this->db->prepare("DELETE FROM feedback WHERE id=?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    
    public function getReviewsForMedicine($medId) {
        return $this->db->query("SELECT r.*, u.name as reviewer_name FROM reviews r JOIN users u ON r.user_id=u.id WHERE r.medicine_id=$medId ORDER BY r.created_at DESC");
    }

    public function getAverageRating($medId) {
        return $this->db->query("SELECT AVG(rating) as avg FROM reviews WHERE medicine_id=$medId")->fetch_assoc()['avg'];
    }

    public function hasPurchasedMedicine($userId, $medId) {
        $stmt = $this->db->prepare("SELECT o.id FROM orders o JOIN order_items oi ON o.id=oi.order_id WHERE o.user_id=? AND oi.medicine_id=? AND o.status='delivered' LIMIT 1");
        $stmt->bind_param("ii", $userId, $medId);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            return $res->fetch_assoc()['id'];
        }
        return false;
    }

    public function hasReviewedMedicine($userId, $medId) {
        $stmt = $this->db->prepare("SELECT id FROM reviews WHERE user_id=? AND medicine_id=?");
        $stmt->bind_param("ii", $userId, $medId);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function insertReview($userId, $medId, $orderId, $rating, $comment) {
        $stmt = $this->db->prepare("INSERT INTO reviews (user_id, medicine_id, order_id, rating, comment) VALUES (?,?,?,?,?)");
        $stmt->bind_param("iiiis", $userId, $medId, $orderId, $rating, $comment);
        return $stmt->execute();
    }

    public function getDeliveredMedicines($userId) {
        $query = "SELECT DISTINCT m.id, m.name, m.brand, m.image, o.id as order_id 
                  FROM orders o 
                  JOIN order_items oi ON o.id = oi.order_id 
                  JOIN medicines m ON oi.medicine_id = m.id 
                  WHERE o.user_id = ? AND o.status = 'delivered'
                  AND NOT EXISTS (
                      SELECT 1 FROM reviews r 
                      WHERE r.user_id = ? AND r.medicine_id = m.id
                  )";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getUserReviews($userId) {
        $stmt = $this->db->prepare("SELECT r.*, m.name as medicine_name, m.brand as medicine_brand FROM reviews r JOIN medicines m ON r.medicine_id = m.id WHERE r.user_id = ? ORDER BY r.created_at DESC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function deleteReview($reviewId, $userId) {
        $stmt = $this->db->prepare("DELETE FROM reviews WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $reviewId, $userId);
        return $stmt->execute();
    }

    public function updateReview($reviewId, $userId, $rating, $comment) {
        $stmt = $this->db->prepare("UPDATE reviews SET rating=?, comment=? WHERE id=? AND user_id=?");
        $stmt->bind_param("isii", $rating, $comment, $reviewId, $userId);
        return $stmt->execute();
    }
}
