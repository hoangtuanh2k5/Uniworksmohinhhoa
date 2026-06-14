<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireCompanyComplete($pdo);

$user = currentUser();
$keyword = trim($_GET['keyword'] ?? '');

/*
|-------------------------------------------------------
| Lấy company hiện tại
|-------------------------------------------------------
*/
$stmt = $pdo->prepare("SELECT id, company_name FROM companies WHERE user_id = ?");
$stmt->execute([$user['id']]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company) {
    setFlash('error', 'Please complete company profile first.');
    redirect('/Uniworksmohinhhoa/company/profile.php?setup=1');
}

/*
|-------------------------------------------------------
| Query applicants của company hiện tại
|-------------------------------------------------------
*/
$sql = "
    SELECT 
        a.id,
        a.student_id,
        a.job_id,
        a.cv_url,
        a.status,
        a.admin_approved,
        a.company_approved,
        u.id AS student_user_id,
        u.full_name AS student_name,
        s.student_code,
        s.class_name,
        s.gpa,
        j.title AS job_title,
        ir.id AS registration_id,
        ir.start_date,
        ir.end_date,
        ir.status AS internship_status
    FROM applications a
    INNER JOIN students s ON a.student_id = s.id
    INNER JOIN users u ON s.user_id = u.id
    INNER JOIN jobs j ON a.job_id = j.id
    LEFT JOIN internship_registrations ir ON ir.application_id = a.id
    WHERE j.company_id = ?
";

$params = [$company['id']];

if ($keyword !== '') {
    $sql .= "
        AND (
            u.full_name LIKE ?
            OR j.title LIKE ?
            OR s.student_code LIKE ?
        )
    ";
    $searchValue = '%' . $keyword . '%';
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
}

$sql .= " ORDER BY a.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

function renderApplicationStatus(array $app): string {
    if ((int)$app['admin_approved'] === 0) {
        return 'Waiting for school approval';
    }

    if ((int)$app['admin_approved'] === -1) {
        return 'Rejected by school';
    }

    if ((int)$app['admin_approved'] === 1 && (int)$app['company_approved'] === 0) {
        return 'Waiting for company decision';
    }

    if ((int)$app['company_approved'] === 1) {
        return 'Accepted by company';
    }

    if ((int)$app['company_approved'] === -1) {
        return 'Rejected by company';
    }

    return ucfirst(str_replace('_', ' ', $app['status'] ?? 'pending'));
}

function renderApplicationBadgeClass(array $app): string {
    if ((int)$app['admin_approved'] === 0) {
        return 'pending';
    }

    if ((int)$app['admin_approved'] === -1) {
        return 'rejected';
    }

    if ((int)$app['admin_approved'] === 1 && (int)$app['company_approved'] === 0) {
        return 'reviewing';
    }

    if ((int)$app['company_approved'] === 1) {
        return 'accepted';
    }

    if ((int)$app['company_approved'] === -1) {
        return 'rejected';
    }

    return 'default';
}

function renderInternshipBadgeClass(?string $status): string {
    $status = strtolower((string)$status);

    if ($status === 'ongoing') {
        return 'ongoing';
    }

    if ($status === 'completed') {
        return 'completed';
    }

    return 'default';
}

define('NOTIF_PAGE', 'reports');
require_once '../includes/notifications.php';
include '../includes/header.php';
?>

<style>
.company-page-header{
    margin-bottom: 24px;
}
.company-page-header h1{
    margin: 0 0 8px;
    font-size: 44px;
    line-height: 1.1;
    color: #1f2233;
    font-weight: 800;
}
.company-page-header p{
    margin: 0;
    font-size: 18px;
    color: #7a8096;
}

.company-search-card,
.company-table-card{
    background:#fff;
    border-radius:28px;
    padding:24px;
    border:1px solid #eeebf8;
    box-shadow:0 10px 28px rgba(31,34,51,.05);
}

.company-search-card{
    margin-bottom:24px;
}

.company-search-card h3{
    margin:0 0 16px;
    font-size:20px;
    color:#1f2233;
}

.company-search-form{
    display:flex;
    gap:14px;
    flex-wrap:wrap;
}

.company-search-input{
    flex:1;
    min-width:240px;
    height:56px;
    border:1px solid #ddd9ef;
    border-radius:18px;
    padding:0 18px;
    font-size:16px;
    outline:none;
    background:#faf9ff;
}

.company-search-input:focus{
    border-color:#cdbdff;
    background:#fff;
}

