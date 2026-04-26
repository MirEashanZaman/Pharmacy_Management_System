<?php
require_once 'session.php';
requireLogin('login.php');
$user = getUser();
if ($user['role']!=='customer') { header("Location: index.php"); exit; }

$medId = intval($_GET['id'] ?? 0);
$buy = isset($_GET['buy']);
if (!$medId) { header("Location: medicines.php"); exit; }

$stmt = $conn->prepare("SELECT * FROM medicines WHERE id=? AND is_active=1 AND stock>0");
$stmt->bind_param("i", $medId);
$stmt->execute();
$med = $stmt->get_result()->fetch_assoc();
if (!$med) { header("Location: medicines.php"); exit; }


$check = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id=? AND medicine_id=?");
$check->bind_param("ii", $user['id'], $medId);
$check->execute();
$existing = $check->get_result()->fetch_assoc();

if ($existing) {
    $newQty = min($existing['quantity']+1, $med['stock']);
    $upd = $conn->prepare("UPDATE cart SET quantity=? WHERE id=?");
    $upd->bind_param("ii", $newQty, $existing['id']);
    $upd->execute();
} else {
    $ins = $conn->prepare("INSERT INTO cart (user_id,medicine_id,quantity) VALUES (?,?,1)");
    $ins->bind_param("ii", $user['id'], $medId);
    $ins->execute();
}

if ($buy) {
    header("Location: cart.php");
} else {
    header("Location: medicines.php?added=1");
}
exit;
?>
