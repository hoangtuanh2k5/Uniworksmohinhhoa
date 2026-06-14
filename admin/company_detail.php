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
    SELECT c.*, u.full_name, u.email, u.phone, u.avatar_url, u.created_at AS registered_at
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

// Số jobs
$totalJobs = (int)$pdo->prepare("SELECT COUNT(*) FROM jobs WHERE company_id = ?")->execute([$company_id]) ? 0 : 0;
$s = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE company_id = ?");
$s->execute([$company_id]);
$totalJobs = (int)$s->fetchColumn();

// Số applications đã nhận
$s = $pdo->prepare("SELECT COUNT(*) FROM applications a INNER JOIN jobs j ON a.job_id = j.id WHERE j.company_id = ?");
$s->execute([$company_id]);
$totalApps = (int)$s->fetchColumn();

// Số intern đang active
$s = $pdo->prepare("
    SELECT COUNT(*) FROM internship_registrations ir
    INNER JOIN applications a ON ir.application_id = a.id
    INNER JOIN jobs j ON a.job_id = j.id
    WHERE j.company_id = ? AND ir.status = 'ongoing'
");
$s->execute([$company_id]);
$activeInterns = (int)$s->fetchColumn();

// Documents
$s = $pdo->prepare("SELECT * FROM company_documents WHERE company_id = ? ORDER BY uploaded_at DESC");
$s->execute([$company_id]);
$docs = $s->fetchAll(PDO::FETCH_ASSOC);

// Recent jobs
$s = $pdo->prepare("
    SELECT j.id, j.title, j.deadline, j.slots, j.status, ip.name AS period_name
    FROM jobs j
    LEFT JOIN internship_periods ip ON j.period_id = ip.id
    WHERE j.company_id = ?
    ORDER BY j.id DESC LIMIT 5
");
$s->execute([$company_id]);
$recentJobs = $s->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="admin-shell">
    <aside class="admin-sidebar">
        <div>
            <div class="admin-brand"><h2>Admin Panel</h2></div>
            <nav class="admin-nav">
                <a href="/Uniworksmohinhhoa/admin/dashboard.php">Dashboard</a>
                <a href="/Uniworksmohinhhoa/admin/users.php" class="active">Users</a>
                <a href="/Uniworksmohinhhoa/admin/company_approvals.php">Companies</a>
                <a href="/Uniworksmohinhhoa/admin/applications.php">Applications</a>
                <a href="/Uniworksmohinhhoa/admin/monitoring.php">Monitoring</a>
                <a href="/Uniworksmohinhhoa/admin/reports.php">Reports</a>
            </nav>
        </div>
        <div class="admin-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">Logout</a>
        </div>
    </aside>

    <main class="admin-main">
        <!-- Topbar -->
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:14px;">
            <div>
                <h1 style="margin:0 0 6px;font-size:38px;font-weight:800;color:#161b34;">Company Profile</h1>
                <p style="margin:0;font-size:16px;color:#707894;">Full overview of company account and activity.</p>
            </div>
            <a href="/Uniworksmohinhhoa/admin/users.php?tab=company"
               style="padding:10px 20px;border-radius:14px;background:#f6f2ff;color:#5e6680;font-size:14px;font-weight:700;text-decoration:none;">
                ← Back to Users
            </a>
        </div>

        <div style="display:grid;grid-template-columns:1.4fr 0.9fr;gap:22px;align-items:start;">

            <!-- Left -->
            <div style="display:grid;gap:20px;">

                <!-- Hero card -->
                <div class="admin-panel" style="padding:26px;">
                    <div style="display:flex;align-items:center;gap:20px;margin-bottom:24px;">
                        <?php if (!empty($company['avatar_url'])): ?>
                            <img src="/Uniworksmohinhhoa/<?= htmlspecialchars($company['avatar_url']) ?>"
                                 style="width:80px;height:80px;border-radius:20px;object-fit:cover;border:2px solid #eceef6;">
                        <?php else: ?>
                            <div style="width:80px;height:80px;border-radius:20px;background:#cfc6f6;display:flex;align-items:center;justify-content:center;font-size:32px;font-weight:800;color:#17172b;flex-shrink:0;">
                                <?= strtoupper(substr($company['company_name'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <div>
                            <div style="font-size:28px;font-weight:800;color:#161b34;margin-bottom:4px;"><?= htmlspecialchars($company['company_name']) ?></div>
                            <div style="font-size:14px;color:#7a8096;"><?= htmlspecialchars($company['industry_type'] ?: 'Industry not set') ?></div>
                            <div style="margin-top:8px;">
                                <?php
                                $cs = $company['status'];
                                $csBadge = match($cs) {
                                    'approved'  => 'background:#d8f2df;color:#187a3d;',
                                    'rejected'  => 'background:#ffe1e1;color:#ad3e3e;',
                                    'suspended' => 'background:#fff3e0;color:#b45309;',
                                    default     => 'background:#f6e8a6;color:#a36a00;',
                                };
                                ?>
                                <span style="display:inline-flex;align-items:center;padding:5px 12px;border-radius:999px;font-size:12px;font-weight:800;<?= $csBadge ?>">
                                    <?= ucfirst($cs) ?>
                                </span>
                                <span style="margin-left:10px;font-size:12px;color:#9093a2;">CO-<?= $company['id'] ?> &middot; USR-<?= $company['user_id'] ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Info grid -->
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                        <?php
                        $fields = [
                            'Representative' => $company['full_name'],
                            'Email'          => $company['email'],
                            'Phone'          => $company['phone'] ?: '—',
                            'Tax Code'       => $company['tax_code'],
                            'Address'        => $company['address'] ?: '—',
                            'Website'        => $company['website'] ?: '—',
                            'Industry'       => $company['industry_type'] ?: '—',
                            'Registered'     => date('M d, Y', strtotime($company['registered_at'])),
                        ];
                        foreach ($fields as $label => $value):
                        ?>
                        <div style="background:#fafbff;border:1px solid #eceef6;border-radius:16px;padding:14px 16px;">
                            <div style="font-size:11px;font-weight:800;color:#8b93ab;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;"><?= $label ?></div>
                            <div style="font-size:15px;font-weight:600;color:#1f2233;word-break:break-word;"><?= htmlspecialchars($value) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Recent Jobs -->
                <div class="admin-panel" style="padding:22px 26px;">
                    <h3 style="margin:0 0 16px;font-size:18px;font-weight:800;color:#161b34;">Recent Job Posts</h3>
                    <?php if (empty($recentJobs)): ?>
                        <p style="color:#7a8096;font-size:14px;">No jobs posted yet.</p>
                    <?php else: ?>
                        <table style="width:100%;border-collapse:collapse;">
                            <thead>
                                <tr>
                                    <?php foreach (['Job ID','Title','Period','Deadline','Slots','Status'] as $h): ?>
                                        <th style="text-align:left;padding:10px 12px;background:#faf8ff;color:#74809b;font-size:12px;font-weight:800;border-bottom:1px solid #eceef6;"><?= $h ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentJobs as $job): ?>
                                <tr>
                                    <td style="padding:11px 12px;border-bottom:1px solid #f1edf8;font-size:12px;">
                                        <span style="background:#f4f1ff;color:#7a8096;padding:3px 8px;border-radius:999px;font-weight:700;">JOB-<?= $job['id'] ?></span>
                                    </td>
                                    <td style="padding:11px 12px;border-bottom:1px solid #f1edf8;font-size:14px;font-weight:700;color:#17172b;"><?= htmlspecialchars($job['title']) ?></td>
                                    <td style="padding:11px 12px;border-bottom:1px solid #f1edf8;font-size:13px;color:#4d566f;"><?= htmlspecialchars($job['period_name'] ?? '—') ?></td>
                                    <td style="padding:11px 12px;border-bottom:1px solid #f1edf8;font-size:13px;color:#4d566f;"><?= htmlspecialchars($job['deadline']) ?></td>
                                    <td style="padding:11px 12px;border-bottom:1px solid #f1edf8;font-size:13px;color:#4d566f;"><?= $job['slots'] ?></td>
                                    <td style="padding:11px 12px;border-bottom:1px solid #f1edf8;">
                                        <span style="padding:4px 10px;border-radius:999px;font-size:12px;font-weight:700;background:<?= $job['status']==='open' ? '#d8f2df' : '#f1edff' ?>;color:<?= $job['status']==='open' ? '#187a3d' : '#6157aa' ?>;">
                                            <?= ucfirst($job['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- Documents -->
                <?php if (!empty($docs)): ?>
                <div class="admin-panel" style="padding:22px 26px;">
                    <h3 style="margin:0 0 16px;font-size:18px;font-weight:800;color:#161b34;">Uploaded Documents</h3>
                    <div style="display:grid;gap:10px;">
                        <?php foreach ($docs as $doc): ?>
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:#f8f9fc;border:1px solid #eceef6;border-radius:14px;">
                            <div>
                                <div style="font-size:14px;font-weight:700;color:#17172b;">📄 <?= htmlspecialchars($doc['doc_name']) ?></div>
                                <div style="font-size:12px;color:#7f8496;margin-top:3px;"><?= htmlspecialchars($doc['doc_type']) ?> &middot; <?= date('M d, Y', strtotime($doc['uploaded_at'])) ?></div>
                            </div>
                            <a href="/Uniworksmohinhhoa/<?= htmlspecialchars($doc['doc_url']) ?>" target="_blank"
                               style="padding:7px 14px;border-radius:10px;background:#cfc6f6;color:#17172b;font-size:13px;font-weight:700;text-decoration:none;">View</a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right -->
            <div style="display:grid;gap:20px;">

                <!-- Stats -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div style="background:#f0d86b;border-radius:20px;padding:18px;">
                        <div style="font-size:13px;color:#5a4b00;font-weight:700;margin-bottom:6px;">Jobs Posted</div>
                        <div style="font-size:34px;font-weight:800;color:#17172b;"><?= $totalJobs ?></div>
                    </div>
                    <div style="background:#cfc6f6;border-radius:20px;padding:18px;">
                        <div style="font-size:13px;color:#3d2f7a;font-weight:700;margin-bottom:6px;">Applications</div>
                        <div style="font-size:34px;font-weight:800;color:#17172b;"><?= $totalApps ?></div>
                    </div>
                    <div style="background:#d8f2df;border-radius:20px;padding:18px;">
                        <div style="font-size:13px;color:#145e30;font-weight:700;margin-bottom:6px;">Active Interns</div>
                        <div style="font-size:34px;font-weight:800;color:#17172b;"><?= $activeInterns ?></div>
                    </div>
                    <div style="background:#f4f1ff;border-radius:20px;padding:18px;">
                        <div style="font-size:13px;color:#5e57a8;font-weight:700;margin-bottom:6px;">Documents</div>
                        <div style="font-size:34px;font-weight:800;color:#17172b;"><?= count($docs) ?></div>
                    </div>
                </div>

                <!-- Quick actions -->
                <div class="admin-panel" style="padding:22px;">
                    <h3 style="margin:0 0 14px;font-size:16px;font-weight:800;color:#161b34;">Quick Actions</h3>
                    <div style="display:grid;gap:10px;">
                        <a href="/Uniworksmohinhhoa/admin/edit_user.php?id=<?= $company['user_id'] ?>"
                           style="display:flex;align-items:center;padding:12px 16px;border-radius:14px;background:#f6f2ff;color:#5e6680;font-size:14px;font-weight:700;text-decoration:none;">
                            ✏️ &nbsp;Edit Account
                        </a>
                        <a href="/Uniworksmohinhhoa/admin/company_approvals.php"
                           style="display:flex;align-items:center;padding:12px 16px;border-radius:14px;background:#fff8e1;color:#7a5200;font-size:14px;font-weight:700;text-decoration:none;">
                            🏢 &nbsp;Manage Approval Status
                        </a>
                        <a href="/Uniworksmohinhhoa/actions/admin/delete_user_action.php?id=<?= $company['user_id'] ?>"
                           onclick="return confirm('Delete this company account?')"
                           style="display:flex;align-items:center;padding:12px 16px;border-radius:14px;background:#ffe1e1;color:#ad3e3e;font-size:14px;font-weight:700;text-decoration:none;">
                            🗑️ &nbsp;Delete Account
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>
