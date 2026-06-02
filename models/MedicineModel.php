<?php
class MedicineModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getMedicinesCount($where = "is_active=1", $types = "", $params = []) {
        $query = "SELECT COUNT(*) as c FROM medicines m WHERE $where";
        if ($params) {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc()['c'];
        }
        return $this->db->query($query)->fetch_assoc()['c'];
    }

    public function getMedicinesPaged($where = "is_active=1", $perPage = 12, $offset = 0, $types = "", $params = []) {
        $query = "SELECT m.*, u.name as seller_name FROM medicines m LEFT JOIN users u ON m.salesperson_id=u.id WHERE $where ORDER BY m.created_at DESC LIMIT $perPage OFFSET $offset";
        if ($params) {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            return $stmt->get_result();
        }
        return $this->db->query($query);
    }

    public function getMedicineById($id) {
        $stmt = $this->db->prepare("SELECT m.*, u.name as seller_name FROM medicines m LEFT JOIN users u ON m.salesperson_id=u.id WHERE m.id=? AND m.is_active=1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getAllMedicinesAdmin() {
        return $this->db->query("SELECT m.*, u.name as seller_name FROM medicines m LEFT JOIN users u ON m.salesperson_id=u.id ORDER BY m.created_at DESC");
    }

    public function getSellerMedicines($sellerId) {
        $stmt = $this->db->prepare("SELECT * FROM medicines WHERE salesperson_id=? ORDER BY created_at DESC");
        $stmt->bind_param("i", $sellerId);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getSellerLowStock($sellerId) {
        $stmt = $this->db->prepare("SELECT * FROM medicines WHERE salesperson_id=? AND stock<=10 AND is_active=1 ORDER BY stock ASC");
        $stmt->bind_param("i", $sellerId);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function insertMedicine($name, $generic, $brand, $cat, $desc, $price, $stock, $unit, $img, $rx, $sellerId) {
        $stmt = $this->db->prepare("INSERT INTO medicines (name, generic_name, brand, category, description, price, stock, unit, image, requires_prescription, salesperson_id) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("sssssdisiii", $name, $generic, $brand, $cat, $desc, $price, $stock, $unit, $img, $rx, $sellerId);
        return $stmt->execute();
    }

    public function updateMedicine($id, $name, $generic, $brand, $cat, $desc, $price, $stock, $unit, $img, $rx, $sellerId) {
        $stmt = $this->db->prepare("UPDATE medicines SET name=?, generic_name=?, brand=?, category=?, description=?, price=?, stock=?, unit=?, image=?, requires_prescription=? WHERE id=? AND salesperson_id=?");
        $stmt->bind_param("sssssdisiiii", $name, $generic, $brand, $cat, $desc, $price, $stock, $unit, $img, $rx, $id, $sellerId);
        return $stmt->execute();
    }

    public function toggleMedicineStatus($id) {
        $stmt = $this->db->prepare("UPDATE medicines SET is_active = NOT is_active WHERE id=?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
