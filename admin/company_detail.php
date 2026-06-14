<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('admin');

$company_id = (int)($_GET['id'] ?? 0);

if ($company_id <= 0) {
    setFlash('error', 'Invalid company.');
    redirect('/Uniworksmohinhhoa/admin/users.php?tab=company');
}

$stmt = $pdo->prepare("
    SELECT c.*, u.full_name, u.email, u.phone
    FROM companies c
    INNER JOIN users u ON c.user_id = u.id
    WHERE c.id = ?
");
$stmt->execute([$company_id]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company) {
    setFlash('error', 'Company not found.');
    redirect('/Uniworksmohinhhoa/admin/users.php?tab=company');
}

// Tài liệu đã upload
$docs = $pdo->prepare("SELECT * FROM company_documents WHERE company_id = ? ORDER BY uploaded_at DESC");
$docs->execute([$company_id]);
$documents = $docs->fetchAll(PDO::FETCH_ASSOC);

// Thống kê jobs
$totalJobs = (int)$pdo->prepare("SELECT COUNT(*) FROM jobs WHERE company_id = ?")->execute([$company_id])
    ? $pdo->query("SELECT COUNT(*) FROM jobs WHERE company_id = $company_id")->fetchColumn() : 0;

require_once '../includes/notifications.php';
include '../includes/header.php';
?>

<style>
.admin-brand { padding:8px 8px 28px; margin-bottom:16px; }
.admin-brand h2 { margin:0; font-size:24px; color:#1b2038; font-weight:800; }
.admin-nav { display:flex; flex-direction:column; gap:12px; margin-top:10px; }

.admin-cd-page { padding:8px 6px 24px; }
.admin-cd-header { margin-bottom:24px; }
.admin-cd-header h1 { margin:0 0 10px; font-size:44px; line-height:1.08; color:#161b34; font-weight:800; }
.admin-cd-header p { margin:0; font-size:18px; color:#707894; line-height:1.6; }

.admin-cd-layout { display:grid; grid-template-columns:1.35fr .9fr; gap:24px; align-items:start; }

.admin-cd-card {
    background:#fff; border:1px solid #ece9f7; border-radius:30px;
    padding:28px; box-shadow:0 12px 28px rgba(31,34,51,.05);
}
.admin-cd-card h2 { margin:0 0 18px; font-size:22px; color:#161b34; font-weight:800; }

.admin-cd-hero { display:flex; align-items:flex-start; gap:20px; margin-bottom:24px; }
.admin-cd-avatar {
    width:84px; height:84px; border-radius:24px;
    background: linear-gradient(135deg,#f0d86b,#cfc6f6);
    color:#1f2233; display:flex; align-items:center; justify-content:center;
    font-size:32px; font-weight:800; flex-shrink:0;
}
.admin-cd-main h2 { margin:0 0 6px; font-size:32px; color:#161b34; font-weight:800; }
.admin-cd-main p  { margin:0; font-size:17px; color:#6f7790; }

.admin-cd-grid { display:grid; grid-template-columns:repeat(2, 1fr); gap:16px; }
.admin-info-box { background:#fcfbff; border:1px solid #f0ebfb; border-radius:20px; padding:16px; }
.admin-info-box span { display:block; margin-bottom:6px; font-size:12px; font-weight:800; letter-spacing:.05em; text-transform:uppercase; color:#8b93ab; }
.admin-info-box strong { color:#1f2233; font-size:16px; line-height:1.5; word-break:break-word; }

/* Documents */
.admin-cd-doc-item {
    display:flex; align-items:center; justify-content:space-between; gap:12px;
    padding:12px 14px; background:#f8f9fc; border:1px solid #eceef6;
    border-radius:14px; margin-bottom:10px;
}
.admin-cd-doc-item:last-child { margin-bottom:0; }
.admin-cd-doc-info p { margin:0; font-size:14px; font-weight:700; color:#17172b; }
.admin-cd-doc-info small { font-size:12px; color:#7f8496; }
.admin-cd-doc-link {
    display:inline-flex; align-items:center; gap:5px;
    padding:7px 14px; border-radius:10px; background:#e7dcff;
    color:#5b43a7; font-size:13px; font-weight:700; text-decoration:none;
    white-space:nowrap; flex-shrink:0;
}
.admin-cd-doc-link:hover { background:#ddd0ff; }

.admin-cd-no-docs { color:#9093a2; font-size:14px; font-style:italic; }

.admin-side-stack { display:flex; flex-direction:column; gap:22px; }
.admin-highlight { background:#fff7d6; border:1px solid #efe0a0; border-radius:26px; padding:22px; }
.admin-highlight h3 { margin:0 0 10px; font-size:20px; color:#161b34; font-weight:800; }
.admin-highlight p { margin:0; color:#6f7790; line-height:1.7; font-size:15px; }

.admin-mini-card { background:#fff; border:1px solid #ece9f7; border-radius:26px; padding:22px; box-shadow:0 10px 24px rgba(31,34,51,.04); }
.admin-mini-card h3 { margin:0 0 14px; font-size:20px; color:#161b34; font-weight:800; }
.admin-mini-list { display:grid; gap:12px; }
.admin-mini-item { display:flex; justify-content:space-between; gap:16px; padding:12px 0; border-bottom:1px solid #f1edf8; font-size:14px; }
.admin-mini-item:last-child { border-bottom:none; padding-bottom:0; }
.admin-mini-item span { color:#8a93aa; font-weight:600; }
.admin-mini-item strong { color:#1f2233; text-align:right; }

.admin-btn-row { display:flex; flex-wrap:wrap; gap:12px; margin-top:18px; }
.admin-back-btn {
    display:inline-flex; align-items:center; justify-content:center;
    min-width:120px; height:44px; padding:0 18px; border:none; border-radius:14px;
    text-decoration:none; font-size:14px; font-weight:800; cursor:pointer;
    background:#f6f2ff; color:#5e6680; transition:.2s;
}
.admin-back-btn:hover { background:#eee7ff; }

@media(max-width:1100px){ .admin-cd-layout { grid-template-columns:1fr; } }
@media(max-width:768px){
    .admin-cd-grid { grid-template-columns:1fr; }
    .admin-cd-hero { flex-direction:column; }
}
</style>

<div class="admin-shell">
    <aside class="admin-sidebar">
        <div>
            <div class="admin-brand"><h2>Admin Panel</h2></div>
            <nav class="admin-nav">
                <a href="/Uniworksmohinhhoa/admin/dashboard.php">Dashboard</a>
                <a href="/Uniworksmohinhhoa/admin/users.php" class="active">Users</a>
                <a href="/Uniworksmohinhhoa/admin/applications.php">Applications</a>
                <a href="/Uniworksmohinhhoa/admin/monitoring.php">Monitoring</a>
                <a href="/Uniworksmohinhhoa/admin/reports.php">Reports<?php if(!empty($notif['reports']) && $notif['reports']>0): ?><span class="notif-badge"><?= $notif['reports'] ?></span><?php endif; ?></a>
            </nav>
        </div>
        <div class="admin-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">Logout</a>
        </div>
    </aside>

    <main class="admin-main">
        <div class="admin-cd-page">
            <div class="admin-cd-header">
                <h1>Company Profile</h1>
                <p>View company information and uploaded verification documents.</p>
            </div>

            <div class="admin-cd-layout">
                <!-- LEFT: info + docs -->
                <section>
                    <div class="admin-cd-card" style="margin-bottom:22px;">
                        <div class="admin-cd-hero">
                            <div class="admin-cd-avatar">
                                <?= strtoupper(substr($company['company_name'] ?? $company['full_name'], 0, 1)) ?>
                            </div>
                            <div class="admin-cd-main">
                                <h2><?= htmlspecialchars($company['company_name'] ?: '(No company name)') ?></h2>
                                <p><?= htmlspecialchars($company['industry_type'] ?: 'Industry not set') ?></p>
                            </div>
                        </div>

                        <div class="admin-cd-grid">
                            <div class="admin-info-box">
                                <span>Contact Name</span>
                                <strong><?= htmlspecialchars($company['full_name']) ?></strong>
                            </div>
                            <div class="admin-info-box">
                                <span>Email</span>
                                <strong><?= htmlspecialchars($company['email']) ?></strong>
                            </div>
                            <div class="admin-info-box">
                                <span>Phone</span>
                                <strong><?= htmlspecialchars($company['phone'] ?: '—') ?></strong>
                            </div>
                            <div class="admin-info-box">
                                <span>Tax Code</span>
                                <strong><?= htmlspecialchars($company['tax_code'] ?: '—') ?></strong>
                            </div>
                            <div class="admin-info-box">
                                <span>Address</span>
                                <strong><?= htmlspecialchars($company['address'] ?: '—') ?></strong>
                            </div>
                            <div class="admin-info-box">
                                <span>Website</span>
                                <strong>
                                    <?php if (!empty($company['website'])): ?>
                                        <a href="<?= htmlspecialchars($company['website']) ?>" target="_blank"
                                           style="color:#7f4df3;">
                                            <?= htmlspecialchars($company['website']) ?>
                                        </a>
                                    <?php else: ?>—<?php endif; ?>
                                </strong>
                            </div>
                        </div>
                    </div>

                    <!-- Documents -->
                    <div class="admin-cd-card">
                        <h2>Uploaded Documents (<?= count($documents) ?>)</h2>
                        <?php if (empty($documents)): ?>
                            <p class="admin-cd-no-docs">No documents uploaded yet.</p>
                        <?php else: ?>
                            <?php foreach ($documents as $doc): ?>
                                <div class="admin-cd-doc-item">
                                    <div class="admin-cd-doc-info">
                                        <p><?= htmlspecialchars($doc['doc_name']) ?></p>
                                        <small>
                                            <?= htmlspecialchars($doc['doc_type'] ?? 'Document') ?>
                                            &nbsp;·&nbsp;
                                            <?= date('d M Y', strtotime($doc['uploaded_at'])) ?>
                                        </small>
                                    </div>
                                    <a href="/Uniworksmohinhhoa/uploads/company_docs/<?= htmlspecialchars($doc['doc_url']) ?>"
                                       target="_blank" class="admin-cd-doc-link">
                                        📎 View
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- RIGHT: summary -->
                <aside class="admin-side-stack">
                    <div class="admin-highlight">
                        <h3>Verification Note</h3>
                        <p>
                            Review the uploaded Business License and other documents to verify the company's legitimacy before approving internship postings.
                        </p>
                    </div>

                    <div class="admin-mini-card">
                        <h3>Quick Summary</h3>
                        <div class="admin-mini-list">
                            <div class="admin-mini-item">
                                <span>Company</span>
                                <strong><?= htmlspecialchars($company['company_name'] ?: '—') ?></strong>
                            </div>
                            <div class="admin-mini-item">
                                <span>Tax Code</span>
                                <strong><?= htmlspecialchars($company['tax_code'] ?: '—') ?></strong>
                            </div>
                            <div class="admin-mini-item">
                                <span>Industry</span>
                                <strong><?= htmlspecialchars($company['industry_type'] ?: '—') ?></strong>
                            </div>
                            <div class="admin-mini-item">
                                <span>Documents</span>
                                <strong><?= count($documents) ?> file(s)</strong>
                            </div>
                            <div class="admin-mini-item">
                                <span>Business License</span>
                                <strong>
                                    <?php
                                    $hasLicense = array_filter($documents, fn($d) => $d['doc_type'] === 'Business License');
                                    echo $hasLicense ? '✅ Uploaded' : '❌ Not uploaded';
                                    ?>
                                </strong>
                            </div>
                        </div>

                        <div class="admin-btn-row">
                            <a href="/Uniworksmohinhhoa/admin/users.php?tab=company" class="admin-back-btn">← Back</a>
                            <a href="/Uniworksmohinhhoa/admin/edit_user.php?id=<?= $company['user_id'] ?>"
                               style="display:inline-flex;align-items:center;justify-content:center;min-width:120px;height:44px;padding:0 18px;border-radius:14px;background:#cfc6f6;color:#17172b;font-weight:800;font-size:14px;text-decoration:none;">
                                Edit User
                            </a>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>
