<?php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/MedicineModel.php';
require_once __DIR__ . '/../models/OrderModel.php';
require_once __DIR__ . '/../models/FeedbackModel.php';

class DashboardController {
    private $db;
    private $userModel;
    private $medicineModel;
    private $orderModel;
    private $feedbackModel;

    public function __construct($db) {
        $this->db = $db;
        $this->userModel = new UserModel($db);
        $this->medicineModel = new MedicineModel($db);
        $this->orderModel = new OrderModel($db);
        $this->feedbackModel = new FeedbackModel($db);
    }

    public function adminDashboard() {
        $user = $this->userModel->getUserById($_SESSION['user_id']);
        if ($user['role']!=='admin') { header("Location: ../index.php"); exit; }

        $totalUsers = $this->userModel->getAllUsers("role='customer'")->num_rows;
        $totalSellers = $this->userModel->getAllUsers("role='salesperson'")->num_rows;
        $totalMeds = $this->medicineModel->getMedicinesCount("is_active=1");
        $totalOrders = $this->db->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'];
        $totalRevenue = $this->db->query("SELECT SUM(total_amount) as r FROM orders WHERE status!='cancelled'")->fetch_assoc()['r'] ?? 0;
        $pendingOrders = $this->db->query("SELECT COUNT(*) as c FROM orders WHERE status='pending'")->fetch_assoc()['c'];
        $recentOrders = $this->orderModel->getOrdersAdmin("1", 10);
        $recentUsers = $this->db->query("SELECT * FROM users WHERE role='customer' ORDER BY created_at DESC LIMIT 5");
        $recentFeedback = $this->feedbackModel->getAllFeedback(); 

        $statusColors = ['pending'=>'warning','processing'=>'info','shipped'=>'primary','delivered'=>'success','cancelled'=>'danger'];
        
        // Chart Data queries
        $weeklyDays = [];
        $weeklyRev = [];
        $weeklyRevenueQuery = $this->db->query("
            SELECT DATE_FORMAT(created_at, '%a') as day_name, SUM(total_amount) as revenue 
            FROM orders 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND status != 'cancelled'
            GROUP BY DATE(created_at), day_name 
            ORDER BY DATE(created_at) ASC
        ");
        while ($row = $weeklyRevenueQuery->fetch_assoc()) {
            $weeklyDays[] = $row['day_name'];
            $weeklyRev[] = (float)$row['revenue'];
        }

        $topMedNames = [];
        $topMedQtys = [];
        $topMedsQuery = $this->db->query("
            SELECT m.name, SUM(oi.quantity) as qty 
            FROM order_items oi 
            JOIN medicines m ON oi.medicine_id = m.id 
            JOIN orders o ON oi.order_id = o.id
            WHERE o.status != 'cancelled'
            GROUP BY oi.medicine_id, m.name 
            ORDER BY qty DESC 
            LIMIT 5
        ");
        while ($row = $topMedsQuery->fetch_assoc()) {
            $topMedNames[] = $row['name'];
            $topMedQtys[] = (int)$row['qty'];
        }

        $statusLabels = [];
        $statusCounts = [];
        $statusDistQuery = $this->db->query("
            SELECT status, COUNT(*) as count 
            FROM orders 
            GROUP BY status
        ");
        while ($row = $statusDistQuery->fetch_assoc()) {
            $statusLabels[] = ucfirst($row['status']);
            $statusCounts[] = (int)$row['count'];
        }

        // Expiry alerts queries for admin
        $redAlerts = $this->db->query("SELECT COUNT(*) as c FROM medicines WHERE is_active=1 AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetch_assoc()['c'] ?? 0;
        $yellowAlerts = $this->db->query("SELECT COUNT(*) as c FROM medicines WHERE is_active=1 AND expiry_date > DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)")->fetch_assoc()['c'] ?? 0;
        $greenAlerts = $this->db->query("SELECT COUNT(*) as c FROM medicines WHERE is_active=1 AND expiry_date > DATE_ADD(CURDATE(), INTERVAL 90 DAY)")->fetch_assoc()['c'] ?? 0;

        $expiringMeds = $this->db->query("
            SELECT *, DATEDIFF(expiry_date, CURDATE()) as days_left 
            FROM medicines 
            WHERE is_active=1 AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 90 DAY) 
            ORDER BY expiry_date ASC 
            LIMIT 5
        ");

        $viewFile = __DIR__ . '/../views/admin/dashboard.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "Admin Dashboard View not found.";
        }
    }

    public function sellerDashboard() {
        $user = $this->userModel->getUserById($_SESSION['user_id']);
        if ($user['role']!=='salesperson') { header("Location: ../index.php"); exit; }

        $myMeds = $this->medicineModel->getMedicinesCount("salesperson_id={$user['id']} AND is_active=1");
        $totalStock = $this->db->query("SELECT SUM(stock) as s FROM medicines WHERE salesperson_id={$user['id']} AND is_active=1")->fetch_assoc()['s'] ?? 0;
        
        $myMedIds = $this->db->query("SELECT GROUP_CONCAT(id) as ids FROM medicines WHERE salesperson_id={$user['id']}")->fetch_assoc()['ids'];
        $myOrders = 0;
        $myRevenue = 0;
        if ($myMedIds) {
            $myOrders = $this->db->query("SELECT COUNT(DISTINCT order_id) as c FROM order_items WHERE medicine_id IN ($myMedIds)")->fetch_assoc()['c'];
            $myRevenue = $this->db->query("SELECT SUM(oi.price*oi.quantity) as r FROM order_items oi JOIN orders o ON oi.order_id=o.id WHERE oi.medicine_id IN ($myMedIds) AND o.status!='cancelled'")->fetch_assoc()['r'] ?? 0;
        }

        $recentMeds = $this->db->query("SELECT * FROM medicines WHERE salesperson_id={$user['id']} ORDER BY created_at DESC LIMIT 5");
        $lowStock = $this->medicineModel->getSellerLowStock($user['id']);

        // Chart Data queries
        $weeklyDays = [];
        $weeklyRev = [];
        $topMedNames = [];
        $topMedQtys = [];
        $statusLabels = [];
        $statusCounts = [];

        if ($myMedIds) {
            $weeklyRevenueQuery = $this->db->query("
                SELECT DATE_FORMAT(o.created_at, '%a') as day_name, SUM(oi.price * oi.quantity) as revenue
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                WHERE oi.medicine_id IN ($myMedIds) AND o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND o.status != 'cancelled'
                GROUP BY DATE(o.created_at), day_name
                ORDER BY DATE(o.created_at) ASC
            ");
            while ($row = $weeklyRevenueQuery->fetch_assoc()) {
                $weeklyDays[] = $row['day_name'];
                $weeklyRev[] = (float)$row['revenue'];
            }

            $topMedsQuery = $this->db->query("
                SELECT m.name, SUM(oi.quantity) as qty 
                FROM order_items oi 
                JOIN medicines m ON oi.medicine_id = m.id 
                JOIN orders o ON oi.order_id = o.id
                WHERE oi.medicine_id IN ($myMedIds) AND o.status != 'cancelled'
                GROUP BY oi.medicine_id, m.name 
                ORDER BY qty DESC 
                LIMIT 5
            ");
            while ($row = $topMedsQuery->fetch_assoc()) {
                $topMedNames[] = $row['name'];
                $topMedQtys[] = (int)$row['qty'];
            }

            $statusDistQuery = $this->db->query("
                SELECT o.status, COUNT(DISTINCT o.id) as count 
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                WHERE oi.medicine_id IN ($myMedIds)
                GROUP BY o.status
            ");
            while ($row = $statusDistQuery->fetch_assoc()) {
                $statusLabels[] = ucfirst($row['status']);
                $statusCounts[] = (int)$row['count'];
            }
        }

        // Expiry alerts queries for salesperson
        $redAlerts = $this->db->query("SELECT COUNT(*) as c FROM medicines WHERE salesperson_id={$user['id']} AND is_active=1 AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetch_assoc()['c'] ?? 0;
        $yellowAlerts = $this->db->query("SELECT COUNT(*) as c FROM medicines WHERE salesperson_id={$user['id']} AND is_active=1 AND expiry_date > DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)")->fetch_assoc()['c'] ?? 0;
        $greenAlerts = $this->db->query("SELECT COUNT(*) as c FROM medicines WHERE salesperson_id={$user['id']} AND is_active=1 AND expiry_date > DATE_ADD(CURDATE(), INTERVAL 90 DAY)")->fetch_assoc()['c'] ?? 0;

        $expiringMeds = $this->db->query("
            SELECT *, DATEDIFF(expiry_date, CURDATE()) as days_left 
            FROM medicines 
            WHERE salesperson_id={$user['id']} AND is_active=1 AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 90 DAY) 
            ORDER BY expiry_date ASC 
            LIMIT 5
        ");

        $viewFile = __DIR__ . '/../views/seller/dashboard.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "Seller Dashboard View not found.";
        }
    }
}
