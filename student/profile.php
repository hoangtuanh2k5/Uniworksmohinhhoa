<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('student');

$user = currentUser();
$flash = getFlash();

$stmt = $pdo->prepare("
    SELECT s.*, u.full_name, u.email
    FROM users u
    LEFT JOIN students s ON u.id = s.user_id
    WHERE u.id = ?
");
$stmt->execute([$user['id']]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

$setupMode = isset($_GET['setup']);

include '../includes/header.php';
?>

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
                <a href="/Uniworksmohinhhoa/student/messages.php">Messages</a>
                <a href="/Uniworksmohinhhoa/student/profile.php" class="active">Profile</a>
            </nav>
        </div>

        <div class="student-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">↩ Log Out</a>
        </div>
    </aside>

    <main class="student-main">
        <div class="student-topbar">
            <div>
                <h1>Student Profile</h1>
                <p>Complete and manage your student information.</p>
            </div>
        </div>

        <div class="student-form-card">
            <?php if ($setupMode): ?>
                <div class="flash success" style="margin-bottom:18px;">Please complete your profile before continuing.</div>
            <?php endif; ?>

            <?php if ($flash): ?>
                <div class="flash <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:18px;">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <form action="../actions/student/update_profile_action.php" method="POST">
                <div class="student-form-group">
                    <label>Full Name</label>
                    <input type="text" class="student-form-control" value="<?= htmlspecialchars($profile['full_name'] ?? '') ?>" disabled>
                </div>

                <div class="student-form-group">
                    <label>Email</label>
                    <input type="email" class="student-form-control" value="<?= htmlspecialchars($profile['email'] ?? '') ?>" disabled>
                </div>

                <div class="student-form-group">
                    <label>Student Code</label>
                    <input type="text" name="student_code" class="student-form-control" value="<?= htmlspecialchars($profile['student_code'] ?? '') ?>" required>
                </div>

                <div class="student-form-group">
                    <label>Major</label>
                    <select name="major_id" class="student-form-control" required>
                        <option value="">Select major</option>
                        <option value="1" <?= (($profile['major_id'] ?? '') == 1) ? 'selected' : '' ?>>Information Systems</option>
                        <option value="2" <?= (($profile['major_id'] ?? '') == 2) ? 'selected' : '' ?>>Computer Science</option>
                        <option value="3" <?= (($profile['major_id'] ?? '') == 3) ? 'selected' : '' ?>>Business Administration</option>
                    </select>
                </div>

                <div class="student-form-group">
                    <label>Class Name</label>
                    <input type="text" name="class_name" class="student-form-control" value="<?= htmlspecialchars($profile['class_name'] ?? '') ?>">
                </div>

                <div class="student-form-group">
                    <label>GPA</label>
                    <input type="number" step="0.01" name="gpa" class="student-form-control" value="<?= htmlspecialchars($profile['gpa'] ?? '') ?>">
                </div>

                <button type="submit" class="student-btn">Save Profile</button>
            </form>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>