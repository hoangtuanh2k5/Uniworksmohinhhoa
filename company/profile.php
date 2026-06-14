<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

$previewMode = true;

<<<<<<< Updated upstream
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
=======
$stmt = $pdo->prepare("
    SELECT c.*, u.full_name, u.email, u.phone, u.avatar_url
    FROM users u
    LEFT JOIN companies c ON u.id = c.user_id
    WHERE u.id = ?
");
$stmt->execute([$user['id']]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

// Lấy danh sách documents
$docs = [];
if (!empty($profile['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM company_documents WHERE company_id = ? ORDER BY uploaded_at DESC");
    $stmt->execute([$profile['id']]);
    $docs = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$setupMode = isset($_GET['setup']);
>>>>>>> Stashed changes

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
<<<<<<< Updated upstream
                <h2>Uniworks</h2>
                <p>Recruiter Portal</p>
            </div>

            <nav class="company-nav">
                <a href="dashboard.php">Dashboard</a>
                <a href="applications.php">Applicants</a>
                <a href="manage_jobs.php">Jobs</a>
                <a href="messages.php">Messages</a>
                <a class="active" href="profile.php">Profile</a>
=======
                <div class="company-brand__logo">
                                <?php if (!empty($user['avatar_url'])): ?>
                                    <img src="/Uniworksmohinhhoa/<?= htmlspecialchars($user['avatar_url']) ?>" alt="avatar" style="width:34px;height:34px;min-width:34px;min-height:34px;max-width:34px;max-height:34px;object-fit:cover;border-radius:10px;display:block;">
                                <?php else: ?>
                                    ✦
                                <?php endif; ?>
                            </div>
                <div class="company-brand__text">
                    <h3><?= htmlspecialchars($profile['company_name'] ?: 'UniWorks') ?></h3>
                    <p>Recruiter Portal</p>
                </div>
            </div>

            <nav class="company-nav">
                <a href="/Uniworksmohinhhoa/company/dashboard.php">Dashboard</a>
                <a href="/Uniworksmohinhhoa/company/applications.php">Applicants</a>
                <a href="/Uniworksmohinhhoa/company/manage_job.php">Jobs</a>
                <a href="/Uniworksmohinhhoa/company/internship_history.php">History</a>
                <a href="/Uniworksmohinhhoa/company/evaluations.php">Evaluations</a>
                <a href="/Uniworksmohinhhoa/company/messages.php">Messages</a>
                <a href="/Uniworksmohinhhoa/company/profile.php" class="active">Profile</a>
>>>>>>> Stashed changes
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
<<<<<<< Updated upstream
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
=======
                <h1>Company Profile</h1>
                <p>
                    Keep your company profile polished and up to date so students can quickly
                    understand your brand, industry, and contact details before applying.
                </p>
            </div>
            <div class="company-profile-hero__status">
                <span class="company-pill-success">Profile Active</span>
                <small>Last updated just now</small>
            </div>
        </section>

        <section class="company-profile-grid">
            <div class="company-form-card company-form-card--large">

                <!-- AVATAR UPLOAD -->
                <h2 style="margin-bottom:14px;">Profile Photo</h2>
                <div style="display:flex;align-items:center;gap:20px;margin-bottom:24px;">
                    <?php if (!empty($profile['avatar_url'])): ?>
                        <img src="/Uniworksmohinhhoa/<?= htmlspecialchars($profile['avatar_url']) ?>"
                             style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:2px solid #e1e3ee;">
                    <?php else: ?>
                        <div style="width:72px;height:72px;border-radius:50%;background:#ece8fb;display:flex;align-items:center;justify-content:center;font-size:28px;color:#7c6fcf;">✦</div>
                    <?php endif; ?>
                    <form action="/Uniworksmohinhhoa/actions/auth/upload_avatar_action.php" method="POST" enctype="multipart/form-data">
                        <label style="font-size:13px;font-weight:700;display:block;margin-bottom:6px;">Upload Photo (JPG/PNG/WEBP, max 2MB)</label>
                        <div style="display:flex;gap:10px;align-items:center;">
                            <input type="file" name="avatar" accept="image/*" class="company-form-control" style="height:auto;padding:6px 10px;">
                            <button type="submit" class="company-btn" style="white-space:nowrap;">Upload</button>
                        </div>
                    </form>
                </div>

                <hr style="border:none;border-top:1px solid #eceef6;margin:0 0 24px;">

                <!-- COMPANY INFO FORM -->
                <h2 style="margin-bottom:6px;">Edit Company Information</h2>
                <p class="company-muted" style="margin-bottom:18px;">
                    Update the information students will see on your company profile.
                </p>

                <form action="../actions/company/update_profile_action.php" method="POST">
                    <div class="company-form-row">
                        <div class="company-form-group">
                            <label>Company Name</label>
                            <input type="text" name="company_name" class="company-form-control"
                                   value="<?= htmlspecialchars($profile['company_name'] ?? '') ?>" required>
                        </div>
                        <div class="company-form-group">
                            <label>Tax Code</label>
                            <input type="text" name="tax_code" class="company-form-control"
                                   value="<?= htmlspecialchars($profile['tax_code'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="company-form-row">
                        <div class="company-form-group">
                            <label>Industry Type</label>
                            <input type="text" name="industry_type" class="company-form-control"
                                   value="<?= htmlspecialchars($profile['industry_type'] ?? '') ?>">
                        </div>
                        <div class="company-form-group">
                            <label>Website</label>
                            <input type="text" name="website" class="company-form-control"
                                   value="<?= htmlspecialchars($profile['website'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="company-form-row">
                        <div class="company-form-group">
                            <label>Address</label>
                            <input type="text" name="address" class="company-form-control"
                                   value="<?= htmlspecialchars($profile['address'] ?? '') ?>">
                        </div>
                        <div class="company-form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phone" class="company-form-control"
                                   value="<?= htmlspecialchars($profile['phone'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="company-form-row">
                        <div class="company-form-group">
                            <label>Account Email</label>
                            <input type="email" class="company-form-control"
                                   value="<?= htmlspecialchars($profile['email'] ?? '') ?>" disabled>
                        </div>
                    </div>

                    <button type="submit" class="company-btn">Update Profile</button>
                </form>

                <hr style="border:none;border-top:1px solid #eceef6;margin:28px 0;">

                <!-- CHANGE PASSWORD -->
                <h2 style="margin-bottom:6px;">Change Password</h2>
                <p class="company-muted" style="margin-bottom:18px;">Leave blank if you do not want to change your password.</p>

                <form action="/Uniworksmohinhhoa/actions/auth/change_password_action.php" method="POST">
                    <div class="company-form-row">
                        <div class="company-form-group">
                            <label>Current Password</label>
                            <input type="password" name="current_password" class="company-form-control" placeholder="Enter current password" required>
                        </div>
                    </div>
                    <div class="company-form-row">
                        <div class="company-form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" class="company-form-control" placeholder="At least 6 characters" required>
                        </div>
                        <div class="company-form-group">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_password" class="company-form-control" placeholder="Repeat new password" required>
                        </div>
                    </div>
                    <button type="submit" class="company-btn">Change Password</button>
                </form>

                <hr style="border:none;border-top:1px solid #eceef6;margin:28px 0;">

                <!-- DOCUMENTS -->
                <h2 style="margin-bottom:6px;">Company Documents</h2>
                <p class="company-muted" style="margin-bottom:18px;">
                    Upload legal and verification documents for university review
                    (Business License, Tax Certificate, Company Profile, etc.)
                </p>

                <form action="/Uniworksmohinhhoa/actions/company/upload_document_action.php" method="POST" enctype="multipart/form-data">
                    <div class="company-form-row">
                        <div class="company-form-group">
                            <label>Document Type</label>
                            <select name="doc_type" class="company-form-control">
                                <option value="Business License">Business License</option>
                                <option value="Tax Certificate">Tax Certificate</option>
                                <option value="Company Profile">Company Profile</option>
                                <option value="Brand / Logo">Brand / Logo</option>
                                <option value="Partnership Agreement">Partnership Agreement</option>
                                <option value="Other">Other Legal Document</option>
                            </select>
                        </div>
                        <div class="company-form-group">
                            <label>File (PDF/JPG/PNG/DOC, max 10MB)</label>
                            <input type="file" name="document" class="company-form-control" style="height:auto;padding:6px 10px;" required
                                   accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        </div>
                    </div>
                    <button type="submit" class="company-btn">Upload Document</button>
                </form>

                <?php if (!empty($docs)): ?>
                    <div style="margin-top:22px;display:grid;gap:10px;">
                        <?php foreach ($docs as $doc): ?>
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:#f8f9fc;border:1px solid #eceef6;border-radius:14px;">
                                <div>
                                    <div style="font-size:14px;font-weight:700;color:#17172b;"><?= htmlspecialchars($doc['doc_name']) ?></div>
                                    <div style="font-size:12px;color:#7f8496;margin-top:3px;"><?= htmlspecialchars($doc['doc_type']) ?> &middot; <?= date('M d, Y', strtotime($doc['uploaded_at'])) ?></div>
                                </div>
                                <div style="display:flex;gap:10px;align-items:center;">
                                    <a href="/Uniworksmohinhhoa/<?= htmlspecialchars($doc['doc_url']) ?>" target="_blank"
                                       class="company-btn-outline" style="font-size:13px;min-height:34px;padding:0 12px;">View</a>
                                    <a href="/Uniworksmohinhhoa/actions/company/delete_document_action.php?id=<?= $doc['id'] ?>"
                                       onclick="return confirm('Delete this document?')"
                                       style="font-size:13px;color:#b42323;font-weight:700;">Delete</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="company-muted" style="margin-top:14px;">No documents uploaded yet.</p>
                <?php endif; ?>

            </div>

            <div class="company-profile-side">
                <div class="company-profile-snapshot">
                    <div class="company-profile-snapshot__top">
                        <span class="company-muted">Company Snapshot</span>
                        <span class="company-pill-success">Brand</span>
                    </div>
                    <h3><?= htmlspecialchars($profile['company_name'] ?: 'Your Company Name') ?></h3>
                    <p><?= htmlspecialchars($profile['industry_type'] ?: 'Industry Type') ?></p>
>>>>>>> Stashed changes
                </div>
            </div>

            <div>
                <div class="stat-card yellow" style="margin-bottom:22px;">
                    <span class="stat-pill">Brand</span>
                    <h4>Company Snapshot</h4>
                    <div class="stat-value" style="font-size:24px; margin-bottom:10px;">
                        <?php echo htmlspecialchars($company['company_name'] ?? 'N/A'); ?>
                    </div>
<<<<<<< Updated upstream
                    <div class="small-muted" style="color:#5f6179;">
                        <?php echo htmlspecialchars($company['industry_type'] ?? 'N/A'); ?>
=======
                    <div class="company-info-box">
                        <label>Address</label>
                        <p><?= htmlspecialchars($profile['address'] ?: '-') ?></p>
                    </div>
                    <div class="company-info-box">
                        <label>Tax Code</label>
                        <p><?= htmlspecialchars($profile['tax_code'] ?: '-') ?></p>
>>>>>>> Stashed changes
                    </div>
                    <div class="company-info-box">
                        <label>Phone</label>
                        <p><?= htmlspecialchars($profile['phone'] ?: '-') ?></p>
                    </div>
                </div>

                <div class="card" style="margin-bottom:22px;">
                    <div class="card-header">
                        <div>
                            <h3>Public Information</h3>
                            <p>How your company currently appears.</p>
                        </div>
                    </div>
<<<<<<< Updated upstream

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
=======
                    <div class="company-info-box company-info-box--yellow">
                        <label>Email</label>
                        <p><?= htmlspecialchars($profile['email'] ?: '-') ?></p>
>>>>>>> Stashed changes
                    </div>
                </div>

                <div class="company-card">
                    <h3 style="margin-bottom:8px;">Documents</h3>
                    <p class="company-muted" style="margin-bottom:6px;"><?= count($docs) ?> file(s) uploaded</p>
                    <?php if (!empty($docs)): ?>
                        <div style="display:grid;gap:6px;margin-top:10px;">
                            <?php foreach (array_slice($docs, 0, 3) as $doc): ?>
                                <div style="font-size:13px;color:#4b5265;padding:6px 0;border-bottom:1px solid #eceef6;">
                                    📄 <?= htmlspecialchars($doc['doc_name']) ?>
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($docs) > 3): ?>
                                <p class="company-muted" style="font-size:12px;">+<?= count($docs) - 3 ?> more</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>
<<<<<<< Updated upstream
</body>
</html>
=======

<?php include '../includes/footer.php'; ?>
>>>>>>> Stashed changes
