<?php
require_once '../session.php';
requireLogin('../login.php');
$user = getUser();
if ($user['role']!=='salesperson') { header("Location: ../index.php"); exit; }

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
        
        $img = 'default_medicine.png';
        if (isset($_FILES['image']) && $_FILES['image']['error']===0) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext,['jpg','jpeg','png','gif','webp'])) { $error='Invalid image format. Use JPG, PNG, or GIF.'; }
            elseif ($_FILES['image']['size']>5242880) { $error='Image must be under 5MB.'; }
            else {
                if (!is_dir('../uploads/medicines')) mkdir('../uploads/medicines', 0755, true);
                $img = uniqid('med_').'.'.$ext;
                if (!move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/medicines/'.$img)) {
                    $error = 'Failed to upload image. Check directory permissions.';
                    $img = 'default_medicine.png';
                }
            }
        }

        if (!$error) {
            $stmt = $conn->prepare("INSERT INTO medicines (name,generic_name,brand,category,description,price,stock,unit,image,requires_prescription,salesperson_id) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("ssssssdssii",$name,$generic,$brand,$cat,$desc,$price,$stock,$unit,$img,$rx,$user['id']);
            if ($stmt->execute()) {
                $success = "Medicine '$name' added successfully!";
                header("Refresh:2; url=my_medicines.php");
            } else {
                $error = 'Failed to add medicine: '.$conn->error;
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
<title>Add Medicine - Pharmacy Management System</title>
<link rel="stylesheet" href="../style.css">
</head>
<body>
<?php include '../navbar.php'; ?>
<div class="main-container">
    <div class="page-header">
        <div><h1>➕ Add New Medicine</h1><p>List a medicine for sale</p></div>
        <a href="my_medicines.php" class="btn btn-secondary">← My Medicines</a>
    </div>

    <?php if($success): ?><div class="alert alert-success">✅ <?= $success ?> Redirecting...</div><?php endif; ?>
    <?php if($error): ?><div class="alert alert-danger">❌ <?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="grid-2" style="align-items:start; gap:24px;">
        <div class="card">
            <div class="card-header">📋 Medicine Details</div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-label">💊 Medicine Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g., Napa 500mg" required value="<?= htmlspecialchars($_POST['name']??'') ?>">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">🔬 Generic Name</label>
                            <input type="text" name="generic_name" class="form-control" placeholder="e.g., Paracetamol" value="<?= htmlspecialchars($_POST['generic_name']??'') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">🏭 Brand *</label>
                            <input type="text" name="brand" class="form-control" placeholder="e.g., Beximco Pharma" required value="<?= htmlspecialchars($_POST['brand']??'') ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">🏷️ Category *</label>
                            <select name="category" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php foreach($categories as $c): ?>
                                    <option value="<?= $c ?>" <?= ($_POST['category']??'')===$c?'selected':'' ?>><?= $c ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">📏 Unit</label>
                            <select name="unit" class="form-control">
                                <?php foreach($units as $u): ?>
                                    <option value="<?= $u ?>" <?= ($_POST['unit']??'tablet')===$u?'selected':'' ?>><?= ucfirst($u) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">💰 Price (৳) *</label>
                            <input type="number" name="price" class="form-control" placeholder="0.00" step="0.01" min="0.01" required value="<?= htmlspecialchars($_POST['price']??'') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">📦 Stock Quantity *</label>
                            <input type="number" name="stock" class="form-control" placeholder="0" min="0" required value="<?= htmlspecialchars($_POST['stock']??'') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">📝 Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Describe the medicine, its uses, dosage info..."><?= htmlspecialchars($_POST['description']??'') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100" style="padding:14px; font-size:1rem;">✅ Add Medicine</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">📷 Medicine Image</div>
            <div class="card-body">
                <div class="upload-area" id="uploadArea" onclick="document.getElementById('imageInput').click()">
                    <div class="upload-icon" id="uploadIcon">📷</div>
                    <div class="upload-text">
                        <strong>Click to upload medicine image</strong><br>
                        <span style="font-size:0.8rem; color:#999;">JPG, PNG, GIF up to 5MB</span>
                    </div>
                </div>
                <input type="file" name="image" id="imageInput" accept="image/*" style="display:none;" form="none" onchange="previewImage(event)">
                <div id="imagePreview" style="display:none; margin-top:16px; text-align:center;">
                    <img id="previewImg" style="max-width:100%; max-height:200px; border-radius:12px; border:2px solid var(--primary);" alt="">
                    <div class="mt-1">
                        <button onclick="clearImage()" class="btn btn-danger btn-sm">🗑️ Remove</button>
                    </div>
                </div>

                <div style="margin-top:24px; background:#f0f4ff; border-radius:12px; padding:20px;">
                    <h4 style="font-size:0.9rem; margin-bottom:12px; color:var(--primary);">💡 Image Tips</h4>
                    <ul style="font-size:0.85rem; color:#555; line-height:2; padding-left:20px;">
                        <li>Use a clear, high-quality photo</li>
                        <li>White background preferred</li>
                        <li>Show the medicine packaging</li>
                        <li>Square format works best (1:1 ratio)</li>
                        <li>Max file size: 5MB</li>
                    </ul>
                </div>

                <div style="margin-top:16px; background:#fff3cd; border-radius:12px; padding:16px;">
                    <h4 style="font-size:0.9rem; margin-bottom:8px; color:#856404;">⚠️ Important</h4>
                    <p style="font-size:0.85rem; color:#856404; line-height:1.6;">Ensure medicine information is accurate. Incorrect information may lead to account suspension.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const uploadArea = document.getElementById('uploadArea');
const imageInput = document.getElementById('imageInput');
const formEl = document.querySelector('form');
imageInput.name = 'image';
formEl.appendChild(imageInput);

function previewImage(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = ev => {
        document.getElementById('previewImg').src = ev.target.result;
        document.getElementById('imagePreview').style.display = 'block';
        document.getElementById('uploadIcon').textContent = '✅';
    };
    reader.readAsDataURL(file);
}
function clearImage() {
    imageInput.value = '';
    document.getElementById('imagePreview').style.display = 'none';
    document.getElementById('uploadIcon').textContent = '📷';
}
['dragenter','dragover'].forEach(e => uploadArea.addEventListener(e, ev => { ev.preventDefault(); uploadArea.classList.add('drag-over'); }));
['dragleave','drop'].forEach(e => uploadArea.addEventListener(e, ev => { ev.preventDefault(); uploadArea.classList.remove('drag-over'); }));
uploadArea.addEventListener('drop', ev => {
    const files = ev.dataTransfer.files;
    if (files.length) { imageInput.files = files; previewImage({target:imageInput}); }
});
</script>
</body>
</html>





