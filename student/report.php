<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('student');

$user = currentUser();
$flash = getFlash();

/*
|-------------------------------------------------------
| Lấy student hiện tại
|-------------------------------------------------------
*/
$stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
$stmt->execute([$user['id']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    redirect('/Uniworksmohinhhoa/student/profile.php?setup=1');
}

/*
|-------------------------------------------------------
| Lấy internship registration của student
|-------------------------------------------------------
*/
$stmt = $pdo->prepare("
    SELECT 
        ir.id AS registration_id,
        ir.start_date,
        ir.end_date,
        ir.status,
        j.title AS job_title,
        c.company_name,
        r.id AS report_id
    FROM internship_registrations ir
    INNER JOIN applications a ON ir.application_id = a.id
    INNER JOIN jobs j ON a.job_id = j.id
    INNER JOIN companies c ON j.company_id = c.id
    LEFT JOIN reports r ON r.registration_id = ir.id
    WHERE a.student_id = ?
    ORDER BY ir.id DESC
    LIMIT 1
");
$stmt->execute([$student['id']]);
$internship = $stmt->fetch(PDO::FETCH_ASSOC);

// Lấy chi tiết report nếu đã nộp
$reportDetail = null;
if (!empty($internship['report_id'])) {
    $stmt2 = $pdo->prepare("SELECT content, file_url, submitted_at FROM reports WHERE id = ?");
    $stmt2->execute([$internship['report_id']]);
    $reportDetail = $stmt2->fetch(PDO::FETCH_ASSOC);
}

require_once '../includes/notifications.php';
include '../includes/header.php';
?>

<style>
.student-report-page{
    padding:8px 6px 24px;
}

.student-report-header{
    margin-bottom:24px;
}

.student-report-header h1{
    margin:0 0 10px;
    font-size:44px;
    line-height:1.08;
    color:#1a1f36;
    font-weight:800;
}

.student-report-header p{
    margin:0;
    font-size:18px;
    color:#707894;
    line-height:1.6;
}

.student-report-layout{
    display:grid;
    grid-template-columns:1.3fr .9fr;
    gap:24px;
    align-items:start;
}

.student-report-card{
    background:#fff;
    border:1px solid #ece9f7;
    border-radius:30px;
    padding:28px;
    box-shadow:0 12px 28px rgba(31,34,51,.05);
}

.student-report-card h2{
    margin:0 0 16px;
    font-size:24px;
    color:#1a1f36;
    font-weight:800;
}

.student-report-info{
    display:grid;
    gap:14px;
}

.student-report-info-item{
    display:flex;
    justify-content:space-between;
    gap:16px;
    padding:14px 0;
    border-bottom:1px solid #f1edf8;
}

.student-report-info-item:last-child{
    border-bottom:none;
}

.student-report-info-item span{
    color:#8a93aa;
    font-weight:600;
}

.student-report-info-item strong{
    color:#1f2233;
    text-align:right;
}

.student-report-form{
    display:flex;
    flex-direction:column;
    gap:18px;
}

.student-report-form label{
    font-size:16px;
    font-weight:700;
    color:#25283a;
}

.student-report-form textarea{
    width:100%;
    min-height:220px;
    border:1.5px solid #ddd9ef;
    border-radius:18px;
    background:#fff;
    padding:16px;
    font-size:16px;
    color:#1f2233;
    outline:none;
    transition:.2s ease;
    resize:vertical;
    box-sizing:border-box;
}

.student-report-form textarea:focus{
    border-color:#c7b7ff;
    box-shadow:0 0 0 4px rgba(207,192,255,.18);
}

.student-report-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:150px;
    height:48px;
    border:none;
    border-radius:16px;
    background:#cfc0ff;
    color:#1f2233;
    font-size:15px;
    font-weight:800;
    cursor:pointer;
    transition:.2s ease;
}

.student-report-btn:hover{
    transform:translateY(-1px);
    background:#c2b1ff;
}

.student-report-note{
    background:#fff7d6;
    border:1px solid #efe0a0;
    border-radius:24px;
    padding:22px;
    color:#5f6478;
    line-height:1.7;
}

.student-report-note h3{
    margin:0 0 10px;
    font-size:22px;
    color:#1a1f36;
    font-weight:800;
}

.student-report-note p{
    margin:0;
}

.student-report-badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:9px 12px;
    border-radius:999px;
    font-size:13px;
    font-weight:800;
    white-space:nowrap;
}

.student-report-badge.ongoing{
    background:#dce7ff;
    color:#265ad9;
}

.student-report-badge.completed{
    background:#d8f2df;
    color:#187a3d;
}

.student-report-badge.default{
    background:#f1edff;
    color:#6157aa;
}

.student-report-empty{
    background:#fff;
    border:1px solid #ece9f7;
    border-radius:30px;
    padding:32px;
    color:#707894;
    box-shadow:0 12px 28px rgba(31,34,51,.05);
}

@media (max-width: 1000px){
    .student-report-layout{
        grid-template-columns:1fr;
    }
}

