<?php
require_once 'session.php';
requireLogin('login.php');
require_once 'models/OrderModel.php';
require_once 'models/UserModel.php';

$userModel = new UserModel($conn);
$orderModel = new OrderModel($conn);
$user = $userModel->getUserById($_SESSION['user_id']);
if (!$user || $user['role'] !== 'customer') {
    header("Location: index.php");
    exit;
}

$checkoutData = $_SESSION['checkout_data'] ?? null;
if (!$checkoutData) {
    header("Location: cart.php");
    exit;
}

$total = $checkoutData['total'];
$payMethod = $checkoutData['payment_method'];

$methodNames = ['bkash' => 'bKash', 'nagad' => 'Nagad', 'rocket' => 'Rocket'];
$methodColors = ['bkash' => '#e2136e', 'nagad' => '#f69220', 'rocket' => '#7b2cbf'];
$methodName = $methodNames[$payMethod] ?? 'Mobile Payment';
$themeColor = $methodColors[$payMethod] ?? '#56488d';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    $mobile = trim($_POST['wallet_number'] ?? '');
    $pin = trim($_POST['pin_code'] ?? '');

    if (empty($mobile) || empty($pin)) {
        $error = 'Please enter your account details.';
    } elseif (strlen($mobile) < 11) {
        $error = 'Please enter a valid 11-digit mobile number.';
    } else {
        // Retrieve cart items to insert in transaction
        $items = $orderModel->getCartItems($user['id']);
        $itemsList = [];
        while($row = $items->fetch_assoc()) { 
            $itemsList[] = $row; 
        }

        if (empty($itemsList)) {
            $error = 'Your cart is empty.';
        } else {
            $conn->begin_transaction();
            try {
                $orderId = $orderModel->insertOrder(
                    $user['id'], 
                    $total, 
                    $checkoutData['del_address'], 
                    $checkoutData['del_division'], 
                    $checkoutData['del_district'], 
                    $checkoutData['del_upazila'], 
                    $checkoutData['del_phone'], 
                    $payMethod, 
                    $checkoutData['prescription_img'], 
                    0
                );

                if ($orderId) {
                    foreach($itemsList as $item) {
                        $orderModel->insertOrderItem($orderId, $item['medicine_id'], $item['quantity'], $item['price']);
                        $orderModel->deductMedicineStock($item['medicine_id'], $item['quantity']);
                    }
                    $orderModel->clearCart($user['id']);
                    $conn->commit();
                    
                    unset($_SESSION['checkout_data']);
                    header("Location: orders.php?success=Order%20%23" . $orderId . "%20placed%20successfully!");
                    exit;
                } else {
                    throw new Exception("Insert order failed");
                }
            } catch(Exception $e) {
                $conn->rollback();
                $error = 'Payment validation failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($methodName) ?> Gateway - Pharmacy Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: #f3f2fa;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .gateway-card {
            background: white;
            border-radius: 20px;
            width: min(440px, 100%);
            box-shadow: 0 15px 35px rgba(60, 48, 112, 0.12);
            overflow: hidden;
            border: 1px solid rgba(60, 48, 112, 0.08);
        }
        .gateway-header {
            background: <?= $themeColor ?>;
            color: white;
            padding: 28px;
            text-align: center;
            position: relative;
        }
        .gateway-logo-text {
            font-size: 2.2rem;
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 4px;
        }
        .gateway-amount {
            background: rgba(255, 255, 255, 0.15);
            padding: 8px 16px;
            border-radius: 20px;
            display: inline-block;
            font-size: 0.95rem;
            font-weight: 700;
            margin-top: 10px;
        }
        .gateway-body {
            padding: 32px;
        }
        .gateway-info {
            text-align: center;
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 24px;
        }
        .btn-confirm {
            background: <?= $themeColor ?>;
            color: white;
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            margin-top: 16px;
            transition: var(--transition);
        }
        .btn-confirm:hover {
            filter: brightness(0.9);
            transform: translateY(-1px);
        }
        .gateway-footer {
            text-align: center;
            padding: 16px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
            font-size: 0.8rem;
            color: #888;
        }
        body.dark-mode .gateway-card {
            background: #181528;
            border-color: rgba(187, 174, 255, 0.1);
        }
        body.dark-mode .gateway-footer {
            background: #151126;
            border-top-color: rgba(187, 174, 255, 0.05);
        }
    </style>
</head>
<body>

<div class="gateway-card">
    <div class="gateway-header">
        <div class="gateway-logo-text"><?= htmlspecialchars($methodName) ?></div>
        <p style="font-size: 0.85rem; opacity: 0.9;">Merchant: Pharmacy Management System</p>
        <div class="gateway-amount">Amount: ৳<?= number_format($total, 2) ?></div>
    </div>
    
    <div class="gateway-body">
        <div class="gateway-info">
            Enter your <?= htmlspecialchars($methodName) ?> account number and credentials to authorize the secure transaction.
        </div>

        <?php if($error): ?>
            <div class="alert alert-danger" style="margin-bottom: 20px; font-size: 0.85rem;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label" style="font-size:0.85rem;"><?= htmlspecialchars($methodName) ?> Account Number</label>
                <input type="tel" name="wallet_number" class="form-control" placeholder="e.g. 01XXXXXXXXX" minlength="11" maxlength="11" required value="<?= htmlspecialchars($_POST['wallet_number']??'') ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label" style="font-size:0.85rem;">Account PIN / Password</label>
                <input type="password" name="pin_code" class="form-control" placeholder="••••" required autocomplete="off">
            </div>

            <button type="submit" name="confirm_payment" class="btn-confirm">
                Confirm Payment — ৳<?= number_format($total, 2) ?>
            </button>
        </form>
        
        <div style="text-align: center; margin-top: 18px;">
            <a href="cart.php" style="color: var(--secondary); font-size: 0.85rem; text-decoration: none; font-weight: 600;">Cancel Transaction</a>
        </div>
    </div>

    <div class="gateway-footer">
        🔒 SSL Secured 128-bit Payment Gateway
    </div>
</div>

<script>
    if (localStorage.getItem("theme") === "dark") {
        document.body.classList.add("dark-mode");
    }
</script>
</body>
</html>
