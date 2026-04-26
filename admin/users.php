<?php
require_once '../session.php';
requireLogin('../login.php');
$user = getUser();
if ($user['role']!=='admin') { header("Location: ../index.php"); exit; }

$success = '';
$error = '';

if (isset($_GET['delete'])) {
    $delId = intval($_GET['delete']);
    if ($delId === $user['id']) {
        $error = 'You cannot delete your own account.';
    } else {
        $target = $conn->query("SELECT role FROM users WHERE id=$delId")->fetch_assoc();
        if (!$target) {
            $error = 'User not found.';
        } elseif ($target['role'] === 'admin') {
            $error = 'You cannot deactivate another admin.';
        } else {
            $conn->query("UPDATE users SET is_active=0 WHERE id=$delId");
            $success = 'User deactivated successfully.';
        }
    }
}

if (isset($_GET['restore'])) {
    $resId = intval($_GET['restore']);
    $conn->query("UPDATE users SET is_active=1 WHERE id=$resId");
    $success = 'User restored successfully.';
}

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_salesperson'])) {
    $name = trim($_POST['sp_name']??'');
    $email = trim($_POST['sp_email']??'');
    $pass = $_POST['sp_pass']??'';
    $phone = trim($_POST['sp_phone']??'');
    if (empty($name)||empty($email)||empty($pass)) { $error='Please fill all fields.'; }
    else {
        $check = $conn->prepare("SELECT id FROM users WHERE email=?");
        $check->bind_param("s",$email); $check->execute();
        if ($check->get_result()->num_rows>0) { $error='Email already exists.'; }
        else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $role = 'salesperson';
            $stmt = $conn->prepare("INSERT INTO users (name,email,password,role,phone) VALUES (?,?,?,?,?)");
            $stmt->bind_param("sssss",$name,$email,$hash,$role,$phone);
            if ($stmt->execute()) $success='Salesperson added!';
            else $error='Failed to add salesperson.';
        }
    }
}

$filter = $_GET['filter'] ?? 'all';
$where = $filter==='all' ? "1" : ($filter==='inactive' ? "is_active=0" : "role='$filter' AND is_active=1");
$users = $conn->query("SELECT * FROM users WHERE $where ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Users - Admin - Pharmacy Management System</title>
<link rel="stylesheet" href="../style.css">
</head>
<body>
<?php include '../navbar.php'; ?>
<div class="main-container">
    <div class="page-header">
        <div><h1>👥 User Management</h1><p>Manage all users of Pharmacy Management System</p></div>
        <button class="btn btn-primary" onclick="document.getElementById('addSpModal').classList.add('active')">➕ Add Salesperson</button>
    </div>

    <?php if($success): ?><div class="alert alert-success">✅ <?= $success ?></div><?php endif; ?>
    <?php if($error): ?><div class="alert alert-danger">❌ <?= htmlspecialchars($error) ?></div><?php endif; ?>
    <div class="tabs mb-2">
        <a href="?filter=all" class="tab <?= $filter==='all'?'active':'' ?>">👥 All Users</a>
        <a href="?filter=customer" class="tab <?= $filter==='customer'?'active':'' ?>">🛒 Customers</a>
        <a href="?filter=salesperson" class="tab <?= $filter==='salesperson'?'active':'' ?>">🏪 Salespersons</a>
        <a href="?filter=admin" class="tab <?= $filter==='admin'?'active':'' ?>">👑 Admins</a>
        <a href="?filter=inactive" class="tab <?= $filter==='inactive'?'active':'' ?>">🚫 Deactivated</a>
    </div>

    <div class="card">
        <div class="table-container">
            <table>
                <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Phone</th><th>Location</th><th>Joined</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if($users->num_rows===0): ?>
                <tr><td colspan="9" class="text-center" style="padding:40px;color:#888;">No users found</td></tr>
                <?php else: ?>
                <?php while($u=$users->fetch_assoc()): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <?php
                            $picSrc = '../uploads/profiles/'.($u['profile_pic']??'');
                            if($u['profile_pic'] && $u['profile_pic']!=='default_user.png' && file_exists($picSrc)):
                            ?>
                            <img src="<?= $picSrc ?>" style="width:36px;height:36px;border-radius:50%;object-fit:cover;" alt="">
                            <?php else: ?>
                            <div style="width:36px;height:36px;border-radius:50%;background:var(--primary);color:white;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.9rem;">
                                <?= strtoupper(substr($u['name'],0,1)) ?>
                            </div>
                            <?php endif; ?>
                            <strong><?= htmlspecialchars($u['name']) ?></strong>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <span class="badge badge-<?= $u['role']==='admin'?'danger':($u['role']==='salesperson'?'warning':'success') ?>">
                            <?= ucfirst($u['role']) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($u['phone']??'-') ?></td>
                    <td><?= htmlspecialchars(($u['district']??'').' '.($u['division']??'')) ?></td>
                    <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <?php if($u['is_active']): ?>
                            <span class="badge badge-success">✅ Active</span>
                        <?php else: ?>
                            <span class="badge badge-danger">🚫 Deactivated</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($u['is_active']): ?>
                            <?php if($u['id']!==$user['id'] && $u['role'] !== 'admin'): ?>
                            <a href="?delete=<?= $u['id'] ?>&filter=<?= $filter ?>" class="btn btn-danger btn-sm" onclick="return confirm('Deactivate this user?')">🚫 Deactivate</a>
                            <?php elseif($u['id']=== $user['id']): ?>
                            <span class="text-muted" style="font-size:0.8rem;">You</span>
                            <?php else: ?>
                            <span class="text-muted" style="font-size:0.8rem;">No actions available</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="?restore=<?= $u['id'] ?>&filter=<?= $filter ?>" class="btn btn-success btn-sm">✅ Restore</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal-overlay" id="addSpModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">➕ Add Salesperson</span>
            <button class="modal-close" onclick="document.getElementById('addSpModal').classList.remove('active')">✕</button>
        </div>
        <?php if($error): ?><div class="alert alert-danger">❌ <?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label class="form-label">👤 Full Name *</label>
                <input type="text" name="sp_name" class="form-control" placeholder="Salesperson name" required>
            </div>
            <div class="form-group">
                <label class="form-label">📧 Email *</label>
                <input type="email" name="sp_email" class="form-control" placeholder="email@example.com" required>
            </div>
            <div class="form-group">
                <label class="form-label">📱 Phone</label>
                <input type="tel" name="sp_phone" class="form-control" placeholder="01XXXXXXXXX">
            </div>
            <div class="form-group">
                <label class="form-label">🔒 Password *</label>
                <input type="password" name="sp_pass" class="form-control" placeholder="Set a password" required>
            </div>
            <button type="submit" name="add_salesperson" class="btn btn-primary w-100">✅ Add Salesperson</button>
        </form>
    </div>
</div>

<script>
<?php if($error && isset($_POST['add_salesperson'])): ?>
document.getElementById('addSpModal').classList.add('active');
<?php endif; ?>
</script>
</body>
</html>





