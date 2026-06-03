<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pharmacy_bd');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$conn->query("CREATE DATABASE IF NOT EXISTS pharmacy_bd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db(DB_NAME);


$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','salesperson','customer') NOT NULL DEFAULT 'customer',
    phone VARCHAR(20),
    address TEXT,
    division VARCHAR(50),
    district VARCHAR(50),
    upazila VARCHAR(50),
    profile_pic VARCHAR(255) DEFAULT 'default_user.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1
)");


$conn->query("CREATE TABLE IF NOT EXISTS medicines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    generic_name VARCHAR(200),
    brand VARCHAR(100),
    category VARCHAR(100),
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    unit VARCHAR(50) DEFAULT 'pcs',
    image VARCHAR(255) DEFAULT 'default_medicine.png',
    requires_prescription TINYINT(1) DEFAULT 0,
    salesperson_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (salesperson_id) REFERENCES users(id)
)");


$conn->query("CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    medicine_id INT NOT NULL,
    quantity INT DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (medicine_id) REFERENCES medicines(id)
)");


$conn->query("CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    delivery_address TEXT NOT NULL,
    delivery_division VARCHAR(50),
    delivery_district VARCHAR(50),
    delivery_upazila VARCHAR(50),
    delivery_phone VARCHAR(20),
    status ENUM('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
    payment_method ENUM('cod','bkash','nagad','rocket') DEFAULT 'cod',
    prescription_image VARCHAR(255) DEFAULT NULL,
    rx_approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)");

// Safely Alter Table if columns are missing
$conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS prescription_image VARCHAR(255) DEFAULT NULL AFTER payment_method");
$conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS rx_approved TINYINT(1) DEFAULT 0 AFTER prescription_image");
$conn->query("ALTER TABLE medicines ADD COLUMN IF NOT EXISTS expiry_date DATE DEFAULT NULL AFTER requires_prescription");

// Populate mock expiry dates for testing if any null
$conn->query("UPDATE medicines SET expiry_date = DATE_ADD(CURDATE(), INTERVAL 15 DAY) WHERE (expiry_date IS NULL OR expiry_date = '0000-00-00') AND category = 'Painkiller'");
$conn->query("UPDATE medicines SET expiry_date = DATE_SUB(CURDATE(), INTERVAL 5 DAY) WHERE (expiry_date IS NULL OR expiry_date = '0000-00-00') AND category = 'Gastric'");
$conn->query("UPDATE medicines SET expiry_date = DATE_ADD(CURDATE(), INTERVAL 150 DAY) WHERE expiry_date IS NULL OR expiry_date = '0000-00-00'");


$conn->query("CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    medicine_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (medicine_id) REFERENCES medicines(id)
)");


$conn->query("CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    medicine_id INT NOT NULL,
    order_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (medicine_id) REFERENCES medicines(id),
    FOREIGN KEY (order_id) REFERENCES orders(id)
)");


$conn->query("CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100),
    email VARCHAR(150),
    subject VARCHAR(200),
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");


$adminCheck = $conn->query("SELECT id FROM users WHERE email='admin@pharma.bd'");
if ($adminCheck->num_rows === 0) {
    $adminPass = password_hash('12345!', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (name, email, password, role, phone, division, district, upazila) VALUES 
        ('Eshan', 'admin@pharma.bd', '$adminPass', 'admin', '01700000001', 'Dhaka', 'Dhaka', 'Mirpur')");
}


$spCheck = $conn->query("SELECT id FROM users WHERE email='tanjim@pharma.bd'");
if ($spCheck->num_rows === 0) {
    $spPass = password_hash('12345', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (name, email, password, role, phone, division, district, upazila) VALUES 
        ('Tanjim', 'tanjim@pharma.bd', '$spPass', 'salesperson', '01700000002', 'Dhaka', 'Gazipur', 'Tongi')");
}


$custCheck = $conn->query("SELECT id FROM users WHERE email='milton@pharma.bd'");
if ($custCheck->num_rows === 0) {
    $custPass = password_hash('12345', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (name, email, password, role, phone, division, district, upazila) VALUES 
        ('Milton', 'milton@pharma.bd', '$custPass', 'customer', '01700000003', 'Dhaka', 'Narayanganj', 'Siddhirganj')");
}


$medCheck = $conn->query("SELECT id FROM medicines LIMIT 1");
if ($medCheck->num_rows === 0) {
    $sp = $conn->query("SELECT id FROM users WHERE role='salesperson' LIMIT 1")->fetch_assoc();
    $spId = $sp['id'];
    $meds = [
        ['Napa 500mg', 'Paracetamol', 'Beximco Pharma', 'Painkiller', 'Used for fever and mild pain relief.', 2.00, 500, 'tablet'],
        ['Seclo 20mg', 'Omeprazole', 'Square Pharma', 'Gastric', 'Used to treat gastric ulcers and acid reflux.', 5.00, 300, 'capsule'],
        ['Azithro 500mg', 'Azithromycin', 'Incepta Pharma', 'Antibiotic', 'Broad-spectrum antibiotic.', 35.00, 200, 'tablet'],
        ['Amlodipine 5mg', 'Amlodipine', 'Opsonin Pharma', 'Cardiac', 'Used for high blood pressure treatment.', 8.00, 250, 'tablet'],
        ['Metformin 500mg', 'Metformin HCl', 'Renata Pharma', 'Diabetes', 'Used to control blood sugar levels.', 4.00, 400, 'tablet'],
        ['Cetirizine 10mg', 'Cetirizine', 'ACI Limited', 'Antihistamine', 'Used for allergy relief.', 3.00, 350, 'tablet'],
        ['Vitamin C 500mg', 'Ascorbic Acid', 'Drug International', 'Vitamin', 'Boosts immune system.', 10.00, 500, 'tablet'],
        ['Calcium Plus D3', 'Calcium Carbonate', 'Healthcare Pharma', 'Supplement', 'Bone health supplement.', 15.00, 200, 'tablet'],
        ['Atorvastatin 10mg', 'Atorvastatin', 'Square Pharma', 'Cardiac', 'Reduces bad cholesterol.', 12.00, 180, 'tablet'],
        ['Cefuroxime 250mg', 'Cefuroxime Axetil', 'Beximco Pharma', 'Antibiotic', 'Second-generation cephalosporin antibiotic.', 45.00, 100, 'tablet'],
    ];
    foreach ($meds as $m) {
        $stmt = $conn->prepare("INSERT INTO medicines (name, generic_name, brand, category, description, price, stock, unit, salesperson_id) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("sssssdisi", $m[0],$m[1],$m[2],$m[3],$m[4],$m[5],$m[6],$m[7],$spId);
        $stmt->execute();
    }
}
?>
