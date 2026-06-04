<?php
require_once 'session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Setup Guide & Credentials</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .section-title {
            text-align: center;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 24px;
            margin-top: 40px;
            position: relative;
            color: var(--primary-dark);
        }
        .section-title::after {
            content: '';
            display: block;
            width: 50px;
            height: 4px;
            background: var(--primary);
            margin: 10px auto 0 auto;
            border-radius: 2px;
        }
        .credentials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 24px;
            margin-bottom: 48px;
        }
        .credential-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 28px;
            box-shadow: var(--card-shadow);
            border-top: 5px solid var(--primary);
            transition: var(--transition);
        }
        .credential-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(60, 48, 112, 0.12);
        }
        .credential-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .role-badge {
            font-size: 0.75rem;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-admin { background: #fce7f3; color: var(--danger); }
        .badge-customer { background: #d1fae5; color: var(--success); }
        .badge-seller { background: #fef3c7; color: var(--warning); }
        
        .copy-row {
            background: var(--light);
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid rgba(60, 48, 112, 0.08);
        }
        .copy-row code {
            font-family: 'Courier New', Courier, monospace;
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--dark);
        }
        .btn-copy {
            background: white;
            border: 1px solid var(--secondary);
            color: var(--primary);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }
        .btn-copy:hover {
            background: var(--primary);
            color: white;
        }

        .guide-box {
            background: white;
            border-radius: var(--border-radius);
            padding: 36px;
            box-shadow: var(--card-shadow);
            margin-bottom: 48px;
        }
        .guide-step {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 16px 0;
            border-bottom: 1px solid rgba(60, 48, 112, 0.08);
        }
        .guide-step:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .guide-step-num {
            width: 36px;
            height: 36px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            flex-shrink: 0;
        }
        .guide-step-text {
            font-size: 1rem;
            color: var(--dark);
            font-weight: 500;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="main-container" style="max-width: 1200px; margin: 0 auto; padding: 40px 20px;">
    
    <h1 style="text-align: center; margin-bottom: 10px; color: var(--primary-dark);">Developer Guide & Access Points</h1>
    <p style="text-align: center; color: var(--secondary); margin-bottom: 40px;">This page is for development reference. Please use the accounts listed below to log in and inspect individual role dashboards.</p>

    <!-- Credentials Section -->
    <h2 class="section-title">Preconfigured Roles & Accounts</h2>
    <div class="credentials-grid">
        <!-- Admin -->
        <div class="credential-card" style="border-top-color: var(--danger);">
            <div class="credential-header">
                <div>
                    <h3 style="font-weight: 700; font-size: 1.2rem; color: var(--dark);">Admin</h3>
                    <p style="font-size: 0.85rem; color:#777;">Full system controls & approvals</p>
                </div>
                <span class="role-badge badge-admin">Admin</span>
            </div>
            
            <div class="copy-row">
                <div>
                    <div style="font-size:0.7rem; color:#888; text-transform:uppercase;">Email</div>
                    <code id="admin-email">admin@pharma.bd</code>
                </div>
                <button class="btn-copy" id="btn-admin-email" onclick="copyToClipboard('admin@pharma.bd', 'btn-admin-email')">Copy</button>
            </div>
            <div class="copy-row">
                <div>
                    <div style="font-size:0.7rem; color:#888; text-transform:uppercase;">Password</div>
                    <code id="admin-pass">12345!</code>
                </div>
                <button class="btn-copy" id="btn-admin-pass" onclick="copyToClipboard('12345!', 'btn-admin-pass')">Copy</button>
            </div>
        </div>

        <!-- Customer -->
        <div class="credential-card" style="border-top-color: var(--success);">
            <div class="credential-header">
                <div>
                    <h3 style="font-weight: 700; font-size: 1.2rem; color: var(--dark);">Customer</h3>
                    <p style="font-size: 0.85rem; color:#777;">Search medicines, upload Rx & buy</p>
                </div>
                <span class="role-badge badge-customer">Customer</span>
            </div>
            
            <div class="copy-row">
                <div>
                    <div style="font-size:0.7rem; color:#888; text-transform:uppercase;">Email</div>
                    <code id="cust-email">milton@pharma.bd</code>
                </div>
                <button class="btn-copy" id="btn-cust-email" onclick="copyToClipboard('milton@pharma.bd', 'btn-cust-email')">Copy</button>
            </div>
            <div class="copy-row">
                <div>
                    <div style="font-size:0.7rem; color:#888; text-transform:uppercase;">Password</div>
                    <code id="cust-pass">12345</code>
                </div>
                <button class="btn-copy" id="btn-cust-pass" onclick="copyToClipboard('12345', 'btn-cust-pass')">Copy</button>
            </div>
        </div>

        <!-- Salesperson -->
        <div class="credential-card" style="border-top-color: var(--warning);">
            <div class="credential-header">
                <div>
                    <h3 style="font-weight: 700; font-size: 1.2rem; color: var(--dark);">Seller</h3>
                    <p style="font-size: 0.85rem; color:#777;">Manage stock & fulfill orders</p>
                </div>
                <span class="role-badge badge-seller">Seller</span>
            </div>
            
            <div class="copy-row">
                <div>
                    <div style="font-size:0.7rem; color:#888; text-transform:uppercase;">Email</div>
                    <code id="sell-email">tanjim@pharma.bd</code>
                </div>
                <button class="btn-copy" id="btn-sell-email" onclick="copyToClipboard('tanjim@pharma.bd', 'btn-sell-email')">Copy</button>
            </div>
            <div class="copy-row">
                <div>
                    <div style="font-size:0.7rem; color:#888; text-transform:uppercase;">Password</div>
                    <code id="sell-pass">12345</code>
                </div>
                <button class="btn-copy" id="btn-sell-pass" onclick="copyToClipboard('12345', 'btn-sell-pass')">Copy</button>
            </div>
        </div>
    </div>

    <!-- Setup Guide -->
    <h2 class="section-title">Quick Installation Steps</h2>
    <div class="guide-box">
        <div class="guide-step">
            <div class="guide-step-num">1</div>
            <div class="guide-step-text">Install and start your local XAMPP Control Panel. Start <strong>Apache</strong> and <strong>MySQL</strong>.</div>
        </div>
        <div class="guide-step">
            <div class="guide-step-num">2</div>
            <div class="guide-step-text">Ensure the system files reside under your XAMPP htdocs root folder: <code>C:\xampp\htdocs\pharmacy\</code>.</div>
        </div>
        <div class="guide-step">
            <div class="guide-step-num">3</div>
            <div class="guide-step-text">Visit <code>http://localhost/pharmacy/index.php</code>. The database and sample tables will auto-initialize!</div>
        </div>
        <div class="guide-step">
            <div class="guide-step-num">4</div>
            <div class="guide-step-text">Login using any of the copyable credentials above to test the rich user roles!</div>
        </div>
    </div>

</div>

<?php include 'footer.php'; ?>

<script>
function copyToClipboard(text, btnId) {
    navigator.clipboard.writeText(text).then(function() {
        const btn = document.getElementById(btnId);
        const originalText = btn.innerText;
        btn.innerText = 'Copied!';
        btn.style.background = 'var(--success)';
        btn.style.color = 'white';
        btn.style.borderColor = 'var(--success)';
        
        showToast("Copied to clipboard: " + text, "success", 2000);
        
        setTimeout(function() {
            btn.innerText = originalText;
            btn.style.background = '';
            btn.style.color = '';
            btn.style.borderColor = '';
        }, 1500);
    });
}
</script>

</body>
</html>
