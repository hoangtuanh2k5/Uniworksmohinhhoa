<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('admin');

$user = currentUser();
$student_id = (int)($_GET['id'] ?? 0);

if ($student_id <= 0) {
    setFlash('error', 'Invalid student.');
    redirect('/Uniworksmohinhhoa/admin/applications.php');
}

$stmt = $pdo->prepare("
    SELECT 
        s.id,
        s.student_code,
        s.class_name,
        s.gpa,
        u.full_name,
        u.email,
        u.phone
    FROM students s
    INNER JOIN users u ON s.user_id = u.id
    WHERE s.id = ?
");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    setFlash('error', 'Student not found.');
    redirect('/Uniworksmohinhhoa/admin/applications.php');
}

// Lấy tất cả applications của student này
$stmt2 = $pdo->prepare("
    SELECT 
        a.id,
        a.cv_url,
        a.status,
        a.admin_approved,
        a.company_approved,
        a.applied_at,
        j.title AS job_title,
        c.company_name
    FROM applications a
    INNER JOIN jobs j ON a.job_id = j.id
    INNER JOIN companies c ON j.company_id = c.id
    WHERE a.student_id = ?
    ORDER BY a.id DESC
");
$stmt2->execute([$student_id]);
$applications = $stmt2->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/notifications.php';
include '../includes/header.php';
?>

<style>
.admin-brand{
    padding:8px 8px 28px;
    margin-bottom:16px;
}

.admin-brand h2{
    margin:0;
    font-size:24px;
    line-height:1.25;
    color:#1b2038;
    font-weight:800;
}

.admin-nav{
    display:flex;
    flex-direction:column;
    gap:12px;
    margin-top:10px;
}

.admin-student-page{
    padding:8px 6px 24px;
}

.admin-student-header{
    margin-bottom:24px;
}

.admin-student-header h1{
    margin:0 0 10px;
    font-size:46px;
    line-height:1.08;
    color:#161b34;
    font-weight:800;
}

.admin-student-header p{
    margin:0;
    font-size:18px;
    color:#707894;
    line-height:1.6;
}

.admin-student-layout{
    display:grid;
    grid-template-columns:1.35fr .9fr;
    gap:24px;
    align-items:start;
}

.admin-student-card{
    background:#fff;
    border:1px solid #ece9f7;
    border-radius:30px;
    padding:28px;
    box-shadow:0 12px 28px rgba(31,34,51,.05);
}

.admin-student-hero{
    display:flex;
    align-items:flex-start;
    gap:20px;
    margin-bottom:24px;
}

.admin-student-avatar{
    width:86px;
    height:86px;
    border-radius:24px;
    background:#cfc6ff;
    color:#1f2233;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:34px;
    font-weight:800;
    flex-shrink:0;
}

.admin-student-main h2{
    margin:0 0 8px;
    font-size:36px;
    line-height:1.1;
    color:#161b34;
    font-weight:800;
}

.admin-student-main p{
    margin:0;
    font-size:18px;
    color:#6f7790;
    line-height:1.6;
}

.admin-student-grid{
    display:grid;
    grid-template-columns:repeat(2, minmax(0, 1fr));
    gap:18px;
}

.admin-info-box{
    background:#fcfbff;
    border:1px solid #f0ebfb;
    border-radius:22px;
    padding:18px;
}

.admin-info-box span{
    display:block;
    margin-bottom:8px;
    font-size:13px;
    font-weight:800;
    letter-spacing:.03em;
    text-transform:uppercase;
    color:#8b93ab;
}

.admin-info-box strong{
    color:#1f2233;
    font-size:18px;
    line-height:1.5;
}

.admin-side-stack{
    display:flex;
    flex-direction:column;
    gap:24px;
}

.admin-highlight{
    background:#fff7d6;
    border:1px solid #efe0a0;
    border-radius:28px;
    padding:24px;
    box-shadow:0 12px 28px rgba(31,34,51,.05);
}

.admin-highlight h3{
    margin:0 0 10px;
    font-size:24px;
    color:#161b34;
    font-weight:800;
}

.admin-highlight p{
    margin:0;
    color:#6f7790;
    line-height:1.7;
    font-size:16px;
}

.admin-mini-card{
    background:#fff;
    border:1px solid #ece9f7;
    border-radius:28px;
    padding:24px;
    box-shadow:0 12px 28px rgba(31,34,51,.05);
}

.admin-mini-card h3{
    margin:0 0 14px;
    font-size:22px;
    color:#161b34;
    font-weight:800;
}

.admin-mini-list{
    display:grid;
    gap:14px;
}

.admin-mini-item{
    display:flex;
    justify-content:space-between;
    gap:16px;
    padding:14px 0;
    border-bottom:1px solid #f1edf8;
}

.admin-mini-item:last-child{
    border-bottom:none;
    padding-bottom:0;
}

.admin-mini-item span{
    color:#8a93aa;
    font-weight:600;
}

.admin-mini-item strong{
    color:#1f2233;
    text-align:right;
}

.admin-btn-row{
    display:flex;
    flex-wrap:wrap;
    gap:12px;
    margin-top:22px;
}

.admin-btn{
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

.admin-btn:hover{
    transform:translateY(-1px);
}

.admin-btn.purple{
    background:#cfc6ff;
    color:#1f2233;
}

.admin-btn.purple:hover{
    background:#c1b3ff;
}

.admin-btn.yellow{
    background:#efd867;
    color:#1f2233;
}

.admin-btn.yellow:hover{
    background:#e7cf5d;
}

.admin-btn.ghost{
    background:#f6f2ff;
    color:#5e6680;
}

.admin-btn.ghost:hover{
    background:#eee7ff;
}

@media (max-width: 1100px){
    .admin-student-layout{
        grid-template-columns:1fr;
    }
}

@media (max-width: 768px){
    .admin-student-header h1{
        font-size:38px;
    }

    .admin-student-header p{
        font-size:16px;
    }

    .admin-student-hero{
        flex-direction:column;
    }

    .admin-student-main h2{
        font-size:30px;
    }

    .admin-student-grid{
        grid-template-columns:1fr;
    }

    .admin-btn{
        width:100%;
    }
}
</style>

<div class="admin-shell">
    <aside class="admin-sidebar">
        <div>
            <div class="admin-brand">
                <h2>Admin Panel</h2>
            </div>

            <nav class="admin-nav">
                <a href="/Uniworksmohinhhoa/admin/dashboard.php">Dashboard</a>
                <a href="/Uniworksmohinhhoa/admin/users.php">Users</a>
                <a href="/Uniworksmohinhhoa/admin/applications.php" class="active">Applications</a>
                <a href="/Uniworksmohinhhoa/admin/monitoring.php">Monitoring</a>
                <a href="/Uniworksmohinhhoa/admin/reports.php">Reports<?php if(!empty($notif['reports']) && $notif['reports']>0): ?><span class="notif-badge"><?= $notif['reports'] ?></span><?php endif; ?></a>
            </nav>
        </div>

        <div class="admin-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">Logout</a>
        </div>
    </aside>

    <main class="admin-main">
        <div class="admin-student-page">
            <div class="admin-student-header">
                <h1>Student Profile</h1>
                <p>Review the student’s academic and contact information before making an application decision.</p>
            </div>

            <div class="admin-student-layout">
                <section class="admin-student-card">
                    <div class="admin-student-hero">
                        <div class="admin-student-avatar">
                            <?= htmlspecialchars(strtoupper(substr($student['full_name'], 0, 1))) ?>
                        </div>

                        <div class="admin-student-main">
                            <h2><?= htmlspecialchars($student['full_name']) ?></h2>
                            <p>Student profile overview for internship application review.</p>
                        </div>
                    </div>

                    <div class="admin-student-grid">
                        <div class="admin-info-box">
                            <span>Email</span>
                            <strong><?= htmlspecialchars($student['email'] ?: 'Updating') ?></strong>
                        </div>

                        <div class="admin-info-box">
                            <span>Phone</span>
                            <strong><?= htmlspecialchars($student['phone'] ?: 'Updating') ?></strong>
                        </div>

                        <div class="admin-info-box">
                            <span>Student Code</span>
                            <strong><?= htmlspecialchars($student['student_code'] ?: 'Updating') ?></strong>
                        </div>

                        <div class="admin-info-box">
                            <span>Class</span>
                            <strong><?= htmlspecialchars($student['class_name'] ?: 'Updating') ?></strong>
                        </div>

                        <div class="admin-info-box">
                            <span>GPA</span>
                            <strong><?= htmlspecialchars(number_format((float)$student['gpa'], 2)) ?></strong>
                        </div>

                        <div class="admin-info-box">
                            <span>Status</span>
                            <strong>Available for review</strong>
                        </div>
                    </div>
                </section>

                <aside class="admin-side-stack">
                    <div class="admin-highlight">
                        <h3>Review Note</h3>
                        <p>
                            Check this student’s profile carefully before approving the internship application. Focus on contact information, class, student code, and GPA.
                        </p>
                    </div>

                    <div class="admin-mini-card">
                        <h3>Quick Summary</h3>

                        <div class="admin-mini-list">
                            <div class="admin-mini-item">
                                <span>Student Name</span>
                                <strong><?= htmlspecialchars($student['full_name']) ?></strong>
                            </div>

                            <div class="admin-mini-item">
                                <span>Code</span>
                                <strong><?= htmlspecialchars($student['student_code'] ?: 'Updating') ?></strong>
                            </div>

                            <div class="admin-mini-item">
                                <span>Class</span>
                                <strong><?= htmlspecialchars($student['class_name'] ?: 'Updating') ?></strong>
                            </div>

                            <div class="admin-mini-item">
                                <span>GPA</span>
                                <strong><?= htmlspecialchars(number_format((float)$student['gpa'], 2)) ?></strong>
                            </div>
                        </div>

                        <div class="admin-btn-row">
                            <a href="/Uniworksmohinhhoa/admin/applications.php" class="admin-btn ghost">Back</a>
                            <a href="/Uniworksmohinhhoa/admin/users.php" class="admin-btn purple">Manage Users</a>
                        </div>
                    </div>
                </aside>
            </div>

            <!-- ===== APPLICATIONS OF THIS STUDENT ===== -->
            <div style="margin-top:28px;">
                <div class="admin-app-card" style="background:#fff;border:1px solid #ece9f7;border-radius:30px;box-shadow:0 12px 28px rgba(31,34,51,.05);overflow:hidden;">
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:22px 28px 16px;">
                        <h2 style="margin:0;font-size:22px;color:#161b34;font-weight:800;">
                            All Applications
                            <span style="margin-left:10px;display:inline-flex;align-items:center;padding:5px 12px;border-radius:999px;background:#f6f2ff;color:#6259aa;font-size:13px;font-weight:700;">
                                <?= count($applications) ?> job(s)
                            </span>
                        </h2>
                    </div>

                    <?php if (empty($applications)): ?>
                        <div style="padding:32px 28px;color:#7a8198;text-align:center;">No applications yet.</div>
                    <?php else: ?>
                        <div style="overflow-x:auto;">
                            <table style="width:100%;border-collapse:collapse;min-width:900px;">
                                <thead>
                                    <tr>
                                        <th style="text-align:left;padding:16px 24px;background:#faf8ff;color:#74809b;font-size:13px;font-weight:800;border-bottom:1px solid #ece9f7;">Job</th>
                                        <th style="text-align:left;padding:16px 24px;background:#faf8ff;color:#74809b;font-size:13px;font-weight:800;border-bottom:1px solid #ece9f7;">Company</th>
                                        <th style="text-align:left;padding:16px 24px;background:#faf8ff;color:#74809b;font-size:13px;font-weight:800;border-bottom:1px solid #ece9f7;">Applied At</th>
                                        <th style="text-align:left;padding:16px 24px;background:#faf8ff;color:#74809b;font-size:13px;font-weight:800;border-bottom:1px solid #ece9f7;">Status</th>
                                        <th style="text-align:left;padding:16px 24px;background:#faf8ff;color:#74809b;font-size:13px;font-weight:800;border-bottom:1px solid #ece9f7;">CV</th>
                                        <th style="text-align:left;padding:16px 24px;background:#faf8ff;color:#74809b;font-size:13px;font-weight:800;border-bottom:1px solid #ece9f7;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($applications as $app): ?>
                                        <?php
                                        // Status label
                                        if ((int)$app['admin_approved'] === 0)       { $label = 'Waiting Admin';              $cls = 'pending'; }
                                        elseif ((int)$app['admin_approved'] === -1)  { $label = 'Rejected by Admin';          $cls = 'rejected'; }
                                        elseif ((int)$app['company_approved'] === 0) { $label = 'Approved → Waiting Company'; $cls = 'reviewing'; }
                                        elseif ((int)$app['company_approved'] === 1) { $label = 'Accepted by Company';        $cls = 'accepted'; }
                                        elseif ((int)$app['company_approved'] === -1){ $label = 'Rejected by Company';        $cls = 'rejected'; }
                                        else { $label = ucfirst($app['status']); $cls = 'default'; }

                                        $badgeColors = [
                                            'pending'   => 'background:#f6e8a6;color:#a36a00;',
                                            'reviewing' => 'background:#dce7ff;color:#265ad9;',
                                            'accepted'  => 'background:#d8f2df;color:#187a3d;',
                                            'rejected'  => 'background:#ffe1e1;color:#ad3e3e;',
                                            'default'   => 'background:#f1edff;color:#6157aa;',
                                        ];
                                        $badgeStyle = $badgeColors[$cls] ?? $badgeColors['default'];
                                        ?>
                                        <tr>
                                            <td style="padding:16px 24px;border-bottom:1px solid #f1edf8;font-weight:700;color:#1f2233;">
                                                <?= htmlspecialchars($app['job_title']) ?>
                                            </td>
                                            <td style="padding:16px 24px;border-bottom:1px solid #f1edf8;color:#4d566f;font-weight:600;">
                                                <?= htmlspecialchars($app['company_name']) ?>
                                            </td>
                                            <td style="padding:16px 24px;border-bottom:1px solid #f1edf8;color:#6e7690;">
                                                <?= htmlspecialchars(date('d M Y', strtotime($app['applied_at']))) ?>
                                            </td>
                                            <td style="padding:16px 24px;border-bottom:1px solid #f1edf8;">
                                                <span style="display:inline-flex;align-items:center;padding:7px 12px;border-radius:999px;font-size:13px;font-weight:800;white-space:nowrap;<?= $badgeStyle ?>">
                                                    <?= htmlspecialchars($label) ?>
                                                </span>
                                            </td>
                                            <td style="padding:16px 24px;border-bottom:1px solid #f1edf8;">
                                                <?php if (!empty($app['cv_url'])): ?>
                                                    <a href="/Uniworksmohinhhoa/<?= htmlspecialchars($app['cv_url']) ?>"
                                                       target="_blank"
                                                       style="display:inline-flex;align-items:center;padding:7px 14px;border-radius:10px;background:#f0d86a;color:#1f2233;font-size:13px;font-weight:700;text-decoration:none;">
                                                        📄 CV
                                                    </a>
                                                <?php else: ?>
                                                    <span style="color:#9093a2;font-size:13px;">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="padding:16px 24px;border-bottom:1px solid #f1edf8;">
                                                <?php if ((int)$app['admin_approved'] === 0): ?>
                                                    <div style="display:flex;gap:8px;">
                                                        <form action="/Uniworksmohinhhoa/actions/admin/approve_application_action.php" method="POST" style="display:inline;">
                                                            <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                                                            <input type="hidden" name="redirect_back" value="/Uniworksmohinhhoa/admin/student_detail.php?id=<?= $student_id ?>">
                                                            <button type="submit"
                                                                style="display:inline-flex;align-items:center;height:36px;padding:0 14px;border:none;border-radius:10px;background:#cfc6ff;color:#1f2233;font-size:13px;font-weight:800;cursor:pointer;">
                                                                Approve
                                                            </button>
                                                        </form>
                                                        <form action="/Uniworksmohinhhoa/actions/admin/reject_application_action.php" method="POST" style="display:inline;">
                                                            <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                                                            <input type="hidden" name="redirect_back" value="/Uniworksmohinhhoa/admin/student_detail.php?id=<?= $student_id ?>">
                                                            <button type="submit"
                                                                style="display:inline-flex;align-items:center;height:36px;padding:0 14px;border:none;border-radius:10px;background:#efd867;color:#1f2233;font-size:13px;font-weight:800;cursor:pointer;">
                                                                Reject
                                                            </button>
                                                        </form>
                                                    </div>
                                                <?php else: ?>
                                                    <span style="color:#7c849c;font-size:13px;font-weight:700;">Reviewed</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>