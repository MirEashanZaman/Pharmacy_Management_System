<?php
class OrderModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getCartItems($userId) {
        $stmt = $this->db->prepare("SELECT c.*, m.name, m.price, m.stock, m.image, m.unit, m.brand, m.requires_prescription FROM cart c JOIN medicines m ON c.medicine_id=m.id WHERE c.user_id=?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getCartCount($userId) {
        $stmt = $this->db->prepare("SELECT SUM(quantity) as c FROM cart WHERE user_id=?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['c'] ?? 0;
    }

    public function updateCartQty($cartId, $userId, $qty) {
        if ($qty <= 0) {
            $stmt = $this->db->prepare("DELETE FROM cart WHERE id=? AND user_id=?");
            $stmt->bind_param("ii", $cartId, $userId);
        } else {
            $stmt = $this->db->prepare("UPDATE cart SET quantity=? WHERE id=? AND user_id=?");
            $stmt->bind_param("iii", $qty, $cartId, $userId);
        }
        return $stmt->execute();
    }

    public function addToCart($userId, $medId, $qty = 1) {
        
        $stmt = $this->db->prepare("SELECT id, quantity FROM cart WHERE user_id=? AND medicine_id=?");
        $stmt->bind_param("ii", $userId, $medId);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $newQty = $row['quantity'] + $qty;
            $stmt2 = $this->db->prepare("UPDATE cart SET quantity=? WHERE id=?");
            $stmt2->bind_param("ii", $newQty, $row['id']);
            return $stmt2->execute();
        } else {
            $stmt2 = $this->db->prepare("INSERT INTO cart (user_id, medicine_id, quantity) VALUES (?,?,?)");
            $stmt2->bind_param("iii", $userId, $medId, $qty);
            return $stmt2->execute();
        }
    }

    public function removeCartItem($cartId, $userId) {
        $stmt = $this->db->prepare("DELETE FROM cart WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $cartId, $userId);
        return $stmt->execute();
    }

    public function clearCart($userId) {
        $stmt = $this->db->prepare("DELETE FROM cart WHERE user_id=?");
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }

    public function insertOrder($userId, $total, $address, $division, $district, $upazila, $phone, $payMethod, $prescriptionImg = null, $rxApproved = 0) {
        $stmt = $this->db->prepare("INSERT INTO orders (user_id, total_amount, delivery_address, delivery_division, delivery_district, delivery_upazila, delivery_phone, payment_method, prescription_image, rx_approved) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("idsssssssi", $userId, $total, $address, $division, $district, $upazila, $phone, $payMethod, $prescriptionImg, $rxApproved);
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        return false;
    }

    public function insertOrderItem($orderId, $medId, $qty, $price) {
        $stmt = $this->db->prepare("INSERT INTO order_items (order_id, medicine_id, quantity, price) VALUES (?,?,?,?)");
        $stmt->bind_param("iiid", $orderId, $medId, $qty, $price);
        return $stmt->execute();
    }

    public function deductMedicineStock($medId, $qty) {
        $stmt = $this->db->prepare("UPDATE medicines SET stock=stock-? WHERE id=? AND stock>=?");
        $stmt->bind_param("iii", $qty, $medId, $qty);
        return $stmt->execute();
    }

    public function getOrdersAdmin($where = "1", $limit = "") {
        $sql = "SELECT o.*, u.name as customer_name, u.phone as customer_phone FROM orders o JOIN users u ON o.user_id=u.id WHERE $where ORDER BY o.created_at DESC";
        if ($limit) {
            $sql .= " LIMIT " . intval($limit);
        }
        return $this->db->query($sql);
    }

    public function getUserOrders($userId) {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getOrderItems($orderId, $extraWhere = "1") {
        return $this->db->query("SELECT oi.*, m.name, m.image FROM order_items oi JOIN medicines m ON oi.medicine_id=m.id WHERE oi.order_id=$orderId AND $extraWhere");
    }

    public function updateOrderStatus($orderId, $status) {
        $stmt = $this->db->prepare("UPDATE orders SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $orderId);
        return $stmt->execute();
    }
}
