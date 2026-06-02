<?php
class UserModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getUserByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email=? AND is_active=1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getUserById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function checkEmailExists($email) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function insertUser($name, $email, $hash, $role, $phone, $address, $division, $district, $upazila) {
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password, role, phone, address, division, district, upazila) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("sssssssss", $name, $email, $hash, $role, $phone, $address, $division, $district, $upazila);
        return $stmt->execute();
    }

    public function updateUser($id, $name, $phone, $division, $district, $upazila, $address, $pic) {
        $stmt = $this->db->prepare("UPDATE users SET name=?, phone=?, division=?, district=?, upazila=?, address=?, profile_pic=? WHERE id=?");
        $stmt->bind_param("sssssssi", $name, $phone, $division, $district, $upazila, $address, $pic, $id);
        return $stmt->execute();
    }

    public function updatePassword($id, $hash) {
        $stmt = $this->db->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $hash, $id);
        return $stmt->execute();
    }

    public function getAllUsers($where = "1") {
        return $this->db->query("SELECT * FROM users WHERE $where ORDER BY created_at DESC");
    }

    public function deactivateUser($id) {
        $stmt = $this->db->prepare("UPDATE users SET is_active=0 WHERE id=?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function restoreUser($id) {
        $stmt = $this->db->prepare("UPDATE users SET is_active=1 WHERE id=?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
