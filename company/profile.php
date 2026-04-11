<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

$previewMode = true;

function safeRedirect($path) {
    header("Location: " . $path);
    exit;
}

if ($previewMode) {
    $company = [
        'company_name'   => 'Uniworks Vietnam',
        'tax_code'       => '0312345678',
        'industry_type'  => 'Technology & Software',
        'website'        => 'https://uniworks.vn',
        'address'        => '123 Nguyen Hue, District 1, Ho Chi Minh City',
        'email'          => 'hr@uniworks.vn',
        'phone'          => '0901234567'
    ];
    $success = null;
    $error = null;
} else {
    if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'company') {
        safeRedirect('../public/login.php');
    }

    $user = $_SESSION['user'];

    $stmt = $pdo->prepare("
        SELECT c.*, u.email, u.full_name, u.phone
        FROM companies c
        JOIN users u ON c.user_id = u.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user['id']]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$company) {
        if (function_exists('setFlash')) {
            setFlash('error', 'Company profile not found.');
        }
        safeRedirect('../public/login.php');
    }

    $success = function_exists('getFlash') ? getFlash('success') : null;
    $error   = function_exists('getFlash') ? getFlash('error') : null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Company Profile</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="company-shell">
    <aside class="company-sidebar">
        <div>
            <div class="company-brand">
                <h2>Uniworks</h2>
                <p>Recruiter Portal</p>
            </div>

            <nav class="company-nav">
                <a href="dashboard.php">Dashboard</a>
                <a href="applications.php">Applicants</a>
                <a href="manage_jobs.php">Jobs</a>
                <a href="messages.php">Messages</a>
                <a class="active" href="profile.php">Profile</a>
            </nav>
        </div>

        <div class="company-signout">
            <a href="../public/logout.php">Sign Out</a>
        </div>
    </aside>

    <main class="company-main">
        <div class="topbar">
            <div></div>
            <div class="topbar-actions">
                <a class="btn btn-primary" href="dashboard.php">Back to Dashboard</a>
            </div>
        </div>

        <?php if (!empty($success)): ?>
            <div class="flash success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="flash error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card" style="background: linear-gradient(135deg, #ece5ff 0%, #faf6e8 100%);">
            <div class="card-header" style="margin-bottom:0;">
                <div>
                    <h1 class="page-title" style="margin-bottom:10px;">Company Profile</h1>
                    <p class="page-subtitle" style="margin-bottom:0; max-width:760px;">
                        Keep your company profile polished and up to date so students can quickly understand your brand, industry, and contact details before applying.
                    </p>
                </div>
                <div style="text-align:right;">
                    <div class="badge approved" style="margin-bottom:10px;">Profile Active</div>
                    <div class="small-muted">Last updated just now</div>
                </div>
            </div>
        </div>

        <div class="detail-grid" style="align-items:start;">
            <div>
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h3>Edit Company Information</h3>
                            <p>Update the information students will see on your company profile.</p>
                        </div>
                    </div>

                    <form action="../actions/company/update_profile_action.php" method="POST">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Company Name</label>
                                <input type="text" name="company_name" value="<?php echo htmlspecialchars($company['company_name'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Tax Code</label>
                                <input type="text" name="tax_code" value="<?php echo htmlspecialchars($company['tax_code'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Industry Type</label>
                                <input type="text" name="industry_type" value="<?php echo htmlspecialchars($company['industry_type'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label>Website</label>
                                <input type="text" name="website" value="<?php echo htmlspecialchars($company['website'] ?? ''); ?>">
                            </div>

                            <div class="form-group full">
                                <label>Address</label>
                                <input type="text" name="address" value="<?php echo htmlspecialchars($company['address'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label>Account Email</label>
                                <input type="text" value="<?php echo htmlspecialchars($company['email'] ?? ''); ?>" disabled>
                            </div>

                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" value="<?php echo htmlspecialchars($company['phone'] ?? ''); ?>" disabled>
                            </div>
                        </div>

                        <?php if ($previewMode): ?>
                            <button class="btn btn-primary" type="button">Update Profile</button>
                        <?php else: ?>
                            <button class="btn btn-primary" type="submit">Update Profile</button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <div>
                <div class="stat-card yellow" style="margin-bottom:22px;">
                    <span class="stat-pill">Brand</span>
                    <h4>Company Snapshot</h4>
                    <div class="stat-value" style="font-size:24px; margin-bottom:10px;">
                        <?php echo htmlspecialchars($company['company_name'] ?? 'N/A'); ?>
                    </div>
                    <div class="small-muted" style="color:#5f6179;">
                        <?php echo htmlspecialchars($company['industry_type'] ?? 'N/A'); ?>
                    </div>
                </div>

                <div class="card" style="margin-bottom:22px;">
                    <div class="card-header">
                        <div>
                            <h3>Public Information</h3>
                            <p>How your company currently appears.</p>
                        </div>
                    </div>

                    <div class="detail-item" style="margin-bottom:12px;">
                        <strong>Website</strong>
                        <?php echo htmlspecialchars($company['website'] ?? 'N/A'); ?>
                    </div>

                    <div class="detail-item" style="margin-bottom:12px;">
                        <strong>Address</strong>
                        <?php echo htmlspecialchars($company['address'] ?? 'N/A'); ?>
                    </div>

                    <div class="detail-item">
                        <strong>Tax Code</strong>
                        <?php echo htmlspecialchars($company['tax_code'] ?? 'N/A'); ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <h3>Account Contact</h3>
                            <p>Primary contact information.</p>
                        </div>
                    </div>

                    <div class="detail-item" style="margin-bottom:12px; background:#ece5ff; border-color:#cdbbff;">
                        <strong>Email</strong>
                        <?php echo htmlspecialchars($company['email'] ?? 'N/A'); ?>
                    </div>

                    <div class="detail-item" style="background:#faf6e8; border-color:#f3d86e;">
                        <strong>Phone</strong>
                        <?php echo htmlspecialchars($company['phone'] ?? 'N/A'); ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>