.company-search-btn,
.company-clear-btn,
.company-action-btn,
.company-link-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
    border:none;
    cursor:pointer;
    border-radius:16px;
    padding:12px 18px;
    font-weight:700;
    font-size:14px;
    transition:.2s ease;
}

.company-search-btn{
    background:#cfc0ff;
    color:#1f2233;
}

.company-search-btn:hover{
    background:#c3b1ff;
}

.company-clear-btn{
    background:#f4f1ff;
    color:#5d647a;
}

.company-clear-btn:hover{
    background:#ece6ff;
}

.company-table-wrap{
    overflow-x:auto;
}

.company-table{
    width:100%;
    border-collapse:collapse;
    min-width:1320px;
}

.company-table th{
    text-align:left;
    padding:16px 14px;
    color:#737b94;
    font-size:14px;
    font-weight:800;
    border-bottom:1px solid #ece9f7;
}

.company-table td{
    padding:16px 14px;
    border-bottom:1px solid #f0edf8;
    vertical-align:middle;
    color:#1f2233;
    font-size:15px;
}

.company-table tr:last-child td{
    border-bottom:none;
}

.company-status-badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:8px 12px;
    border-radius:999px;
    font-size:13px;
    font-weight:700;
    white-space:nowrap;
}

.company-status-badge.pending{
    background:#f6e8a6;
    color:#a36a00;
}

.company-status-badge.accepted{
    background:#d8f2df;
    color:#187a3d;
}

.company-status-badge.reviewing{
    background:#dce7ff;
    color:#265ad9;
}

.company-status-badge.rejected{
    background:#ffe1e1;
    color:#ad3e3e;
}

.company-status-badge.default{
    background:#f1edff;
    color:#6157aa;
}

.company-internship-badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:8px 12px;
    border-radius:999px;
    font-size:13px;
    font-weight:700;
    white-space:nowrap;
}

.company-internship-badge.ongoing{
    background:#dce7ff;
    color:#265ad9;
}

.company-internship-badge.completed{
    background:#d8f2df;
    color:#187a3d;
}

.company-internship-badge.default{
    background:#f1edff;
    color:#6157aa;
}

.company-action-group{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
}

.company-link-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
    border:none;
    cursor:pointer;
    border-radius:14px;
    padding:10px 16px;
    font-weight:700;
    font-size:14px;
    transition:.2s ease;
    white-space:nowrap;
}

.company-link-btn:hover{
    transform:translateY(-1px);
}

.company-link-btn.cv{
    background:#f0d86a;
    color:#1f2233;
}

.company-link-btn.cv:hover{
    background:#e7cf5d;
}

.company-link-btn.profile{
    background:#cfc0ff;
    color:#1f2233;
}

.company-link-btn.profile:hover{
    background:#c1afff;
}

.company-action-btn.accept{
    background:#dff7e8;
    color:#156b39;
}

.company-action-btn.accept:hover{
    background:#d1f1dc;
}

.company-action-btn.reject{
    background:#ffe1e1;
    color:#a33a3a;
}

.company-action-btn.reject:hover{
    background:#ffd3d3;
}

.company-empty{
    text-align:center;
    padding:40px 20px;
    color:#6e7690;
}

.company-section-title{
    margin:0 0 18px;
    font-size:22px;
    color:#1f2233;
    font-weight:800;
}

.company-date{
    color:#4f5770;
    font-weight:600;
}

.company-muted{
    color:#8c93aa;
    font-size:14px;
    font-weight:600;
}

@media (max-width: 768px){
    .company-page-header h1{
        font-size:34px;
    }

    .company-search-btn,
    .company-clear-btn{
        width:100%;
    }
}
</style>

