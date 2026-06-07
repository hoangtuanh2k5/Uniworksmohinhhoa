<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('company');

$user = currentUser();
$application_id = (int)($_GET['id'] ?? 0);

if ($application_id <= 0) {
    setFlash('error', 'Invalid application.');
    redirect('/Uniworksmohinhhoa/company/applications.php');
}

$stmt = $pdo->prepare("SELECT id, company_name FROM companies WHERE user_id = ?");
$stmt->execute([$user['id']]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company) {
    setFlash('error', 'Company profile not found.');
    redirect('/Uniworksmohinhhoa/company/profile.php?setup=1');
}

$stmt = $pdo->prepare("
    SELECT 
        a.id AS application_id,
        a.cv_url,
        a.status,
        a.admin_approved,
        a.company_approved,
        u.full_name,
        u.email,
        u.phone,
        s.id AS student_id,
        s.student_code,
        s.class_name,
        s.gpa,
        j.title AS job_title
    FROM applications a
    INNER JOIN students s ON a.student_id = s.id
    INNER JOIN users u ON s.user_id = u.id
    INNER JOIN jobs j ON a.job_id = j.id
    WHERE a.id = ?
      AND j.company_id = ?
");
$stmt->execute([$application_id, $company['id']]);
$app = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$app) {
    setFlash('error', 'Application not found or access denied.');
    redirect('/Uniworksmohinhhoa/company/applications.php');
}

