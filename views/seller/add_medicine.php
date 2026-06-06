<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Medicine - Pharmacy Management System</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../navbar.php'; ?>
<div class="main-container">
    <div class="page-header">
        <div><h1>Add New Medicine</h1><p>List a medicine for sale</p></div>
        <a href="my_medicines.php" class="btn btn-secondary">← My Medicines</a>
    </div>

    <?php if($success): ?><div class="alert alert-success"><?= $success ?> Redirecting...</div><?php endif; ?>
    <?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="grid-2 align-start gap-24">
        <div class="card">
            <div class="card-header">Medicine Details</div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-label">Medicine Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g., Napa 500mg" required value="<?= htmlspecialchars($_POST['name']??'') ?>">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Generic Name</label>
                            <input type="text" name="generic_name" class="form-control" placeholder="e.g., Paracetamol" value="<?= htmlspecialchars($_POST['generic_name']??'') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Brand *</label>
                            <input type="text" name="brand" class="form-control" placeholder="e.g., Beximco Pharma" required value="<?= htmlspecialchars($_POST['brand']??'') ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Category *</label>
                            <select name="category" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php foreach($categories as $c): ?>
                                    <option value="<?= $c ?>" <?= ($_POST['category']??'')===$c?'selected':'' ?>><?= $c ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Unit</label>
                            <select name="unit" class="form-control">
                                <?php foreach($units as $u): ?>
                                    <option value="<?= $u ?>" <?= ($_POST['unit']??'tablet')===$u?'selected':'' ?>><?= ucfirst($u) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Price (৳) *</label>
                            <input type="number" name="price" class="form-control" placeholder="0.00" step="0.01" min="0.01" required value="<?= htmlspecialchars($_POST['price']??'') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Stock Quantity *</label>
                            <input type="number" name="stock" class="form-control" placeholder="0" min="0" required value="<?= htmlspecialchars($_POST['stock']??'') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Describe the medicine, its uses, dosage info..."><?= htmlspecialchars($_POST['description']??'') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100 btn-large">Add Medicine</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Medicine Image</div>
            <div class="card-body">
                <div class="upload-area" id="uploadArea" onclick="document.getElementById('imageInput').click()">
                    <div class="upload-icon" id="uploadIcon"></div>
                    <div class="upload-text">
                        <strong>Click to upload medicine image</strong><br>
                        <span class="fs-8 text-muted-999">JPG, PNG, GIF up to 5MB</span>
                    </div>
                </div>
                <input type="file" name="image" id="imageInput" accept="image/*" class="display-none" form="none" onchange="previewImage(event)">
                <div id="imagePreview" class="display-none mt-12 text-center">
                    <img id="previewImg" class="medicine-preview-img-box" alt="">
                    <div class="mt-1">
                        <button onclick="clearImage()" class="btn btn-danger btn-sm">Remove</button>
                    </div>
                </div>

                <div class="med-tips-box">
                    <h4 class="med-tips-title">Image Tips</h4>
                    <ul class="med-tips-list">
                        <li>Use a clear, high-quality photo</li>
                        <li>White background preferred</li>
                        <li>Show the medicine packaging</li>
                        <li>Square format works best (1:1 ratio)</li>
                        <li>Max file size: 5MB</li>
                    </ul>
                </div>

                <div class="med-important-box">
                    <h4 class="med-important-title">Important</h4>
                    <p class="med-important-text">Ensure medicine information is accurate. Incorrect information may lead to account suspension.</p>
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
        document.getElementById('uploadIcon').textContent = 'Uploaded';
    };
    reader.readAsDataURL(file);
}
function clearImage() {
    imageInput.value = '';
    document.getElementById('imagePreview').style.display = 'none';
    document.getElementById('uploadIcon').textContent = '';
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
