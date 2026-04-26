<?php
require_once '../session.php';
requireLogin('../login.php');
$user = getUser();
if ($user['role']!=='salesperson') { header("Location: ../index.php"); exit; }

$id = intval($_GET['id'] ?? 0);
if (!$id) { header("Location: my_medicines.php"); exit; }

$stmt = $conn->prepare("SELECT * FROM medicines WHERE id=? AND salesperson_id=?");
$stmt->bind_param("ii", $id, $user['id']);
$stmt->execute();
$med = $stmt->get_result()->fetch_assoc();
if (!$med) { header("Location: my_medicines.php"); exit; }

$categories = ['Antibiotic','Painkiller','Gastric','Cardiac','Diabetes','Antihistamine','Vitamin','Supplement','Antifungal','Antiviral','Neurological','Respiratory','Dermatology','Ophthalmic','Other'];
$units = ['tablet','capsule','syrup','injection','cream','ointment','drops','inhaler','sachet','suppository','pcs'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name = trim($_POST['name']??'');
    $generic = trim($_POST['generic_name']??'');
    $brand = trim($_POST['brand']??'');
    $cat = $_POST['category']??'';
    $desc = trim($_POST['description']??'');
    $price = floatval($_POST['price']??0);
    $stock = intval($_POST['stock']??0);
    $unit = $_POST['unit']??'tablet';
    $rx = isset($_POST['requires_prescription'])?1:0;

    if (empty($name)||empty($brand)||$price<=0||$stock<0) {
        $error = 'Please fill all required fields with valid values.';
    } else {
        $img = $med['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error']===0) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext,['jpg','jpeg','png','gif','webp'])) { $error='Invalid image format.'; }
            elseif ($_FILES['image']['size']>5242880) { $error='Image must be under 5MB.'; }
            else {
                if (!is_dir('../uploads/medicines')) mkdir('../uploads/medicines', 0755, true);
                $img = uniqid('med_').'.'.$ext;
                if (!move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/medicines/'.$img)) {
                    $error='Failed to upload image.'; $img=$med['image'];
                }
            }
        }
        if (!$error) {
            $stmt2 = $conn->prepare("UPDATE medicines SET name=?,generic_name=?,brand=?,category=?,description=?,price=?,stock=?,unit=?,image=?,requires_prescription=? WHERE id=? AND salesperson_id=?");
            $stmt2->bind_param("ssssssdssiii",$name,$generic,$brand,$cat,$desc,$price,$stock,$unit,$img,$rx,$id,$user['id']);
            if ($stmt2->execute()) {
                $success='Medicine updated successfully!';
                $med = array_merge($med, ['name'=>$name,'generic_name'=>$generic,'brand'=>$brand,'category'=>$cat,'description'=>$desc,'price'=>$price,'stock'=>$stock,'unit'=>$unit,'image'=>$img,'requires_prescription'=>$rx]);
            } else { $error='Update failed.'; }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Medicine - Pharmacy Management System</title>
<link rel="stylesheet" href="../style.css">
</head>
<body>
<?php include '../navbar.php'; ?>
<div class="main-container">
    <div class="page-header">
        <div><h1>✏️ Edit Medicine</h1><p>Update medicine details</p></div>
        <a href="my_medicines.php" class="btn btn-secondary">← Back</a>
    </div>

    <?php if($success): ?><div class="alert alert-success">✅ <?= $success ?></div><?php endif; ?>
    <?php if($error): ?><div class="alert alert-danger">❌ <?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="grid-2" style="align-items:start; gap:24px;">
        <div class="card">
            <div class="card-header">📋 Medicine Details</div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-label">💊 Medicine Name *</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($med['name']) ?>" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">🔬 Generic Name</label>
                            <input type="text" name="generic_name" class="form-control" value="<?= htmlspecialchars($med['generic_name']) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">🏭 Brand *</label>
                            <input type="text" name="brand" class="form-control" value="<?= htmlspecialchars($med['brand']) ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">🏷️ Category</label>
                            <select name="category" class="form-control">
                                <?php foreach($categories as $c): ?>
                                    <option value="<?= $c ?>" <?= $med['category']===$c?'selected':'' ?>><?= $c ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">📏 Unit</label>
                            <select name="unit" class="form-control">
                                <?php foreach($units as $u): ?>
                                    <option value="<?= $u ?>" <?= $med['unit']===$u?'selected':'' ?>><?= ucfirst($u) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">💰 Price (৳) *</label>
                            <input type="number" name="price" class="form-control" value="<?= $med['price'] ?>" step="0.01" min="0.01" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">📦 Stock *</label>
                            <input type="number" name="stock" class="form-control" value="<?= $med['stock'] ?>" min="0" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">📝 Description</label>
                        <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($med['description']) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:12px;background:#f8f9fa;border-radius:8px;">
                            <input type="checkbox" name="requires_prescription" <?= $med['requires_prescription']?'checked':'' ?> style="width:18px;height:18px;">
                            <span>🩺 Requires Prescription</span>
                        </label>
                    </div>
                    <button type="submit" class="btn btn-success w-100" style="padding:14px;">💾 Save Changes</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">📷 Medicine Image</div>
            <div class="card-body">
                
                <div class="text-center mb-2">
                    <?php if($med['image']&&$med['image']!=='default_medicine.png'&&file_exists('../uploads/medicines/'.$med['image'])): ?>
                    <img src="../uploads/medicines/<?= htmlspecialchars($med['image']) ?>" style="max-width:100%;max-height:200px;border-radius:12px;border:2px solid var(--primary);" alt="Current Image">
                    <p class="text-muted mt-1" style="font-size:0.85rem;">Current image</p>
                    <?php else: ?>
                    <div style="height:150px;background:#f0f4ff;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:4rem;">💊</div>
                    <p class="text-muted mt-1" style="font-size:0.85rem;">No image uploaded</p>
                    <?php endif; ?>
                </div>

                <div class="upload-area" onclick="document.getElementById('imageInput').click()">
                    <div class="upload-icon">📷</div>
                    <div class="upload-text">
                        <strong>Click to upload new image</strong><br>
                        <span style="font-size:0.8rem;color:#999;">JPG, PNG up to 5MB</span>
                    </div>
                </div>
                <input type="file" id="imageInput" accept="image/*" style="display:none;" onchange="previewImage(event)">
                <div id="newPreview" style="display:none; text-align:center; margin-top:12px;">
                    <img id="previewImg" style="max-width:100%;max-height:150px;border-radius:10px;border:2px dashed var(--success);">
                    <p class="text-muted" style="font-size:0.8rem; margin-top:6px;">New image preview</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const fileInput = document.getElementById('imageInput');
const form = document.querySelector('form');
fileInput.name = 'image';
form.appendChild(fileInput);
function previewImage(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = ev => {
        document.getElementById('previewImg').src = ev.target.result;
        document.getElementById('newPreview').style.display = 'block';
    };
    reader.readAsDataURL(file);
}
</script>
</body>
</html>