function companyRenderStatus(array $app): string {
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

$statusText = companyRenderStatus($app);
$avatarLetter = strtoupper(mb_substr($app['full_name'], 0, 1));
$cvLink = !empty($app['cv_url']) ? '/Uniworksmohinhhoa/' . ltrim($app['cv_url'], '/') : '';
include '../includes/header.php';
?>

<style>
.company-candidate-wrap{
    display:grid;
    grid-template-columns: 1.45fr .95fr;
    gap:24px;
    align-items:start;
}

.company-card{
    background:#fff;
    border:1px solid #eee9f8;
    border-radius:28px;
    padding:28px;
    box-shadow:0 12px 30px rgba(31,34,51,.05);
}

.company-profile-hero{
    display:flex;
    gap:20px;
    align-items:flex-start;
}

.company-avatar{
    width:84px;
    height:84px;
    border-radius:24px;
    background:linear-gradient(135deg, #cfc0ff, #f2df86);
    color:#1f2233;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:32px;
    font-weight:800;
    flex-shrink:0;
    box-shadow:0 10px 20px rgba(207,192,255,.28);
}

.company-profile-main{
    flex:1;
}

.company-profile-main h1{
    margin:0 0 8px;
    font-size:38px;
    line-height:1.1;
    color:#1f2233;
    font-weight:800;
}

.company-profile-sub{
    margin:0 0 14px;
    font-size:18px;
    color:#6e7690;
}

.company-status-pill{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:10px 14px;
    border-radius:999px;
    background:#f4f0ff;
    color:#6157aa;
    font-size:14px;
    font-weight:700;
}

.company-profile-grid{
    display:grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap:16px;
    margin-top:26px;
}

.company-info-box{
    background:#faf9ff;
    border:1px solid #f0ebfb;
    border-radius:20px;
    padding:18px;
}

.company-info-box span{
    display:block;
    margin-bottom:6px;
    font-size:13px;
    font-weight:700;
    letter-spacing:.03em;
    text-transform:uppercase;
    color:#8c93aa;
}

.company-info-box strong{
    color:#1f2233;
    font-size:18px;
    line-height:1.45;
}

.company-side-stack{
    display:flex;
    flex-direction:column;
    gap:24px;
}

.company-side-title{
    margin:0 0 14px;
    font-size:22px;
    color:#1f2233;
    font-weight:800;
}

.company-side-text{
    margin:0;
    color:#6d748d;
    line-height:1.7;
    font-size:16px;
}

.company-btn-row{
    display:flex;
    flex-wrap:wrap;
    gap:12px;
    margin-top:20px;
}

.company-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:145px;
    height:48px;
    padding:0 18px;
    border:none;
    border-radius:16px;
    text-decoration:none;
    font-weight:800;
    font-size:15px;
    cursor:pointer;
    transition:.2s ease;
}

.company-btn:hover{
    transform:translateY(-1px);
}

.company-btn-yellow{
    background:#efd867;
    color:#1f2233;
}

.company-btn-yellow:hover{
    background:#e7cf5c;
}

.company-btn-purple{
    background:#cfc0ff;
    color:#1f2233;
}

.company-btn-purple:hover{
    background:#c3b2ff;
}

.company-btn-red{
    background:#ffdede;
    color:#a34141;
}

.company-btn-red:hover{
    background:#ffd0d0;
}

.company-btn-ghost{
    background:#f6f2ff;
    color:#5c647d;
}

.company-btn-ghost:hover{
    background:#eee7ff;
}

.company-review-note{
    background:linear-gradient(135deg, #faf8ff, #fffaf0);
    border:1px solid #eee5ff;
    border-radius:22px;
    padding:18px;
    color:#676f88;
    line-height:1.7;
}

.company-highlight-box{
    background:linear-gradient(135deg, #f6f1ff 0%, #fff9df 100%);
    border:1px solid #eee4ff;
    border-radius:24px;
    padding:22px;
}

.company-highlight-box h3{
    margin:0 0 10px;
    font-size:24px;
    color:#1f2233;
    font-weight:800;
}

.company-highlight-box p{
    margin:0;
    color:#68708a;
    line-height:1.7;
}

.company-mini-list{
    display:grid;
    gap:14px;
}

.company-mini-item{
    display:flex;
    justify-content:space-between;
    gap:16px;
    padding:14px 0;
    border-bottom:1px solid #f1ecfa;
    font-size:15px;
}

.company-mini-item:last-child{
    border-bottom:none;
    padding-bottom:0;
}

.company-mini-item span{
    color:#8c93aa;
    font-weight:600;
}

.company-mini-item strong{
    color:#1f2233;
    text-align:right;
}

@media (max-width: 1100px){
    .company-candidate-wrap{
        grid-template-columns:1fr;
    }
}

@media (max-width: 768px){
    .company-profile-hero{
        flex-direction:column;
    }

    .company-profile-main h1{
        font-size:30px;
    }

    .company-profile-grid{
        grid-template-columns:1fr;
    }

    .company-card{
        padding:22px;
        border-radius:22px;
    }

    .company-btn{
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
                <a href="/Uniworksmohinhhoa/company/manage_jobs.php">Jobs</a>
                <a href="/Uniworksmohinhhoa/company/messages.php">Messages</a>
                <a href="/Uniworksmohinhhoa/company/profile.php">Profile</a>
            </nav>
        </div>

        <div class="company-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">↩ Logout</a>
        </div>
    </aside>

    <main class="company-main">
        <div class="company-candidate-wrap">
            <section class="company-card">
                <div class="company-profile-hero">
                    <div class="company-avatar"><?= htmlspecialchars($avatarLetter) ?></div>

                    <div class="company-profile-main">
                        <h1><?= htmlspecialchars($app['full_name']) ?></h1>
                        <p class="company-profile-sub">
                            Applied for <strong><?= htmlspecialchars($app['job_title']) ?></strong>
                        </p>
                        <span class="company-status-pill"><?= htmlspecialchars($statusText) ?></span>

                        <div class="company-btn-row">
                            <a href="/Uniworksmohinhhoa/company/applications.php" class="company-btn company-btn-ghost">
                                Back
                            </a>

                            <?php if (!empty($cvLink)): ?>
                                <a href="<?= htmlspecialchars($cvLink) ?>" target="_blank" class="company-btn company-btn-yellow">
                                    Open CV
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="company-profile-grid">
                    <div class="company-info-box">
                        <span>Email</span>
                        <strong><?= htmlspecialchars($app['email'] ?: 'Updating') ?></strong>
                    </div>

                    <div class="company-info-box">
                        <span>Phone</span>
                        <strong><?= htmlspecialchars($app['phone'] ?: 'Updating') ?></strong>
                    </div>

                    <div class="company-info-box">
                        <span>Student Code</span>
                        <strong><?= htmlspecialchars($app['student_code'] ?: 'Updating') ?></strong>
                    </div>

                    <div class="company-info-box">
                        <span>Class</span>
                        <strong><?= htmlspecialchars($app['class_name'] ?: 'Updating') ?></strong>
                    </div>

                    <div class="company-info-box">
                        <span>GPA</span>
                        <strong><?= htmlspecialchars(number_format((float)$app['gpa'], 2)) ?></strong>
                    </div>

                    <div class="company-info-box">
                        <span>Current Status</span>
                        <strong><?= htmlspecialchars($statusText) ?></strong>
                    </div>
                </div>
            </section>

            <aside class="company-side-stack">
                <div class="company-highlight-box">
                    <h3>Application Review</h3>
                    <p>
                        Review this candidate based on academic profile, CV, and application status before making your decision.
                    </p>
                </div>

                <div class="company-card">
                    <h3 class="company-side-title">Quick Summary</h3>

                    <div class="company-mini-list">
                        <div class="company-mini-item">
                            <span>Applied Position</span>
                            <strong><?= htmlspecialchars($app['job_title']) ?></strong>
                        </div>
                        <div class="company-mini-item">
                            <span>Student</span>
                            <strong><?= htmlspecialchars($app['full_name']) ?></strong>
                        </div>
                        <div class="company-mini-item">
                            <span>Code</span>
                            <strong><?= htmlspecialchars($app['student_code'] ?: 'Updating') ?></strong>
                        </div>
                        <div class="company-mini-item">
                            <span>GPA</span>
                            <strong><?= htmlspecialchars(number_format((float)$app['gpa'], 2)) ?></strong>
                        </div>
                    </div>
                </div>

                <div class="company-card">
                    <h3 class="company-side-title">Review Application</h3>

                    <?php if ((int)$app['admin_approved'] === 0): ?>
                        <div class="company-review-note">
                            This application is still waiting for school approval, so your company cannot make a decision yet.
                        </div>
                    <?php elseif ((int)$app['admin_approved'] === -1): ?>
                        <div class="company-review-note">
                            This application was rejected by the school. No further company action is required.
                        </div>
                    <?php elseif ((int)$app['company_approved'] === 1): ?>
                        <div class="company-review-note">
                            Your company has already accepted this candidate.
                        </div>
                    <?php elseif ((int)$app['company_approved'] === -1): ?>
                        <div class="company-review-note">
                            Your company has already rejected this candidate.
                        </div>
                    <?php else: ?>
                        <div class="company-review-note" style="margin-bottom:16px;">
                            This application has been approved by the school. You can now decide whether to accept or reject the candidate.
                        </div>

                        <div class="company-btn-row">
                            <form action="/Uniworksmohinhhoa/actions/company/review_application_action.php" method="POST" style="display:inline;">
                                <input type="hidden" name="application_id" value="<?= $app['application_id'] ?>">
                                <input type="hidden" name="decision" value="accept">
                                <button type="submit" class="company-btn company-btn-purple">Accept</button>
                            </form>

                            <form action="/Uniworksmohinhhoa/actions/company/review_application_action.php" method="POST" style="display:inline;">
                                <input type="hidden" name="application_id" value="<?= $app['application_id'] ?>">
                                <input type="hidden" name="decision" value="reject">
                                <button type="submit" class="company-btn company-btn-red">Reject</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>