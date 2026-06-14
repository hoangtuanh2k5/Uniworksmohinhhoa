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

// Lấy tất cả internship của company này kèm trạng thái đánh giá
$stmt = $pdo->prepare("
    SELECT
        ir.id              AS registration_id,
        ir.start_date,
        ir.end_date,
        ir.status          AS internship_status,
        u.full_name        AS student_name,
        u.email            AS student_email,
        s.student_code,
        s.gpa,
        j.title            AS job_title,
        ip.name            AS period_name,
        e.id               AS eval_id,
        e.score,
        e.feedback,
        e.created_at       AS eval_date
    FROM internship_registrations ir
    INNER JOIN applications a   ON ir.application_id = a.id
    INNER JOIN jobs j           ON a.job_id = j.id
    INNER JOIN internship_periods ip ON j.period_id = ip.id
    INNER JOIN students s       ON a.student_id = s.id
    INNER JOIN users u          ON s.user_id = u.id
    LEFT  JOIN evaluations e    ON e.registration_id = ir.id AND e.evaluator_role = 'company'
    WHERE j.company_id = ?
    ORDER BY ir.id DESC
");
$stmt->execute([$company['id']]);
$interns = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tách thành 2 nhóm
$needEval = array_filter($interns, fn($i) => $i['internship_status'] === 'completed' && !$i['eval_id']);
$done     = array_filter($interns, fn($i) => $i['eval_id']);
$ongoing  = array_filter($interns, fn($i) => $i['internship_status'] === 'ongoing');

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
                <a href="/Uniworksmohinhhoa/company/internship_history.php">History</a>
                <a href="/Uniworksmohinhhoa/company/evaluations.php" class="active">
                    Evaluations
                    <?php if (count($needEval) > 0): ?>
                        <span style="display:inline-flex;align-items:center;justify-content:center;min-width:18px;height:18px;border-radius:999px;background:#f0d86b;color:#17172b;font-size:10px;font-weight:800;margin-left:6px;padding:0 4px;">
                            <?= count($needEval) ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="/Uniworksmohinhhoa/company/messages.php">Messages</a>
                <a href="/Uniworksmohinhhoa/company/profile.php">Profile</a>
            </nav>
        </div>
        <div class="company-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">↩ Logout</a>
        </div>
    </aside>

    <main class="company-main">
        <div class="company-topbar">
            <div>
                <h1>Intern Evaluations</h1>
                <p>Mark internships as completed and submit evaluations for each student.</p>
            </div>
        </div>

        <?php if ($flash): ?>
            <div class="flash <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:18px;">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <!-- Stats -->
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
            <div class="company-stat-card purple">
                <h4>Ongoing</h4>
                <strong><?= count($ongoing) ?></strong>
            </div>
            <div class="company-stat-card yellow">
                <h4>Needs Evaluation</h4>
                <strong><?= count($needEval) ?></strong>
            </div>
            <div style="background:#d8f2df;border-radius:22px;padding:20px;">
                <h4 style="font-size:14px;color:#145e30;margin:0 0 10px;">Evaluated</h4>
                <strong style="font-size:28px;color:#17172b;"><?= count($done) ?></strong>
            </div>
        </div>

        <!-- SECTION 1: Ongoing — có thể mark completed -->
        <?php if (!empty($ongoing)): ?>
        <div class="company-card" style="margin-bottom:20px;">
            <div class="company-card__title">
                <h3>🔄 Ongoing Internships</h3>
                <span class="company-muted">Mark as completed to unlock evaluation</span>
            </div>
            <table class="company-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Job</th>
                        <th>Period</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ongoing as $i): ?>
                    <tr>
                        <td>
                            <div style="font-weight:700;color:#17172b;"><?= htmlspecialchars($i['student_name']) ?></div>
                            <div style="font-size:12px;color:#8a8fa3;"><?= htmlspecialchars($i['student_code']) ?></div>
                        </td>
                        <td><?= htmlspecialchars($i['job_title']) ?></td>
                        <td><?= htmlspecialchars($i['period_name']) ?></td>
                        <td style="font-size:13px;color:#4d566f;"><?= $i['start_date'] ? date('M d, Y', strtotime($i['start_date'])) : '—' ?></td>
                        <td style="font-size:13px;color:#4d566f;"><?= $i['end_date'] ? date('M d, Y', strtotime($i['end_date'])) : '—' ?></td>
                        <td>
                            <form action="/Uniworksmohinhhoa/actions/company/complete_internship_action.php" method="POST"
                                  onsubmit="return confirm('Mark this internship as completed?')">
                                <input type="hidden" name="registration_id" value="<?= $i['registration_id'] ?>">
                                <button type="submit"
                                        style="border:none;cursor:pointer;padding:8px 14px;border-radius:12px;background:#d8f2df;color:#187a3d;font-size:13px;font-weight:800;">
                                    ✓ Mark Completed
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- SECTION 2: Completed — chưa có đánh giá -->
        <?php if (!empty($needEval)): ?>
        <div class="company-card" style="margin-bottom:20px;">
            <div class="company-card__title">
                <h3>📝 Pending Evaluation</h3>
                <span class="company-muted">Completed internships waiting for your evaluation</span>
            </div>
            <table class="company-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>GPA</th>
                        <th>Job</th>
                        <th>Period</th>
                        <th>Completed</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($needEval as $i): ?>
                    <tr>
                        <td>
                            <div style="font-weight:700;color:#17172b;"><?= htmlspecialchars($i['student_name']) ?></div>
                            <div style="font-size:12px;color:#8a8fa3;"><?= htmlspecialchars($i['student_code']) ?> &middot; <?= htmlspecialchars($i['student_email']) ?></div>
                        </td>
                        <td style="font-weight:700;color:#17172b;"><?= $i['gpa'] !== null ? number_format((float)$i['gpa'], 2) : '—' ?></td>
                        <td><?= htmlspecialchars($i['job_title']) ?></td>
                        <td><?= htmlspecialchars($i['period_name']) ?></td>
                        <td style="font-size:13px;color:#4d566f;"><?= $i['end_date'] ? date('M d, Y', strtotime($i['end_date'])) : '—' ?></td>
                        <td>
                            <a href="/Uniworksmohinhhoa/company/evaluate.php?id=<?= $i['registration_id'] ?>"
                               style="display:inline-flex;align-items:center;padding:8px 14px;border-radius:12px;background:#cfc6f6;color:#17172b;font-size:13px;font-weight:800;text-decoration:none;">
                                ✏️ Evaluate
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- SECTION 3: Đã đánh giá -->
        <div class="company-card">
            <div class="company-card__title">
                <h3>✅ Evaluation History</h3>
                <span class="company-muted"><?= count($done) ?> completed</span>
            </div>

            <?php if (empty($done)): ?>
                <p class="company-muted">No evaluations submitted yet.</p>
            <?php else: ?>
                <table class="company-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Job</th>
                            <th>Score</th>
                            <th>Feedback</th>
                            <th>Evaluated On</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($done as $i): ?>
                        <tr>
                            <td>
                                <div style="font-weight:700;color:#17172b;"><?= htmlspecialchars($i['student_name']) ?></div>
                                <div style="font-size:12px;color:#8a8fa3;"><?= htmlspecialchars($i['student_code']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($i['job_title']) ?></td>
                            <td>
                                <span style="font-size:18px;font-weight:800;color:#17172b;"><?= number_format((float)$i['score'], 1) ?></span>
                                <span style="font-size:12px;color:#8a8fa3;">/10</span>
                            </td>
                            <td style="max-width:220px;font-size:13px;color:#4d566f;">
                                <?= htmlspecialchars(mb_strlen($i['feedback']) > 60 ? mb_substr($i['feedback'], 0, 60) . '...' : $i['feedback']) ?>
                            </td>
                            <td style="font-size:13px;color:#7a8096;white-space:nowrap;">
                                <?= date('M d, Y', strtotime($i['eval_date'])) ?>
                            </td>
                            <td>
                                <a href="/Uniworksmohinhhoa/company/evaluate.php?id=<?= $i['registration_id'] ?>"
                                   style="display:inline-flex;align-items:center;padding:7px 12px;border-radius:12px;background:#f4f1ff;color:#6157aa;font-size:13px;font-weight:700;text-decoration:none;">
                                    View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </main>
</div>

<?php include '../includes/footer.php'; ?>