<div class="company-shell">
    <aside class="company-sidebar">
        <div>
            <div class="company-brand">
                <div class="company-brand__logo">✦</div>
                <div class="company-brand__text">
                    <h3><?= htmlspecialchars($company['company_name']) ?></h3>
                    <p>Recruiter Portal</p>
                </div>
            </div>

            <nav class="company-nav">
                <a href="/Uniworksmohinhhoa/company/dashboard.php">Dashboard</a>
                <a href="/Uniworksmohinhhoa/company/applications.php" class="active">Applicants</a>
                <a href="/Uniworksmohinhhoa/company/manage_job.php">Jobs</a>
                <a href="/Uniworksmohinhhoa/company/messages.php">Messages<?php if(!empty($notif['messages']) && $notif['messages']>0): ?><span class="notif-badge"><?= $notif['messages'] ?></span><?php endif; ?></a>
                <a href="/Uniworksmohinhhoa/company/profile.php">Profile</a>
            </nav>
        </div>

        <div class="company-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">↩ Logout</a>
        </div>
    </aside>

    <main class="company-main">
        <div class="company-page-header">
            <h1>Student Applicants</h1>
            <p>Review students who applied to your internship posts.</p>
        </div>

        <?php if ($flash = getFlash()): ?>
            <div class="flash <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom: 18px;">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <div class="company-search-card">
            <h3>Search Applicants</h3>
            <form method="GET" action="/Uniworksmohinhhoa/company/applications.php" class="company-search-form">
                <input
                    type="text"
                    name="keyword"
                    class="company-search-input"
                    placeholder="Search by name, job, student code..."
                    value="<?= htmlspecialchars($keyword) ?>"
                >
                <button type="submit" class="company-search-btn">Search</button>

                <?php if ($keyword !== ''): ?>
                    <a href="/Uniworksmohinhhoa/company/applications.php" class="company-clear-btn">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="company-table-card">
            <h3 class="company-section-title">Applicants List</h3>

            <?php if (!empty($applications)): ?>
                <div class="company-table-wrap">
                    <table class="company-table">
                        <thead>
                            <tr>
                                <th>Applicant</th>
                                <th>Job</th>
                                <th>GPA</th>
                                <th>Application Status</th>
                                <th>CV</th>
                                <th>Internship Start</th>
                                <th>Internship End</th>
                                <th>Duration</th>
                                <th>Internship Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td><?= htmlspecialchars($app['student_name']) ?></td>
                                    <td><?= htmlspecialchars($app['job_title']) ?></td>
                                    <td><?= htmlspecialchars(number_format((float)$app['gpa'], 2)) ?></td>
                                    <td>
                                        <span class="company-status-badge <?= renderApplicationBadgeClass($app) ?>">
                                            <?= htmlspecialchars(renderApplicationStatus($app)) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($app['cv_url'])): ?>
                                            <a 
                                                href="/Uniworksmohinhhoa/<?= htmlspecialchars($app['cv_url']) ?>" 
                                                target="_blank" 
                                                class="company-link-btn cv"
                                            >
                                                View CV
                                            </a>
                                        <?php else: ?>
                                            <span class="company-muted">No CV</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($app['start_date'])): ?>
                                            <span class="company-date"><?= htmlspecialchars($app['start_date']) ?></span>
                                        <?php else: ?>
                                            <span class="company-muted">Not started</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($app['end_date'])): ?>
                                            <span class="company-date"><?= htmlspecialchars($app['end_date']) ?></span>
                                        <?php else: ?>
                                            <span class="company-muted">Not set</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($app['registration_id'])): ?>
                                            <span class="company-date">3 months</span>
                                        <?php else: ?>
                                            <span class="company-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($app['internship_status'])): ?>
                                            <span class="company-internship-badge <?= renderInternshipBadgeClass($app['internship_status']) ?>">
                                                <?= htmlspecialchars(ucfirst($app['internship_status'])) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="company-muted">Not started</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="company-action-group">
    <a 
        href="/Uniworksmohinhhoa/company/candidate_detail.php?id=<?= $app['id'] ?>" 
        class="company-link-btn profile"
    >
        View Profile
    </a>

    <a 
        href="/Uniworksmohinhhoa/company/messages.php?receiver_id=<?= htmlspecialchars($app['student_user_id'] ?? '') ?>" 
        class="company-link-btn" style="background:#d8f2df;color:#187a3d;"
    >
        💬 Message
    </a>

    <?php if (!empty($app['registration_id']) && $app['internship_status'] === 'completed'): ?>
        <a 
            href="/Uniworksmohinhhoa/company/evaluate.php?id=<?= $app['registration_id'] ?>" 
            class="company-link-btn profile"
        >
            Evaluate
        </a>
    <?php endif; ?>

    <?php if ((int)$app['admin_approved'] === 1 && (int)$app['company_approved'] === 0): ?>
        <form action="/Uniworksmohinhhoa/actions/company/review_application_action.php" method="POST" style="display:inline;">
            <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
            <input type="hidden" name="decision" value="accept">
            <button type="submit" class="company-action-btn accept">Accept</button>
        </form>

        <form action="/Uniworksmohinhhoa/actions/company/review_application_action.php" method="POST" style="display:inline;">
            <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
            <input type="hidden" name="decision" value="reject">
            <button type="submit" class="company-action-btn reject">Reject</button>
        </form>
    <?php endif; ?>
</div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="company-empty">
                    No applicants found.
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>