<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('company');

$user = currentUser();
$registration_id = (int)($_GET['id'] ?? 0);
$flash = getFlash();

if ($registration_id <= 0) {
    setFlash('error', 'Invalid internship registration.');
    redirect('/Uniworksmohinhhoa/company/applications.php');
}

/*
|-------------------------------------------------------
| Lấy company hiện tại
|-------------------------------------------------------
*/
$stmt = $pdo->prepare("SELECT id, company_name FROM companies WHERE user_id = ?");
$stmt->execute([$user['id']]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company) {
    setFlash('error', 'Company profile not found.');
    redirect('/Uniworksmohinhhoa/company/profile.php?setup=1');
}

/*
|-------------------------------------------------------
| Lấy internship registration thuộc company này
|-------------------------------------------------------
*/
$stmt = $pdo->prepare("
    SELECT 
        ir.id AS registration_id,
        ir.start_date,
        ir.end_date,
        ir.status AS internship_status,
        u.full_name AS student_name,
        u.email,
        u.phone,
        s.student_code,
        s.class_name,
        s.gpa,
        j.title AS job_title,
        c.company_name,
        e.id AS evaluation_id,
        e.score,
        e.feedback,
        e.created_at
    FROM internship_registrations ir
    INNER JOIN applications a ON ir.application_id = a.id
    INNER JOIN students s ON a.student_id = s.id
    INNER JOIN users u ON s.user_id = u.id
    INNER JOIN jobs j ON a.job_id = j.id
    INNER JOIN companies c ON j.company_id = c.id
    LEFT JOIN evaluations e ON e.registration_id = ir.id
    WHERE ir.id = ?
      AND c.id = ?
");
$stmt->execute([$registration_id, $company['id']]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    setFlash('error', 'Internship record not found or access denied.');
    redirect('/Uniworksmohinhhoa/company/applications.php');
}

include '../includes/header.php';
?>

<style>
.company-eval-page{
    padding:8px 6px 24px;
}
.company-eval-header{
    margin-bottom:24px;
}
.company-eval-header h1{
    margin:0 0 10px;
    font-size:44px;
    line-height:1.08;
    color:#1a1f36;
    font-weight:800;
}
.company-eval-header p{
    margin:0;
    font-size:18px;
    color:#707894;
    line-height:1.6;
}
.company-eval-layout{
    display:grid;
    grid-template-columns:1.2fr .9fr;
    gap:24px;
    align-items:start;
}
.company-eval-card{
    background:#fff;
    border:1px solid #ece9f7;
    border-radius:30px;
    padding:28px;
    box-shadow:0 12px 28px rgba(31,34,51,.05);
}
.company-eval-card h2{
    margin:0 0 18px;
    font-size:24px;
    color:#161b34;
    font-weight:800;
}
.company-eval-info{
    display:grid;
    grid-template-columns:repeat(2, minmax(0, 1fr));
    gap:18px;
}
.company-eval-box{
    background:#fcfbff;
    border:1px solid #f0ebfb;
    border-radius:22px;
    padding:18px;
}
.company-eval-box span{
    display:block;
    margin-bottom:8px;
    font-size:13px;
    font-weight:800;
    letter-spacing:.03em;
    text-transform:uppercase;
    color:#8b93ab;
}
.company-eval-box strong{
    color:#1f2233;
    font-size:18px;
    line-height:1.5;
}
.company-eval-form{
    display:flex;
    flex-direction:column;
    gap:18px;
}
.company-eval-form label{
    font-size:16px;
    font-weight:700;
    color:#25283a;
    display:block;
    margin-bottom:10px;
}
.company-eval-form input,
.company-eval-form textarea{
    width:100%;
    border:1.5px solid #ddd9ef;
    border-radius:18px;
    background:#fff;
    padding:15px 16px;
    font-size:16px;
    color:#1f2233;
    outline:none;
    transition:.2s ease;
    box-sizing:border-box;
}
.company-eval-form textarea{
    min-height:180px;
    resize:vertical;
}
.company-eval-form input:focus,
.company-eval-form textarea:focus{
    border-color:#c7b7ff;
    box-shadow:0 0 0 4px rgba(207,192,255,.18);
}
.company-eval-actions{
    display:flex;
    flex-wrap:wrap;
    gap:12px;
}
.company-eval-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:140px;
    height:48px;
    padding:0 18px;
    border:none;
    border-radius:16px;
    text-decoration:none;
    font-size:15px;
    font-weight:800;
    transition:.2s ease;
    cursor:pointer;
}
.company-eval-btn:hover{
    transform:translateY(-1px);
}
.company-eval-btn.primary{
    background:#cfc0ff;
    color:#1f2233;
}
.company-eval-btn.primary:hover{
    background:#c2b1ff;
}
.company-eval-btn.ghost{
    background:#f6f2ff;
    color:#5e6680;
}
.company-eval-btn.ghost:hover{
    background:#eee7ff;
}
.company-eval-note{
    background:#fff7d6;
    border:1px solid #efe0a0;
    border-radius:24px;
    padding:22px;
    color:#5f6478;
    line-height:1.7;
}
.company-eval-note h3{
    margin:0 0 10px;
    font-size:22px;
    color:#1a1f36;
    font-weight:800;
}
.company-eval-note p{
    margin:0;
}
.company-eval-badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:9px 12px;
    border-radius:999px;
    font-size:13px;
    font-weight:800;
}
.company-eval-badge.ongoing{
    background:#dce7ff;
    color:#265ad9;
}
.company-eval-badge.completed{
    background:#d8f2df;
    color:#187a3d;
}
.company-eval-badge.default{
    background:#f1edff;
    color:#6157aa;
}
.company-eval-readonly{
    background:#fcfbff;
    border:1px solid #f0ebfb;
    border-radius:22px;
    padding:18px;
}
.company-eval-readonly p{
    margin:0;
    color:#4f5770;
    line-height:1.8;
    white-space:pre-line;
}
.company-eval-meta{
    margin-top:12px;
    color:#8b93ab;
    font-size:14px;
    font-weight:600;
}
@media (max-width: 1000px){
    .company-eval-layout{
        grid-template-columns:1fr;
    }
}
@media (max-width: 768px){
    .company-eval-header h1{
        font-size:36px;
    }
    .company-eval-header p{
        font-size:16px;
    }
    .company-eval-info{
        grid-template-columns:1fr;
    }
    .company-eval-btn{
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
        <div class="company-eval-page">
            <div class="company-eval-header">
                <h1>Intern Evaluation</h1>
                <p>Evaluate the student after the internship has been completed.</p>
            </div>

            <?php if ($flash): ?>
                <div class="flash <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:18px;">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="company-eval-layout">
                <section class="company-eval-card">
                    <h2>Student & Internship Information</h2>

                    <div class="company-eval-info">
                        <div class="company-eval-box">
                            <span>Student</span>
                            <strong><?= htmlspecialchars($item['student_name']) ?></strong>
                        </div>

                        <div class="company-eval-box">
                            <span>Student Code</span>
                            <strong><?= htmlspecialchars($item['student_code'] ?: 'Updating') ?></strong>
                        </div>

                        <div class="company-eval-box">
                            <span>Class</span>
                            <strong><?= htmlspecialchars($item['class_name'] ?: 'Updating') ?></strong>
                        </div>

                        <div class="company-eval-box">
                            <span>GPA</span>
                            <strong><?= htmlspecialchars(number_format((float)$item['gpa'], 2)) ?></strong>
                        </div>

                        <div class="company-eval-box">
                            <span>Job</span>
                            <strong><?= htmlspecialchars($item['job_title']) ?></strong>
                        </div>

                        <div class="company-eval-box">
                            <span>Internship Status</span>
                            <strong>
                                <span class="company-eval-badge <?= in_array($item['internship_status'], ['ongoing','completed']) ? htmlspecialchars($item['internship_status']) : 'default' ?>">
                                    <?= htmlspecialchars(ucfirst($item['internship_status'])) ?>
                                </span>
                            </strong>
                        </div>

                        <div class="company-eval-box">
                            <span>Start Date</span>
                            <strong><?= htmlspecialchars($item['start_date']) ?></strong>
                        </div>

                        <div class="company-eval-box">
                            <span>End Date</span>
                            <strong><?= htmlspecialchars($item['end_date']) ?></strong>
                        </div>
                    </div>
                </section>

                <aside class="company-eval-note">
                    <h3>Evaluation Rule</h3>
                    <p>
                        The company can only submit an evaluation after the internship status becomes <strong>completed</strong>. Each internship registration can only have one evaluation record.
                    </p>
                </aside>
            </div>

            <div style="height:24px;"></div>

            <div class="company-eval-card">
                <h2>Evaluation Form</h2>

                <?php if (!empty($item['evaluation_id'])): ?>
                    <div class="company-eval-note" style="background:#f6f2ff; border-color:#e8ddff; margin-bottom:18px;">
                        <h3>Evaluation Submitted</h3>
                        <p>You have already submitted the evaluation for this student. You can review it below.</p>
                    </div>

                    <div class="company-eval-readonly">
                        <p><strong>Score:</strong> <?= htmlspecialchars($item['score']) ?>/10</p>
                        <p><strong>Feedback:</strong><br><?= nl2br(htmlspecialchars($item['feedback'])) ?></p>
                    </div>

                    <div class="company-eval-meta">
                        Submitted at: <?= htmlspecialchars($item['created_at']) ?>
                    </div>

                    <div class="company-eval-actions" style="margin-top:20px;">
                        <a href="/Uniworksmohinhhoa/company/applications.php" class="company-eval-btn ghost">Back</a>
                    </div>
                <?php elseif ($item['internship_status'] !== 'completed'): ?>
                    <div class="company-eval-note" style="background:#f6f2ff; border-color:#e8ddff;">
                        <h3>Internship Not Completed Yet</h3>
                        <p>You can only submit the evaluation after the internship has been marked as <strong>completed</strong>.</p>
                    </div>

                    <div class="company-eval-actions" style="margin-top:20px;">
                        <a href="/Uniworksmohinhhoa/company/applications.php" class="company-eval-btn ghost">Back</a>
                    </div>
                <?php else: ?>
                    <form action="/Uniworksmohinhhoa/actions/company/evaluate_action.php" method="POST" class="company-eval-form">
                        <input type="hidden" name="registration_id" value="<?= $item['registration_id'] ?>">

                        <div>
                            <label for="score">Score (0 - 10)</label>
                            <input type="number" id="score" name="score" min="0" max="10" step="0.1" required>
                        </div>

                        <div>
                            <label for="feedback">Feedback</label>
                            <textarea id="feedback" name="feedback" placeholder="Write your evaluation for the student..." required></textarea>
                        </div>

                        <div class="company-eval-actions">
                            <a href="/Uniworksmohinhhoa/company/applications.php" class="company-eval-btn ghost">Back</a>
                            <button type="submit" class="company-eval-btn primary">Submit Evaluation</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>