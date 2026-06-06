<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Medicine - Pharmacy Management System</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../navbar.php'; ?>
<div class="main-container">
    <div class="page-header">
        <div><h1>Edit Medicine</h1><p>Update medicine details</p></div>
        <a href="my_medicines.php" class="btn btn-secondary">← Back</a>
    </div>

    <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="grid-2 align-start gap-24">
        <div class="card">
            <div class="card-header">Medicine Details</div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-label">Medicine Name *</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($med['name']) ?>" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Generic Name</label>
                            <input type="text" name="generic_name" class="form-control" value="<?= htmlspecialchars($med['generic_name']) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Brand *</label>
                            <input type="text" name="brand" class="form-control" value="<?= htmlspecialchars($med['brand']) ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-control">
                                <?php foreach($categories as $c): ?>
                                    <option value="<?= $c ?>" <?= $med['category']===$c?'selected':'' ?>><?= $c ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Unit</label>
                            <select name="unit" class="form-control">
                                <?php foreach($units as $u): ?>
                                    <option value="<?= $u ?>" <?= $med['unit']===$u?'selected':'' ?>><?= ucfirst($u) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Price (৳) *</label>
                            <input type="number" name="price" class="form-control" value="<?= $med['price'] ?>" step="0.01" min="0.01" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Stock *</label>
                            <input type="number" name="stock" class="form-control" value="<?= $med['stock'] ?>" min="0" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($med['description']) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="checkbox-label-card">
                            <input type="checkbox" name="requires_prescription" <?= $med['requires_prescription']?'checked':'' ?> class="checkbox-size-lg">
                            <span>Requires Prescription</span>
                        </label>
                    </div>
                    <button type="submit" class="btn btn-success w-100 btn-large">Save Changes</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Medicine Image</div>
            <div class="card-body">
                
                <div class="text-center mb-2">
                    <?php if($med['image']&&$med['image']!=='default_medicine.png'&&file_exists('../uploads/medicines/'.$med['image'])): ?>
                    <img src="../uploads/medicines/<?= htmlspecialchars($med['image']) ?>" class="medicine-preview-img-box" alt="Current Image">
                    <p class="text-muted mt-1 fs-md">Current image</p>
                    <?php else: ?>
                    <div class="med-placeholder-lg">MED</div>
                    <p class="text-muted mt-1 fs-md">No image uploaded</p>
                    <?php endif; ?>
                </div>

                <div class="upload-area" onclick="document.getElementById('imageInput').click()">
                    <div class="upload-icon"></div>
                    <div class="upload-text">
                        <strong>Click to upload new image</strong><br>
                        <span class="fs-8 text-muted-999">JPG, PNG up to 5MB</span>
                    </div>
                </div>
                <input type="file" id="imageInput" accept="image/*" class="display-none" onchange="previewImage(event)">
                <div id="newPreview" class="display-none text-center mt-12">
                    <img id="previewImg" class="image-new-preview-box">
                    <p class="text-muted fs-8 mt-6">New image preview</p>
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
