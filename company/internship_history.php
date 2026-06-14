<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('company');

$user  = currentUser();
$flash = getFlash();

$stmt = $pdo->prepare("SELECT id, company_name FROM companies WHERE user_id = ?");
$stmt->execute([$user['id']]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company) {
    redirect('/Uniworksmohinhhoa/company/profile.php?setup=1');
}

// ── Filters ──────────────────────────────────────────────
$filterPeriod = (int)($_GET['period_id'] ?? 0);
$filterStatus = trim($_GET['status'] ?? '');
$filterSearch = trim($_GET['search'] ?? '');

// ── Stats tổng ───────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT
        COUNT(DISTINCT ir.id)                                          AS total_internships,
        COUNT(DISTINCT CASE WHEN ir.status = 'completed' THEN ir.id END) AS completed,
        COUNT(DISTINCT CASE WHEN ir.status = 'ongoing'   THEN ir.id END) AS ongoing,
        COUNT(DISTINCT CASE WHEN ir.status = 'cancelled' THEN ir.id END) AS cancelled
    FROM internship_registrations ir
    INNER JOIN applications a  ON ir.application_id = a.id
    INNER JOIN jobs j          ON a.job_id = j.id
    WHERE j.company_id = ?
");
$stmt->execute([$company['id']]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// ── Danh sách kỳ thực tập để filter ─────────────────────
$periods = $pdo->query("SELECT id, name FROM internship_periods ORDER BY start_date DESC")->fetchAll(PDO::FETCH_ASSOC);

// ── Danh sách lịch sử ────────────────────────────────────
$sql = "
    SELECT
        ir.id            AS reg_id,
        ir.start_date,
        ir.end_date,
        ir.status        AS reg_status,
        a.id             AS app_id,
        a.cv_url,
        u.full_name      AS student_name,
        u.email          AS student_email,
        s.student_code,
        m.name           AS major_name,
        j.title          AS job_title,
        ip.name          AS period_name,
        ip.id            AS period_id,
        ev.score,
        ev.feedback
    FROM internship_registrations ir
    INNER JOIN applications a   ON ir.application_id = a.id
    INNER JOIN jobs j           ON a.job_id = j.id
    INNER JOIN internship_periods ip ON j.period_id = ip.id
    INNER JOIN students s       ON a.student_id = s.id
    INNER JOIN users u          ON s.user_id = u.id
    LEFT JOIN  majors m         ON s.major_id = m.id
    LEFT JOIN  evaluations ev   ON ev.registration_id = ir.id AND ev.evaluator_role = 'company'
    WHERE j.company_id = ?
";

$params = [$company['id']];

if ($filterPeriod > 0) {
    $sql   .= " AND ip.id = ?";
    $params[] = $filterPeriod;
}
if ($filterStatus !== '') {
    $sql   .= " AND ir.status = ?";
    $params[] = $filterStatus;
}
if ($filterSearch !== '') {
    $sql   .= " AND (u.full_name LIKE ? OR s.student_code LIKE ? OR j.title LIKE ?)";
    $like   = '%' . $filterSearch . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY ir.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Helper badge ─────────────────────────────────────────
function regStatusBadge(string $s): string {
    return match($s) {
        'completed' => '<span class="company-badge approved">Completed</span>',
        'ongoing'   => '<span class="company-badge" style="background:#dce7ff;color:#265ad9;">Ongoing</span>',
        'cancelled' => '<span class="company-badge rejected">Cancelled</span>',
        default     => '<span class="company-badge pending">' . htmlspecialchars(ucfirst($s)) . '</span>',
    };
}

include '../includes/header.php';
?>

<div class="company-shell">
    <aside class="company-sidebar">
        <div>
            <div class="company-brand">
                <div class="company-brand__logo">
                                <?php if (!empty($user['avatar_url'])): ?>
                                    <img src="/Uniworksmohinhhoa/<?= htmlspecialchars($user['avatar_url']) ?>" alt="avatar" style="width:34px;height:34px;min-width:34px;min-height:34px;max-width:34px;max-height:34px;object-fit:cover;border-radius:10px;display:block;">
                                <?php else: ?>
                                    ✦
                                <?php endif; ?>
                            </div>
                <div class="company-brand__text">
                    <h3><?= htmlspecialchars($company['company_name']) ?></h3>
                    <p>Recruiter Portal</p>
                </div>
            </div>

            <nav class="company-nav">
                <a href="/Uniworksmohinhhoa/company/dashboard.php">Dashboard</a>
                <a href="/Uniworksmohinhhoa/company/applications.php">Applicants</a>
                <a href="/Uniworksmohinhhoa/company/manage_job.php">Jobs</a>
                <a href="/Uniworksmohinhhoa/company/internship_history.php" class="active">History</a>
                <a href="/Uniworksmohinhhoa/company/evaluations.php">Evaluations</a>
                <a href="/Uniworksmohinhhoa/company/messages.php">Messages</a>
                <a href="/Uniworksmohinhhoa/company/profile.php">Profile</a>
            </nav>
        </div>

        <div class="company-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">↩ Logout</a>
        </div>
    </aside>

    <main class="company-main">

        <!-- Topbar -->
        <div class="company-topbar">
            <div>
                <h1>Internship History</h1>
                <p>Full record of all internship sessions and participating students.</p>
            </div>
        </div>

        <?php if ($flash): ?>
            <div class="flash <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:18px;">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <!-- Stats -->
        <section class="company-stats" style="margin-bottom:22px;">
            <div class="company-stat-card purple">
                <h4>Total Internships</h4>
                <strong><?= (int)$stats['total_internships'] ?></strong>
            </div>
            <div class="company-stat-card yellow">
                <h4>Completed</h4>
                <strong><?= (int)$stats['completed'] ?></strong>
            </div>
            <div class="company-stat-card purple">
                <h4>Ongoing</h4>
                <strong><?= (int)$stats['ongoing'] ?></strong>
            </div>
            <div class="company-stat-card yellow">
                <h4>Cancelled</h4>
                <strong><?= (int)$stats['cancelled'] ?></strong>
            </div>
        </section>

        <!-- Filters -->
        <div class="company-card" style="margin-bottom:18px;">
            <form method="GET" action="" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:180px;">
                    <label style="font-size:12px;font-weight:700;color:#6f7689;display:block;margin-bottom:5px;">Search</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($filterSearch) ?>"
                           placeholder="Student name, code, or job title..."
                           class="company-form-control" style="height:40px;">
                </div>
                <div style="min-width:160px;">
                    <label style="font-size:12px;font-weight:700;color:#6f7689;display:block;margin-bottom:5px;">Period</label>
                    <select name="period_id" class="company-form-control" style="height:40px;">
                        <option value="0">All Periods</option>
                        <?php foreach ($periods as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $filterPeriod === (int)$p['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="min-width:140px;">
                    <label style="font-size:12px;font-weight:700;color:#6f7689;display:block;margin-bottom:5px;">Status</label>
                    <select name="status" class="company-form-control" style="height:40px;">
                        <option value="">All Status</option>
                        <option value="ongoing"   <?= $filterStatus === 'ongoing'   ? 'selected' : '' ?>>Ongoing</option>
                        <option value="completed" <?= $filterStatus === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $filterStatus === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="submit" class="company-btn" style="height:40px;">Filter</button>
                    <a href="internship_history.php" class="company-btn-outline" style="height:40px;display:inline-flex;align-items:center;padding:0 16px;">Reset</a>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="company-card">
            <div class="company-card__title">
                <h3>Internship Records</h3>
                <span class="company-muted"><?= count($history) ?> record(s) found</span>
            </div>

            <?php if (empty($history)): ?>
                <p class="company-muted" style="padding:20px 0;">No internship records found.</p>
            <?php else: ?>
                <table class="company-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Student Code</th>
                            <th>Major</th>
                            <th>Job Title</th>
                            <th>Period</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Score</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $i => $row): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <div style="font-weight:700;color:#17172b;"><?= htmlspecialchars($row['student_name']) ?></div>
                                    <div style="font-size:12px;color:#7f8496;"><?= htmlspecialchars($row['student_email']) ?></div>
                                </td>
                                <td><?= htmlspecialchars($row['student_code']) ?></td>
                                <td><?= htmlspecialchars($row['major_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['job_title']) ?></td>
                                <td><?= htmlspecialchars($row['period_name']) ?></td>
                                <td><?= $row['start_date'] ? date('M d, Y', strtotime($row['start_date'])) : '-' ?></td>
                                <td><?= $row['end_date']   ? date('M d, Y', strtotime($row['end_date']))   : '-' ?></td>
                                <td>
                                    <?php if ($row['score'] !== null): ?>
                                        <span style="font-weight:700;color:#17172b;"><?= number_format((float)$row['score'], 1) ?></span>
                                        <span style="font-size:12px;color:#7f8496;">/10</span>
                                    <?php else: ?>
                                        <span class="company-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= regStatusBadge($row['reg_status']) ?></td>
                            </tr>
                            <?php if (!empty($row['feedback'])): ?>
                            <tr style="background:#fafbff;">
                                <td></td>
                                <td colspan="9" style="font-size:13px;color:#545a6d;padding:8px 10px 14px;">
                                    <span style="font-weight:700;">Feedback:</span> <?= htmlspecialchars($row['feedback']) ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </main>
</div>

<?php include '../includes/footer.php'; ?>
