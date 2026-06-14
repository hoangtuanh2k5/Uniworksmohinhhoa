<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('company');

$user = currentUser();
$flash = getFlash();

$stmt = $pdo->prepare("
    SELECT c.*, u.full_name, u.email, u.phone, u.avatar
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

require_once '../includes/notifications.php';
include '../includes/header.php';
?>

<div class="company-shell">
    <aside class="company-sidebar">
        <div>
            <div class="company-brand">
                <div class="company-brand__logo">✦</div>
                <div class="company-brand__text">
                    <h3><?= htmlspecialchars($profile['company_name'] ?: 'UniWorks') ?></h3>
                    <p>Recruiter Portal</p>
                </div>
            </div>

            <nav class="company-nav">
                <a href="/Uniworksmohinhhoa/company/dashboard.php">Dashboard</a>
                <a href="/Uniworksmohinhhoa/company/applications.php">Applicants<?php if(!empty($notif['reports']) && $notif['reports']>0): ?><span class="notif-badge"><?= $notif['reports'] ?></span><?php endif; ?></a>
                <a href="/Uniworksmohinhhoa/company/manage_job.php">Jobs</a>
                <a href="/Uniworksmohinhhoa/company/messages.php">Messages<?php if(!empty($notif['messages']) && $notif['messages']>0): ?><span class="notif-badge"><?= $notif['messages'] ?></span><?php endif; ?></a>
                <a href="/Uniworksmohinhhoa/company/profile.php" class="active">Profile</a>
            </nav>
        </div>

        <div class="company-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">↩ Logout</a>
        </div>
    </aside>

    <main class="company-main">
        <div class="company-topbar company-topbar--profile">
            <div></div>
            <a href="/Uniworksmohinhhoa/company/dashboard.php" class="company-btn">Back to Dashboard</a>
        </div>

        <?php if ($setupMode): ?>
            <div class="flash success" style="margin-bottom:18px;">
                Please complete your company profile before continuing.
            </div>
        <?php endif; ?>

        <?php if ($flash): ?>
            <div class="flash <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:18px;">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <section class="company-profile-hero">
            <div>
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

                <!-- AVATAR -->
                <h2 style="margin-bottom:14px;">Profile Photo</h2>
                <div style="display:flex;align-items:center;gap:18px;margin-bottom:22px;">
                    <?php if (!empty($profile['avatar'])): ?>
                        <img src="/Uniworksmohinhhoa/uploads/avatars/<?= htmlspecialchars($profile['avatar']) ?>"
                             alt="Avatar" style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:2px solid #eceef6;">
                    <?php else: ?>
                        <div style="width:72px;height:72px;border-radius:50%;background:#e7dcff;display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:800;color:#5e57df;">
                            <?= strtoupper(substr($profile['full_name'] ?? 'C', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <form action="/Uniworksmohinhhoa/actions/auth/upload_avatar_action.php" method="POST" enctype="multipart/form-data" style="display:flex;align-items:center;gap:10px;">
                        <input type="file" name="avatar" accept="image/*" class="company-form-control" style="width:auto;height:auto;padding:6px 10px;border-radius:10px;" required>
                        <button type="submit" class="company-btn" style="min-height:38px;padding:0 14px;font-size:13px;">Upload</button>
                    </form>
                </div>

                <hr style="border:none;border-top:1px solid #eceef6;margin-bottom:22px;">

                <!-- COMPANY INFO -->
                <h2 style="margin-bottom:6px;">Edit Company Information</h2>
                <p class="company-muted" style="margin-bottom:18px;">Update the information students will see on your company profile.</p>

                <form action="/Uniworksmohinhhoa/actions/company/update_profile_action.php" method="POST">
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
                            <label>Phone Number</label>
                            <input type="text" name="phone" class="company-form-control"
                                   value="<?= htmlspecialchars($profile['phone'] ?? '') ?>" placeholder="0901234567">
                        </div>
                        <div class="company-form-group">
                            <label>Industry Type</label>
                            <input type="text" name="industry_type" class="company-form-control"
                                   value="<?= htmlspecialchars($profile['industry_type'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="company-form-row">
                        <div class="company-form-group">
                            <label>Website</label>
                            <input type="text" name="website" class="company-form-control"
                                   value="<?= htmlspecialchars($profile['website'] ?? '') ?>">
                        </div>
                        <div class="company-form-group">
                            <label>Address</label>
                            <input type="text" name="address" class="company-form-control"
                                   value="<?= htmlspecialchars($profile['address'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="company-form-group">
                        <label>Account Email</label>
                        <input type="email" class="company-form-control"
                               value="<?= htmlspecialchars($profile['email'] ?? '') ?>" disabled>
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

                <!-- COMPANY DOCUMENTS -->
                <h2 style="margin-bottom:6px;">Company Documents</h2>
                <p class="company-muted" style="margin-bottom:18px;">
                    Upload legal and verification documents for university review
                    (Business License, Tax Certificate, Company Profile, etc.). Max 5MB per file. PDF, DOC, DOCX, JPG, PNG.
                </p>

                <form action="/Uniworksmohinhhoa/actions/company/upload_document_action.php" method="POST" enctype="multipart/form-data">
                    <div class="company-form-row" style="align-items:end;">
                        <div class="company-form-group">
                            <label>Document Type</label>
                            <select name="doc_type" class="company-form-control">
                                <option value="">-- Select type --</option>
                                <option value="Business License">Business License</option>
                                <option value="Tax Certificate">Tax Certificate</option>
                                <option value="Company Profile">Company Profile</option>
                                <option value="Company Logo / Branding">Company Logo / Branding</option>
                                <option value="Cooperation Agreement">Cooperation Agreement</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="company-form-group">
                            <label>File</label>
                            <input type="file" name="document" class="company-form-control"
                                   style="padding:6px 10px;height:auto;" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                        </div>
                    </div>
                    <button type="submit" class="company-btn">Upload Document</button>
                </form>

                <?php if (!empty($docs)): ?>
                    <div style="margin-top:22px;display:grid;gap:10px;">
                        <?php foreach ($docs as $doc): ?>
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:#f8f9fc;border:1px solid #eceef6;border-radius:14px;">
                                <div>
                                    <p style="font-weight:700;font-size:14px;color:#17172b;margin-bottom:2px;">
                                        <?= htmlspecialchars($doc['doc_name']) ?>
                                    </p>
                                    <p style="font-size:12px;color:#7f8496;">
                                        <?= htmlspecialchars($doc['doc_type'] ?? 'Document') ?>
                                        &nbsp;·&nbsp; <?= date('d M Y', strtotime($doc['uploaded_at'])) ?>
                                    </p>
                                </div>
                                <div style="display:flex;gap:10px;align-items:center;">
                                    <a href="/Uniworksmohinhhoa/uploads/company_docs/<?= htmlspecialchars($doc['doc_url']) ?>"
                                       target="_blank" class="company-btn-outline" style="font-size:13px;min-height:34px;padding:0 12px;">View</a>
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
                </div>

                <div class="company-card">
                    <h3 style="margin-bottom:8px;">Public Information</h3>
                    <p class="company-muted" style="margin-bottom:18px;">How your company currently appears.</p>

                    <div class="company-info-box">
                        <label>Website</label>
                        <p><?= htmlspecialchars($profile['website'] ?: '-') ?></p>
                    </div>
                    <div class="company-info-box">
                        <label>Address</label>
                        <p><?= htmlspecialchars($profile['address'] ?: '-') ?></p>
                    </div>
                    <div class="company-info-box">
                        <label>Tax Code</label>
                        <p><?= htmlspecialchars($profile['tax_code'] ?: '-') ?></p>
                    </div>
                    <div class="company-info-box">
                        <label>Phone</label>
                        <p><?= htmlspecialchars($profile['phone'] ?: '-') ?></p>
                    </div>
                </div>

                <div class="company-card">
                    <h3 style="margin-bottom:8px;">Account Contact</h3>
                    <p class="company-muted" style="margin-bottom:18px;">Primary account information.</p>

                    <div class="company-info-box company-info-box--purple">
                        <label>Full Name</label>
                        <p><?= htmlspecialchars($profile['full_name'] ?: '-') ?></p>
                    </div>
                    <div class="company-info-box company-info-box--yellow">
                        <label>Email</label>
                        <p><?= htmlspecialchars($profile['email'] ?: '-') ?></p>
                    </div>
                </div>

                <div class="company-card">
                    <h3 style="margin-bottom:8px;">Documents</h3>
                    <p class="company-muted" style="margin-bottom:4px;"><?= count($docs) ?> file(s) uploaded</p>
                    <?php if (!empty($docs)): ?>
                        <div style="margin-top:10px;display:grid;gap:6px;">
                            <?php foreach (array_slice($docs, 0, 3) as $doc): ?>
                                <p style="font-size:13px;color:#5f667a;">📄 <?= htmlspecialchars($doc['doc_type'] ?? $doc['doc_name']) ?></p>
                            <?php endforeach; ?>
                            <?php if (count($docs) > 3): ?>
                                <p style="font-size:12px;color:#9093a2;">+<?= count($docs) - 3 ?> more</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
</div>

<?php include '../includes/footer.php'; ?>
