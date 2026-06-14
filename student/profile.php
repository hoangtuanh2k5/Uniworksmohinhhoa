<<<<<<< Updated upstream
=======
<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('student');

$user = currentUser();
$flash = getFlash();

$stmt = $pdo->prepare("
    SELECT s.*, u.full_name, u.email, u.avatar_url
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
                <div class="student-brand__logo">
                                <?php if (!empty($user['avatar_url'])): ?>
                                    <img src="/Uniworksmohinhhoa/<?= htmlspecialchars($user['avatar_url']) ?>" alt="avatar" style="width:34px;height:34px;min-width:34px;min-height:34px;max-width:34px;max-height:34px;object-fit:cover;border-radius:10px;display:block;">
                                <?php else: ?>
                                    ✦
                                <?php endif; ?>
                            </div>
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
                <a href="/Uniworksmohinhhoa/student/report.php">Final Report</a>
                <a href="/Uniworksmohinhhoa/student/evaluation.php">Evaluation</a>
            </nav>
        </div>

        <div class="student-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">↩ Logout</a>
        </div>
    </aside>

    <main class="student-main">
        <div class="student-topbar student-topbar--profile">
            <div></div>
            <a href="/Uniworksmohinhhoa/student/dashboard.php" class="student-btn">Back to Dashboard</a>
        </div>

        <?php if ($setupMode): ?>
            <div class="flash success" style="margin-bottom:18px;">
                Please complete your profile before continuing.
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
    <div class="flash success" style="margin-bottom:18px;">
        Profile updated successfully!
    </div>
<?php endif; ?>

        <?php if ($flash): ?>
            <div class="flash <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:18px;">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <section class="student-profile-hero">
            <div>
                <h1>Student Profile</h1>
                <p>
                    Complete and manage your student information so the system can match
                    you with internships more effectively.
                </p>
            </div>

            <div class="student-profile-hero__status">
                <span class="student-pill-success">Profile Active</span>
                <small>Keep your academic profile updated</small>
            </div>
        </section>

        <section class="student-profile-grid">
            <div class="student-form-card student-form-card--large">

                <!-- AVATAR UPLOAD -->
                <h2 style="margin-bottom:14px;">Profile Photo</h2>
                <div style="display:flex;align-items:center;gap:20px;margin-bottom:24px;">
                    <?php if (!empty($profile['avatar_url'])): ?>
                        <img src="/Uniworksmohinhhoa/<?= htmlspecialchars($profile['avatar_url']) ?>"
                             style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:2px solid #e1e3ee;">
                    <?php else: ?>
                        <div style="width:72px;height:72px;border-radius:50%;background:#ece8fb;display:flex;align-items:center;justify-content:center;font-size:28px;color:#7c6fcf;">✦</div>
                    <?php endif; ?>
                    <form action="/Uniworksmohinhhoa/actions/auth/upload_avatar_action.php" method="POST" enctype="multipart/form-data">
                        <label style="font-size:13px;font-weight:700;display:block;margin-bottom:6px;">Upload Photo (JPG/PNG/WEBP, max 2MB)</label>
                        <div style="display:flex;gap:10px;align-items:center;">
                            <input type="file" name="avatar" accept="image/*" class="student-form-control" style="height:auto;padding:6px 10px;">
                            <button type="submit" class="student-btn" style="white-space:nowrap;">Upload</button>
                        </div>
                    </form>
                </div>

                <hr style="border:none;border-top:1px solid #eceef6;margin:0 0 24px;">

                <h2>Edit Student Information</h2>                <p class="student-muted" style="margin-bottom:18px;">
                    Update the information used for your internship applications.
                </p>

                <form action="../actions/student/update_profile_action.php" method="POST">
                    <div class="student-form-row">
                        <div class="student-form-group">
                            <label>Full Name</label>
                            <input
                                type="text"
                                class="student-form-control"
                                value="<?= htmlspecialchars($profile['full_name'] ?? '') ?>"
                                disabled
                            >
                        </div>

                        <div class="student-form-group">
                            <label>Email</label>
                            <input
                                type="email"
                                class="student-form-control"
                                value="<?= htmlspecialchars($profile['email'] ?? '') ?>"
                                disabled
                            >
                        </div>
                    </div>

                    <div class="student-form-row">
                        <div class="student-form-group">
                            <label>Student Code</label>
                            <input
                                type="text"
                                name="student_code"
                                class="student-form-control"
                                value="<?= htmlspecialchars($profile['student_code'] ?? '') ?>"
                                required
                            >
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
                    </div>

                    <div class="student-form-row">
                        <div class="student-form-group">
                            <label>Class Name</label>
                            <input
                                type="text"
                                name="class_name"
                                class="student-form-control"
                                value="<?= htmlspecialchars($profile['class_name'] ?? '') ?>"
                            >
                        </div>

                        <div class="student-form-group">
                            <label>GPA</label>
                            <input
    type="number"
    step="0.01"
    min="0"
    max="10"
    name="gpa"
    class="student-form-control"
    value="<?= htmlspecialchars($profile['gpa'] ?? '') ?>"
>
                        </div>
                    </div>

                    <button type="submit" class="student-btn">Update Profile</button>
                </form>

                <hr style="border:none;border-top:1px solid #eceef6;margin:28px 0;">

                <h2 style="margin-bottom:6px;">Change Password</h2>
                <p class="student-muted" style="margin-bottom:18px;">Leave blank if you do not want to change your password.</p>

                <form action="/Uniworksmohinhhoa/actions/auth/change_password_action.php" method="POST">
                    <div class="student-form-row">
                        <div class="student-form-group">
                            <label>Current Password</label>
                            <input type="password" name="current_password" class="student-form-control" placeholder="Enter current password" required>
                        </div>
                    </div>
                    <div class="student-form-row">
                        <div class="student-form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" class="student-form-control" placeholder="At least 6 characters" required>
                        </div>
                        <div class="student-form-group">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_password" class="student-form-control" placeholder="Repeat new password" required>
                        </div>
                    </div>
                    <button type="submit" class="student-btn">Change Password</button>
                </form>
            </div>

            <div class="student-profile-side">
                <div class="student-profile-snapshot">
                    <div class="student-profile-snapshot__top">
                        <span class="student-muted">Student Snapshot</span>
                        <span class="student-pill-accent">Academic</span>
                    </div>

                    <h3><?= htmlspecialchars($profile['full_name'] ?: 'Your Name') ?></h3>
                    <p><?= htmlspecialchars($profile['student_code'] ?: 'Student Code') ?></p>
                </div>

                <div class="student-card">
                    <h3 style="margin-bottom:8px;">Academic Information</h3>
                    <p class="student-muted" style="margin-bottom:18px;">Your current study information.</p>

                    <div class="student-info-box student-info-box--purple">
                        <label>Student Code</label>
                        <p><?= htmlspecialchars($profile['student_code'] ?: '-') ?></p>
                    </div>

                    <div class="student-info-box student-info-box--yellow">
                        <label>Class Name</label>
                        <p><?= htmlspecialchars($profile['class_name'] ?: '-') ?></p>
                    </div>

                    <div class="student-info-box">
                        <label>GPA</label>
                        <p><?= htmlspecialchars($profile['gpa'] ?: '-') ?></p>
                    </div>
                </div>

                <div class="student-card">
                    <h3 style="margin-bottom:8px;">Account Information</h3>
                    <p class="student-muted" style="margin-bottom:18px;">Primary account details.</p>

                    <div class="student-info-box student-info-box--purple-soft">
                        <label>Full Name</label>
                        <p><?= htmlspecialchars($profile['full_name'] ?: '-') ?></p>
                    </div>

                    <div class="student-info-box student-info-box--yellow-soft">
                        <label>Email</label>
                        <p><?= htmlspecialchars($profile['email'] ?: '-') ?></p>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

<?php include '../includes/footer.php'; ?>
>>>>>>> Stashed changes
