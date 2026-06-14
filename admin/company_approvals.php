<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('admin');

$flash = getFlash();

// Đếm pending để badge
$pendingCount = (int)$pdo->query("SELECT COUNT(*) FROM companies WHERE status = 'pending'")->fetchColumn();

// Filter
$filterStatus = $_GET['status'] ?? 'pending';
$allowed = ['pending', 'approved', 'rejected', 'suspended', 'all'];
if (!in_array($filterStatus, $allowed)) $filterStatus = 'pending';

$sql = "
    SELECT c.*, u.full_name, u.email, u.phone, u.created_at AS registered_at
    FROM companies c
    INNER JOIN users u ON c.user_id = u.id
";
if ($filterStatus !== 'all') {
    $sql .= " WHERE c.status = " . $pdo->quote($filterStatus);
}
$sql .= " ORDER BY c.id DESC";

$companies = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="admin-shell">
    <aside class="admin-sidebar">
        <div>
            <div class="admin-brand"><h2>Admin Panel</h2></div>
            <nav class="admin-nav">
                <a href="/Uniworksmohinhhoa/admin/dashboard.php">Dashboard</a>
                <a href="/Uniworksmohinhhoa/admin/users.php">Users</a>
                <a href="/Uniworksmohinhhoa/admin/company_approvals.php" class="active">
                    Companies<?php if ($pendingCount > 0): ?>
                        <span style="display:inline-flex;align-items:center;justify-content:center;min-width:18px;height:18px;border-radius:999px;background:#efd867;color:#1f2233;font-size:10px;font-weight:800;margin-left:6px;padding:0 4px;"><?= $pendingCount ?></span>
                    <?php endif; ?>
                </a>
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
        <div class="admin-topbar">
            <div>
                <h1>Company Approvals</h1>
                <p>Review and approve or reject company registration requests.</p>
            </div>
        </div>

        <?php if ($flash): ?>
            <div class="flash <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:18px;">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <!-- Stats -->
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-bottom:24px;">
            <?php
            $counts = $pdo->query("SELECT status, COUNT(*) as n FROM companies GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
            ?>
            <div class="admin-stat-card yellow" style="min-height:auto;padding:18px 22px;">
                <p class="admin-stat-label" style="margin-bottom:6px;">Pending</p>
                <h3 class="admin-stat-value" style="font-size:36px;"><?= (int)($counts['pending'] ?? 0) ?></h3>
            </div>
            <div class="admin-stat-card purple" style="min-height:auto;padding:18px 22px;">
                <p class="admin-stat-label" style="margin-bottom:6px;">Approved</p>
                <h3 class="admin-stat-value" style="font-size:36px;"><?= (int)($counts['approved'] ?? 0) ?></h3>
            </div>
            <div style="background:#ffe1e1;border-radius:22px;padding:18px 22px;">
                <p style="margin:0 0 6px;color:#7a3a3a;font-size:15px;font-weight:600;">Rejected</p>
                <h3 style="margin:0;font-size:36px;font-weight:800;color:#11162d;"><?= (int)($counts['rejected'] ?? 0) ?></h3>
            </div>
            <div style="background:#fff3e0;border-radius:22px;padding:18px 22px;">
                <p style="margin:0 0 6px;color:#7a4f00;font-size:15px;font-weight:600;">Suspended</p>
                <h3 style="margin:0;font-size:36px;font-weight:800;color:#11162d;"><?= (int)($counts['suspended'] ?? 0) ?></h3>
            </div>
        </div>

        <!-- Filter tabs -->
        <div style="display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap;">
            <?php foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'suspended' => 'Suspended', 'all' => 'All'] as $val => $label): ?>
                <a href="?status=<?= $val ?>"
                   style="padding:10px 18px;border-radius:12px;font-size:13px;font-weight:700;text-decoration:none;
                          background:<?= $filterStatus === $val ? '#cfc6ff' : '#f6f2ff' ?>;
                          color:<?= $filterStatus === $val ? '#1f2233' : '#6259aa' ?>;">
                    <?= $label ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Table -->
        <div class="admin-panel" style="padding:0;overflow:hidden;">
            <div style="padding:22px 28px 16px;border-bottom:1px solid #eceef6;">
                <h2 style="margin:0;font-size:20px;font-weight:800;color:#161b34;">
                    <?= ucfirst($filterStatus) ?> Companies
                    <span style="font-size:14px;font-weight:600;color:#7a8198;margin-left:8px;">(<?= count($companies) ?>)</span>
                </h2>
            </div>

            <?php if (empty($companies)): ?>
                <div style="padding:40px;text-align:center;color:#7a8198;">No companies found.</div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;min-width:900px;">
                        <thead>
                            <tr>
                                <?php foreach (['ID', 'Company', 'Contact', 'Tax Code', 'Phone', 'Registered', 'Status', 'Action'] as $h): ?>
                                    <th style="text-align:left;padding:14px 22px;background:#faf8ff;color:#74809b;font-size:13px;font-weight:800;border-bottom:1px solid #eceef6;"><?= $h ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($companies as $c): ?>
                                <tr>
                                    <td style="padding:16px 22px;border-bottom:1px solid #f1edf8;vertical-align:middle;">
                                        <span style="font-size:12px;font-weight:700;color:#7a8096;background:#f4f1ff;padding:4px 10px;border-radius:999px;white-space:nowrap;">
                                            CO-<?= $c['id'] ?>
                                        </span>
                                    </td>
                                    <td style="padding:16px 22px;border-bottom:1px solid #f1edf8;vertical-align:middle;">
                                        <div style="font-weight:800;color:#17172b;font-size:15px;"><?= htmlspecialchars($c['company_name']) ?></div>
                                        <?php if ($c['industry_type']): ?>
                                            <div style="font-size:12px;color:#8a8fa3;margin-top:2px;"><?= htmlspecialchars($c['industry_type']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:16px 22px;border-bottom:1px solid #f1edf8;vertical-align:middle;">
                                        <div style="font-weight:700;color:#1f2233;"><?= htmlspecialchars($c['full_name']) ?></div>
                                        <div style="font-size:12px;color:#7a8096;margin-top:2px;"><?= htmlspecialchars($c['email']) ?></div>
                                    </td>
                                    <td style="padding:16px 22px;border-bottom:1px solid #f1edf8;vertical-align:middle;font-size:14px;color:#4d566f;">
                                        <?= htmlspecialchars($c['tax_code']) ?>
                                    </td>
                                    <td style="padding:16px 22px;border-bottom:1px solid #f1edf8;vertical-align:middle;font-size:14px;color:#4d566f;">
                                        <?= htmlspecialchars($c['phone'] ?: '—') ?>
                                    </td>
                                    <td style="padding:16px 22px;border-bottom:1px solid #f1edf8;vertical-align:middle;font-size:13px;color:#7a8096;">
                                        <?= date('M d, Y', strtotime($c['registered_at'])) ?>
                                    </td>
                                    <td style="padding:16px 22px;border-bottom:1px solid #f1edf8;vertical-align:middle;">
                                        <?php
                                        $badgeStyle = match($c['status']) {
                                            'approved'  => 'background:#d8f2df;color:#187a3d;',
                                            'rejected'  => 'background:#ffe1e1;color:#ad3e3e;',
                                            'suspended' => 'background:#fff3e0;color:#b45309;',
                                            default     => 'background:#f6e8a6;color:#a36a00;',
                                        };
                                        ?>
                                        <span style="display:inline-flex;align-items:center;justify-content:center;padding:7px 12px;border-radius:999px;font-size:12px;font-weight:800;<?= $badgeStyle ?>">
                                            <?= ucfirst($c['status']) ?>
                                        </span>
                                    </td>
                                    <td style="padding:16px 22px;border-bottom:1px solid #f1edf8;vertical-align:middle;">
                                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                            <?php if ($c['status'] !== 'approved'): ?>
                                                <form action="/Uniworksmohinhhoa/actions/admin/approve_company_action.php" method="POST" style="display:inline;">
                                                    <input type="hidden" name="company_id" value="<?= $c['id'] ?>">
                                                    <input type="hidden" name="decision" value="approve">
                                                    <input type="hidden" name="back_status" value="<?= $filterStatus ?>">
                                                    <button type="submit" style="border:none;cursor:pointer;padding:8px 14px;border-radius:12px;background:#d8f2df;color:#187a3d;font-size:13px;font-weight:800;">
                                                        ✓ Approve
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if ($c['status'] !== 'rejected'): ?>
                                                <form action="/Uniworksmohinhhoa/actions/admin/approve_company_action.php" method="POST" style="display:inline;"
                                                      onsubmit="return confirm('Reject this company?')">
                                                    <input type="hidden" name="company_id" value="<?= $c['id'] ?>">
                                                    <input type="hidden" name="decision" value="reject">
                                                    <input type="hidden" name="back_status" value="<?= $filterStatus ?>">
                                                    <button type="submit" style="border:none;cursor:pointer;padding:8px 14px;border-radius:12px;background:#ffe1e1;color:#ad3e3e;font-size:13px;font-weight:800;">
                                                        ✕ Reject
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if ($c['status'] === 'approved'): ?>
                                                <form action="/Uniworksmohinhhoa/actions/admin/approve_company_action.php" method="POST" style="display:inline;"
                                                      onsubmit="return confirm('Suspend this company account? They will not be able to login.')">
                                                    <input type="hidden" name="company_id" value="<?= $c['id'] ?>">
                                                    <input type="hidden" name="decision" value="suspend">
                                                    <input type="hidden" name="back_status" value="<?= $filterStatus ?>">
                                                    <button type="submit" style="border:none;cursor:pointer;padding:8px 14px;border-radius:12px;background:#fff3e0;color:#b45309;font-size:13px;font-weight:800;">
                                                        ⏸ Suspend
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if ($c['status'] === 'suspended'): ?>
                                                <form action="/Uniworksmohinhhoa/actions/admin/approve_company_action.php" method="POST" style="display:inline;">
                                                    <input type="hidden" name="company_id" value="<?= $c['id'] ?>">
                                                    <input type="hidden" name="decision" value="unsuspend">
                                                    <input type="hidden" name="back_status" value="<?= $filterStatus ?>">
                                                    <button type="submit" style="border:none;cursor:pointer;padding:8px 14px;border-radius:12px;background:#d8f2df;color:#187a3d;font-size:13px;font-weight:800;">
                                                        ▶ Reactivate
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>
