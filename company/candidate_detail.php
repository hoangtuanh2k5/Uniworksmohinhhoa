<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'company') {
    redirect('/Uniworksmohinhhoa/public/login.php');
}

$user = $_SESSION['user'];
$appId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM companies WHERE user_id = ?");
$stmt->execute([$user['id']]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT a.*, 
           s.id AS student_id, s.student_code, s.class_name, s.gpa, s.user_id AS student_user_id,
           u.full_name AS student_name, u.email, u.phone,
           m.name AS major_name,
           j.title AS job_title
    FROM applications a
    JOIN students s ON a.student_id = s.id
    JOIN users u ON s.user_id = u.id
    LEFT JOIN majors m ON s.major_id = m.id
    JOIN jobs j ON a.job_id = j.id
    WHERE a.id = ? AND j.company_id = ?
");
$stmt->execute([$appId, $company['id']]);
$candidate = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$candidate) {
    setFlash('error', 'Candidate not found.');
    redirect('/Uniworksmohinhhoa/company/applications.php');
}

function formatAppStatus($status) {
    $status = strtolower(trim($status));
    if ($status === 'pending') return 'Pending';
    if ($status === 'reviewed') return 'Reviewed';
    if ($status === 'approved') return 'Approved';
    if ($status === 'rejected') return 'Rejected';
    return ucfirst($status);
}

function badgeClass($status) {
    $status = strtolower(trim($status));
    if (in_array($status, ['pending', 'reviewed', 'approved', 'rejected'])) {
        return $status;
    }
    return 'pending';
}

$success = getFlash('success');
$error = getFlash('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Candidate Detail</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="company-shell">
    <aside class="company-sidebar">
        <div>
            <div class="company-brand">
                <h2>Uniworks</h2>
                <p>Recruiter Portal</p>
            </div>

            <nav class="company-nav">
                <a href="dashboard.php">Dashboard</a>
                <a class="active" href="applications.php">Applicants</a>
                <a href="manage_job.php">Jobs</a>
                <a href="messages.php">Messages</a>
                <a href="profile.php">Profile</a>
            </nav>
        </div>

        <div class="company-signout">
            <a href="../public/logout.php">Sign Out</a>
        </div>
    </aside>

    <main class="company-main">
        <div class="topbar">
            <div></div>
            <div class="topbar-actions">
                <a class="btn btn-primary" href="applications.php">Back to Applicants</a>
            </div>
        </div>

        <?php if ($success): ?><div class="flash success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="flash error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <h1 class="page-title">Candidate Detail</h1>
        <p class="page-subtitle">Review candidate profile and application information.</p>

        <div class="card">
            <div class="detail-grid">
                <div class="detail-item">
                    <strong>Full Name</strong>
                    <?php echo htmlspecialchars($candidate['student_name']); ?>
                </div>

                <div class="detail-item">
                    <strong>Email</strong>
                    <?php echo htmlspecialchars($candidate['email']); ?>
                </div>

                <div class="detail-item">
                    <strong>Phone</strong>
                    <?php echo htmlspecialchars($candidate['phone'] ?? 'N/A'); ?>
                </div>

                <div class="detail-item">
                    <strong>Student Code</strong>
                    <?php echo htmlspecialchars($candidate['student_code']); ?>
                </div>

                <div class="detail-item">
                    <strong>Major</strong>
                    <?php echo htmlspecialchars($candidate['major_name'] ?? 'N/A'); ?>
                </div>

                <div class="detail-item">
                    <strong>Class</strong>
                    <?php echo htmlspecialchars($candidate['class_name'] ?? 'N/A'); ?>
                </div>

                <div class="detail-item">
                    <strong>GPA</strong>
                    <?php echo htmlspecialchars($candidate['gpa'] ?? 'N/A'); ?>
                </div>

                <div class="detail-item">
                    <strong>Applied Position</strong>
                    <?php echo htmlspecialchars($candidate['job_title']); ?>
                </div>

                <div class="detail-item">
                    <strong>Status</strong>
                    <span class="badge <?php echo badgeClass($candidate['status']); ?>">
                        <?php echo htmlspecialchars(formatAppStatus($candidate['status'])); ?>
                    </span>
                </div>

                <div class="detail-item">
                    <strong>Applied Date</strong>
                    <?php echo htmlspecialchars($candidate['applied_at']); ?>
                </div>

                <div class="detail-item" style="grid-column:1/-1;">
                    <strong>CV</strong>
                    <a class="btn btn-outline btn-sm" href="<?php echo htmlspecialchars($candidate['cv_url']); ?>" target="_blank">View CV</a>
                </div>
            </div>

            <div class="actions" style="margin-top:18px;">
                <a class="btn btn-primary" href="evaluate.php?id=<?php echo $candidate['id']; ?>">Evaluate Candidate</a>
                <a class="btn btn-outline" href="messages.php?student_user_id=<?php echo $candidate['student_user_id']; ?>">Send Message</a>
            </div>
        </div>
    </main>
</div>
</body>
</html>