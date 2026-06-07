<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('student');

$user = currentUser();
$job_id = (int)($_GET['id'] ?? 0);

if ($job_id <= 0) {
    redirect('/Uniworksmohinhhoa/student/jobs.php');
}

$stmt = $pdo->prepare("
    SELECT 
        j.*,
        c.company_name,
        c.website,
        c.address,
        c.industry_type,
        ip.name AS period_name
    FROM jobs j
    INNER JOIN companies c ON j.company_id = c.id
    LEFT JOIN internship_periods ip ON j.period_id = ip.id
    WHERE j.id = ?
");
$stmt->execute([$job_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    setFlash('error', 'Job not found.');
    redirect('/Uniworksmohinhhoa/student/jobs.php');
}

include '../includes/header.php';
?>
<style>
.job-detail-wrap{
    display:grid;
    grid-template-columns: 2.2fr 0.9fr;
    gap:24px;
    align-items:start;
}
.job-hero-card,
.job-section-card,
.job-side-card{
    background:#fff;
    border-radius:24px;
    padding:28px;
    box-shadow:0 10px 30px rgba(31,41,55,.06);
    border:1px solid #efedf7;
}
.job-section-card,
.job-side-card{
    margin-top:20px;
}
.job-hero-top{
    display:grid;
    grid-template-columns:72px 1fr auto;
    gap:20px;
    align-items:start;
}
.job-hero-logo{
    width:72px;
    height:72px;
    border-radius:20px;
    background:linear-gradient(135deg, #6d5efc, #9f8cff);
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:28px;
    font-weight:700;
}
.job-breadcrumb{
    font-size:14px;
    color:#8a8fa3;
    margin-bottom:8px;
}
.job-hero-info h1{
    font-size:40px;
    line-height:1.15;
    margin:0 0 10px;
    color:#1f2233;
}
.job-subtitle{
    font-size:18px;
    color:#616781;
    margin:0 0 14px;
}
.job-meta-row{
    display:flex;
    flex-wrap:wrap;
    gap:10px;
}
.job-meta-row span{
    display:inline-flex;
    align-items:center;
    padding:8px 14px;
    border-radius:999px;
    background:#f5f2ff;
    color:#6d5efc;
    font-size:14px;
    font-weight:600;
}
.job-hero-actions{
    display:flex;
    gap:12px;
    align-items:center;
}
.student-btn,
.student-btn-outline{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:150px;
    height:48px;
    padding:0 20px;
    border-radius:16px;
    font-weight:700;
    text-decoration:none;
    transition:.2s ease;
}
.student-btn{
    background:#cfc0ff;
    color:#1f2233;
    border:none;
}
.student-btn-outline{
    background:#fff;
    color:#1f2233;
    border:1.5px solid #d9cffd;
}
.job-tabs{
    display:flex;
    gap:28px;
    margin-top:26px;
    padding-top:8px;
    border-top:1px solid #f0eef7;
}
.job-tabs span{
    position:relative;
    font-size:16px;
    font-weight:600;
    color:#7d8398;
    padding-bottom:10px;
}
.job-tabs span.active{
    color:#1f2233;
}
.job-tabs span.active::after{
    content:"";
    position:absolute;
    left:0;
    bottom:0;
    width:100%;
    height:3px;
    border-radius:999px;
    background:#1f2233;
}
.job-section-card h3,
.job-side-card h4{
    margin:0 0 16px;
    font-size:28px;
    color:#1f2233;
}
.job-section-card p{
    margin:0;
    color:#5d647a;
    line-height:1.85;
    font-size:17px;
}
.job-side-card p{
    margin:0 0 12px;
    color:#5d647a;
    line-height:1.7;
}
.job-side-list{
    list-style:none;
    padding:0;
    margin:0;
}
.job-side-list li{
    display:flex;
    justify-content:space-between;
    gap:16px;
    padding:12px 0;
    border-bottom:1px solid #f1eff8;
    color:#5d647a;
}
.job-side-list li:last-child{
    border-bottom:none;
}
.job-side-list span{
    color:#8a8fa3;
}
.job-side-list strong{
    color:#1f2233;
}
@media (max-width: 1100px){
    .job-detail-wrap{
        grid-template-columns:1fr;
    }
    .job-hero-top{
        grid-template-columns:72px 1fr;
    }
    .job-hero-actions{
        grid-column:1 / -1;
        margin-top:8px;
        flex-wrap:wrap;
    }
}
</style>

<div class="student-shell">
    <aside class="student-sidebar">
        <div>
            <div class="student-brand">
                <div class="student-brand__logo">✦</div>
                <div class="student-brand__text">
                    <h3><?= htmlspecialchars($user['full_name']) ?></h3>
                    <p>Aspiring Student</p>
                </div>
            </div>

            <nav class="student-nav">
                <a href="/Uniworksmohinhhoa/student/dashboard.php">Dashboard</a>
                <a href="/Uniworksmohinhhoa/student/applications.php">Applications</a>
                <a href="/Uniworksmohinhhoa/student/jobs.php" class="active">Internships</a>
                <a href="/Uniworksmohinhhoa/student/messages.php">Messages</a>
                <a href="/Uniworksmohinhhoa/student/profile.php">Profile</a>
                <a href="/Uniworksmohinhhoa/student/report.php">Final Report</a>
                <a href="/Uniworksmohinhhoa/student/evaluation.php">Evaluation</a>
            </nav>
        </div>

        <div class="student-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">↩ Logout</a>
        </div>
    </aside>

    <main class="student-main">
        <div class="job-detail-wrap">
            <div class="job-detail-left">
                <div class="job-hero-card">
                    <div class="job-hero-top">
                        <div class="job-hero-logo">
                            <?= strtoupper(substr($job['company_name'], 0, 1)) ?>
                        </div>

                        <div class="job-hero-info">
                            <p class="job-breadcrumb">Internships > Job Detail</p>
                            <h1><?= htmlspecialchars($job['title']) ?></h1>
                            <p class="job-subtitle">
                                <?= htmlspecialchars($job['company_name']) ?>
                                <?php if (!empty($job['industry_type'])): ?>
                                    • <?= htmlspecialchars($job['industry_type']) ?>
                                <?php endif; ?>
                                <?php if (!empty($job['period_name'])): ?>
                                    • <?= htmlspecialchars($job['period_name']) ?>
                                <?php endif; ?>
                            </p>

                            <div class="job-meta-row">
                                <?php if (!empty($job['deadline'])): ?>
                                    <span>Deadline: <?= htmlspecialchars($job['deadline']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($job['slots'])): ?>
                                    <span>Slots: <?= htmlspecialchars($job['slots']) ?></span>
                                <?php endif; ?>
                                <span>Status: <?= htmlspecialchars(ucfirst($job['status'])) ?></span>
                            </div>
                        </div>

                        <div class="job-hero-actions">
                            <a href="/Uniworksmohinhhoa/student/messages.php" class="student-btn-outline">Message Recruiter</a>
                            <a href="/Uniworksmohinhhoa/student/apply.php?job_id=<?= $job['id'] ?>" class="student-btn">Apply Now</a>
                        </div>
                    </div>

                    <div class="job-tabs">
                        <span class="active">Description</span>
                        <span>Requirements</span>
                        <span>Company</span>
                    </div>
                </div>

                <div class="job-section-card">
                    <h3>Job Description</h3>
                    <p>
                        <?= nl2br(htmlspecialchars($job['description'] ?? 'No description provided.')) ?>
                    </p>
                </div>

                <div class="job-section-card">
                    <h3>Requirements</h3>
                    <p>
                        <?= nl2br(htmlspecialchars($job['requirements'] ?? 'No requirements provided.')) ?>
                    </p>
                </div>
            </div>

            <div class="job-detail-right">
                <div class="job-side-card">
                    <h4>Company</h4>
                    <p><strong>Name:</strong> <?= htmlspecialchars($job['company_name']) ?></p>
                    <p><strong>Industry:</strong> <?= htmlspecialchars($job['industry_type'] ?: 'Updating') ?></p>
                    <p><strong>Website:</strong> <?= htmlspecialchars($job['website'] ?: 'Updating') ?></p>
                    <p><strong>Address:</strong> <?= htmlspecialchars($job['address'] ?: 'Updating') ?></p>
                </div>

                <div class="job-side-card">
                    <h4>Internship Overview</h4>
                    <ul class="job-side-list">
                        <li><span>Period</span><strong><?= htmlspecialchars($job['period_name'] ?: 'Updating') ?></strong></li>
                        <li><span>Deadline</span><strong><?= htmlspecialchars($job['deadline'] ?: 'Updating') ?></strong></li>
                        <li><span>Slots</span><strong><?= htmlspecialchars($job['slots'] ?: 'Updating') ?></strong></li>
                        <li><span>Status</span><strong><?= htmlspecialchars(ucfirst($job['status'])) ?></strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>