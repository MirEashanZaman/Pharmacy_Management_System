<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile - Pharmacy Management System</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="main-container">
    <div class="page-header">
        <div><h1>My Profile</h1><p>Manage your account information</p></div>
        <span class="badge badge-<?= $user['role']==='admin'?'danger':($user['role']==='salesperson'?'warning':'success') ?> badge-large">
            <?= ucfirst($user['role']) ?>
        </span>
    </div>

    <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="grid-2 align-start gap-24">
        
        <div class="card">
            <div class="card-header">Edit Profile</div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                    <div class="text-center mb-3">
                        <?php
                        $picSrc = 'uploads/profiles/'.($user['profile_pic']??'');
                        if ($user['profile_pic'] && $user['profile_pic']!=='default_user.png' && file_exists($picSrc)):
                        ?>
                        <img src="<?= $picSrc ?>" class="profile-pic-lg" alt="Profile" id="profilePreview">
                        <?php else: ?>
                        <div id="profilePreview" class="profile-avatar-fallback">
                            <?= strtoupper(substr($user['name'],0,1)) ?>
                        </div>
                        <?php endif; ?>
                        <div class="mt-1 display-flex justify-center gap-10 align-center">
                            <label for="picUpload" class="btn btn-sm btn-outline cursor-pointer m-0">Change Photo</label>
                            <input type="file" name="profile_pic" id="picUpload" accept="image/*" class="hidden" onchange="previewPic(event)">
                            <?php if ($user['profile_pic'] && $user['profile_pic'] !== 'default_user.png'): ?>
                                <button type="button" class="btn btn-sm btn-danger" onclick="removePhoto()" id="removePicBtn">Remove Photo</button>
                            <?php endif; ?>
                            <input type="hidden" name="remove_pic" id="removePicInput" value="0">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone *</label>
                            <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']??'') ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email (cannot change)</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Division</label>
                            <select name="division" class="form-control">
                                <option value="">Select Division</option>
                                <?php foreach($divisions as $d): ?>
                                    <option value="<?= $d ?>" <?= ($user['division']??'')===$d?'selected':'' ?>><?= $d ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">District</label>
                            <input type="text" name="district" class="form-control" value="<?= htmlspecialchars($user['district']??'') ?>" placeholder="e.g., Dhaka">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Upazila / Thana</label>
                        <input type="text" name="upazila" class="form-control" value="<?= htmlspecialchars($user['upazila']??'') ?>" placeholder="e.g., Mirpur">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Full Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="House no, road, area..."><?= htmlspecialchars($user['address']??'') ?></textarea>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary w-100">Save Changes</button>
                </form>
            </div>
        </div>

        <div>
            
            <div class="card mb-2">
                <div class="card-header">Change Password</div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                        <div class="form-group">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="old_pass" class="form-control" placeholder="Enter current password" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_pass" class="form-control" placeholder="Min 5 characters" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_pass" class="form-control" placeholder="Repeat new password" required>
                        </div>
                        <button type="submit" name="change_pass" class="btn btn-warning w-100">Update Password</button>
                    </form>
                </div>
            </div>

            
            <div class="card">
                <div class="card-header">Account Information</div>
                <div class="card-body">
                    <div class="display-flex flex-column gap-12">
                        <div class="profile-info-row">
                            <span class="text-muted">Role</span>
                            <span class="badge badge-<?= $user['role']==='admin'?'danger':($user['role']==='salesperson'?'warning':'success') ?>"><?= ucfirst($user['role']) ?></span>
                        </div>
                        <div class="profile-info-row">
                            <span class="text-muted">Member Since</span>
                            <strong><?= date('d M Y', strtotime($user['created_at'])) ?></strong>
                        </div>
                        <div class="profile-info-row">
                            <span class="text-muted">Status</span>
                            <span class="badge badge-success">Active</span>
                        </div>
                        <?php if($user['role']==='customer'): ?>
                        <?php $orderCount=$this->db->query("SELECT COUNT(*) as c FROM orders WHERE user_id={$user['id']}")->fetch_assoc()['c']; ?>
                        <div class="profile-info-row">
                            <span class="text-muted">Total Orders</span>
                            <strong><?= $orderCount ?></strong>
                        </div>
                        <?php endif; ?>
                    </div>
                    <a href="logout.php" class="btn btn-danger w-100 mt-2" onclick="return confirm('Are you sure you want to logout?')">
                        Logout from Account
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewPic(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (ev) => {
        const preview = document.getElementById('profilePreview');
        if (preview.tagName === 'IMG') {
            preview.src = ev.target.result;
        } else {
            const img = document.createElement('img');
            img.src = ev.target.result;
            img.className = 'profile-pic-lg';
            img.id = 'profilePreview';
            preview.replaceWith(img);
        }
        document.getElementById('removePicInput').value = '0';
        // Show remove button
        let removeBtn = document.getElementById('removePicBtn');
        if (!removeBtn) {
            removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-sm btn-danger';
            removeBtn.id = 'removePicBtn';
            removeBtn.onclick = removePhoto;
            removeBtn.textContent = 'Remove Photo';
            document.getElementById('picUpload').parentElement.appendChild(removeBtn);
        } else {
            removeBtn.style.display = '';
        }
    };
    reader.readAsDataURL(file);
}
function removePhoto() {
    const preview = document.getElementById('profilePreview');
    const input = document.getElementById('removePicInput');
    input.value = '1';
    
    const placeholder = document.createElement('div');
    placeholder.id = 'profilePreview';
    placeholder.className = 'profile-avatar-fallback';
    placeholder.textContent = '<?= strtoupper(substr($user['name'],0,1)) ?>';
    
    preview.replaceWith(placeholder);
    
    const removeBtn = document.getElementById('removePicBtn');
    if (removeBtn) removeBtn.style.display = 'none';
    document.getElementById('picUpload').value = '';
}
</script>
</body>
</html>
