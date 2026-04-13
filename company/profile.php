<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('company');

$user = currentUser();
$flash = getFlash();

$stmt = $pdo->prepare("
    SELECT c.*, u.full_name, u.email, u.phone
    FROM users u
    LEFT JOIN companies c ON u.id = c.user_id
    WHERE u.id = ?
");
$stmt->execute([$user['id']]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

$setupMode = isset($_GET['setup']);

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
                <a href="/Uniworksmohinhhoa/company/applications.php">Applicants</a>
                <a href="/Uniworksmohinhhoa/company/manage_job.php">Jobs</a>
                <a href="/Uniworksmohinhhoa/company/messages.php">Messages</a>
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
                <h2>Edit Company Information</h2>
                <p class="company-muted" style="margin-bottom:18px;">
                    Update the information students will see on your company profile.
                </p>

                <form action="../actions/company/update_profile_action.php" method="POST">
                    <div class="company-form-row">
                        <div class="company-form-group">
                            <label>Company Name</label>
                            <input
                                type="text"
                                name="company_name"
                                class="company-form-control"
                                value="<?= htmlspecialchars($profile['company_name'] ?? '') ?>"
                                required
                            >
                        </div>

                        <div class="company-form-group">
                            <label>Tax Code</label>
                            <input
                                type="text"
                                name="tax_code"
                                class="company-form-control"
                                value="<?= htmlspecialchars($profile['tax_code'] ?? '') ?>"
                                required
                            >
                        </div>
                    </div>

                    <div class="company-form-row">
                        <div class="company-form-group">
                            <label>Industry Type</label>
                            <input
                                type="text"
                                name="industry_type"
                                class="company-form-control"
                                value="<?= htmlspecialchars($profile['industry_type'] ?? '') ?>"
                            >
                        </div>

                        <div class="company-form-group">
                            <label>Website</label>
                            <input
                                type="text"
                                name="website"
                                class="company-form-control"
                                value="<?= htmlspecialchars($profile['website'] ?? '') ?>"
                            >
                        </div>
                    </div>

                    <div class="company-form-group">
                        <label>Address</label>
                        <input
                            type="text"
                            name="address"
                            class="company-form-control"
                            value="<?= htmlspecialchars($profile['address'] ?? '') ?>"
                        >
                    </div>

                    <div class="company-form-row">
                        <div class="company-form-group">
                            <label>Account Email</label>
                            <input
                                type="email"
                                class="company-form-control"
                                value="<?= htmlspecialchars($profile['email'] ?? '') ?>"
                                disabled
                            >
                        </div>
                    </div>

                    <button type="submit" class="company-btn">Update Profile</button>
                </form>
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
            </div>
        </section>
    </main>
</div>

<?php include '../includes/footer.php'; ?>