@media (max-width: 768px){
    .student-report-header h1{
        font-size:36px;
    }

    .student-report-header p{
        font-size:16px;
    }

    .student-report-info-item{
        flex-direction:column;
        align-items:flex-start;
    }

    .student-report-info-item strong{
        text-align:left;
    }

    .student-report-btn{
        width:100%;
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
                <a href="/Uniworksmohinhhoa/student/jobs.php">Internships</a>
                <a href="/Uniworksmohinhhoa/student/messages.php">Messages<?php if(!empty($notif['messages']) && $notif['messages']>0): ?><span class="notif-badge"><?= $notif['messages'] ?></span><?php endif; ?></a>
                <a href="/Uniworksmohinhhoa/student/profile.php">Profile</a>
                <a href="/Uniworksmohinhhoa/student/report.php" class="active">Final Report</a>
                <a href="/Uniworksmohinhhoa/student/evaluation.php">Evaluation<?php if(!empty($notif['evaluations']) && $notif['evaluations']>0): ?><span class="notif-badge"><?= $notif['evaluations'] ?></span><?php endif; ?></a>
            </nav>
        </div>

        <div class="student-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">↩ Logout</a>
        </div>
    </aside>

    <main class="student-main">
        <div class="student-report-page">
            <div class="student-report-header">
                <h1>Final Report</h1>
                <p>Submit your final internship report after completing your internship period.</p>
            </div>

            <?php if ($flash): ?>
                <div class="flash <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:18px;">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <?php if ($internship): ?>
                <div class="student-report-layout">
                    <section class="student-report-card">
                        <h2>Internship Information</h2>

                        <div class="student-report-info">
                            <div class="student-report-info-item">
                                <span>Company</span>
                                <strong><?= htmlspecialchars($internship['company_name']) ?></strong>
                            </div>

                            <div class="student-report-info-item">
                                <span>Job</span>
                                <strong><?= htmlspecialchars($internship['job_title']) ?></strong>
                            </div>

                            <div class="student-report-info-item">
                                <span>Start Date</span>
                                <strong><?= htmlspecialchars($internship['start_date']) ?></strong>
                            </div>

                            <div class="student-report-info-item">
                                <span>End Date</span>
                                <strong><?= htmlspecialchars($internship['end_date']) ?></strong>
                            </div>

                            <div class="student-report-info-item">
                                <span>Status</span>
                                <strong>
                                    <span class="student-report-badge <?= in_array($internship['status'], ['ongoing', 'completed']) ? htmlspecialchars($internship['status']) : 'default' ?>">
                                        <?= htmlspecialchars(ucfirst($internship['status'])) ?>
                                    </span>
                                </strong>
                            </div>
                        </div>
                    </section>

                    <aside class="student-report-note">
                        <h3>Submission Note</h3>
                        <p>
                            You can submit your final report after your internship has been completed. If a report has already been submitted, the system will prevent duplicate submission.
                        </p>
                    </aside>
                </div>

                <div style="height:24px;"></div>

                <div class="student-report-card">
                    <h2>Submit Final Report</h2>

                    <?php if (!empty($internship['report_id'])): ?>
                        <div class="student-report-note" style="background:#f6f2ff; border-color:#e8ddff;">
                            <h3>Report Already Submitted</h3>
                            <p>Your final report has already been submitted for this internship.</p>
                            <?php if (!empty($reportDetail['file_url'])): ?>
                                <p style="margin-top:10px;">
                                    <a href="/Uniworksmohinhhoa/<?= htmlspecialchars($reportDetail['file_url']) ?>"
                                       target="_blank"
                                       style="color:#7f4df3;font-weight:700;">📎 View Submitted File</a>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($internship['status'] !== 'completed'): ?>
                        <div class="student-report-note" style="background:#f6f2ff; border-color:#e8ddff;">
                            <h3>Internship Not Completed Yet</h3>
                            <p>You can only submit the final report after your internship status becomes <strong>completed</strong>.</p>
                        </div>
                    <?php else: ?>
                        <form action="/Uniworksmohinhhoa/actions/student/submit_report_action.php" method="POST" enctype="multipart/form-data" class="student-report-form">
                            <input type="hidden" name="registration_id" value="<?= $internship['registration_id'] ?>">

                            <div>
                                <label for="content">Report Content</label>
                                <textarea id="content" name="content" placeholder="Write your final internship report here..." required></textarea>
                            </div>

                            <div>
                                <label for="report_file">Upload Report File <span style="color:#b42323;">*</span> <span style="font-weight:400;color:#7f8496;">(PDF, DOC, DOCX — max 10MB)</span></label>
                                <input type="file" id="report_file" name="report_file"
                                       accept=".pdf,.doc,.docx"
                                       required
                                       style="width:100%;padding:12px 16px;border:1.5px solid #ddd9ef;border-radius:18px;background:#fff;font-size:15px;outline:none;box-sizing:border-box;">
                            </div>

                            <button type="submit" class="student-report-btn">Submit Report</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="student-report-empty">
                    No internship registration found yet. You can submit a final report after being accepted and completing your internship.
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>