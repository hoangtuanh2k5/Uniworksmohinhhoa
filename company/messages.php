<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

<<<<<<< Updated upstream
$previewMode = true;
=======
$user  = currentUser();
$flash = getFlash();
>>>>>>> Stashed changes

function safeRedirect($path) {
    header("Location: " . $path);
    exit;
}

<<<<<<< Updated upstream
function makeInitials($name) {
    $parts = preg_split('/\s+/', trim((string)$name));
    $initials = '';
    foreach ($parts as $p) {
        if ($p !== '') {
            $initials .= strtoupper(substr($p, 0, 1));
        }
        if (strlen($initials) >= 2) {
            break;
        }
    }
    return $initials ?: 'NA';
}

if ($previewMode) {
    $students = [
        ['user_id' => 101, 'full_name' => 'Kieu Tram Nguyen', 'email' => 'alex.j@example.com'],
        ['user_id' => 102, 'full_name' => 'Hong Hanh Nguyen', 'email' => 'm.garcia@example.com'],
        ['user_id' => 103, 'full_name' => 'Kim Tu Hoang', 'email' => 'slee@example.com'],
        ['user_id' => 104, 'full_name' => 'Le Yen Thi', 'email' => 'jordan@example.com'],
        ['user_id' => 105, 'full_name' => 'Riley Tran', 'email' => 'riley@example.com'],
    ];

    $studentUserId = isset($_GET['student_user_id']) ? (int)($_GET['student_user_id']) : 101;

    $selectedStudent = null;
    foreach ($students as $s) {
        if ((int)$s['user_id'] === $studentUserId) {
            $selectedStudent = $s;
            break;
        }
    }

    if (!$selectedStudent) {
        $selectedStudent = $students[0];
        $studentUserId = 101;
    }

    $previewConversations = [
        101 => [
            ['sender_id' => 999, 'content' => 'Hi Kieu, thank you for applying for the Software Engineer Intern role at Uniworks.', 'created_at' => '2026-04-11 08:45:00'],
            ['sender_id' => 101, 'content' => 'Thank you for your message. I am very interested in this opportunity and would love to learn more about the role.', 'created_at' => '2026-04-11 08:52:00'],
            ['sender_id' => 999, 'content' => 'We reviewed your CV and noticed your web development project. Could you tell us more about your responsibilities in that project?', 'created_at' => '2026-04-11 09:00:00'],
            ['sender_id' => 101, 'content' => 'Sure. In my most recent project, I worked on the front-end interface and also connected several CRUD features using PHP and MySQL.', 'created_at' => '2026-04-11 09:05:00'],
            ['sender_id' => 999, 'content' => 'That sounds good. We are planning a short interview next Monday at 9:00 AM. Would that time work for you?', 'created_at' => '2026-04-11 09:12:00'],
            ['sender_id' => 101, 'content' => 'Yes, that works for me. I will be available at that time.', 'created_at' => '2026-04-11 09:18:00'],
        ],
        102 => [
            ['sender_id' => 999, 'content' => 'Hello Hanh, your application for the UI/UX Design Intern position is currently under review.', 'created_at' => '2026-04-10 10:10:00'],
            ['sender_id' => 102, 'content' => 'Thank you. Could you let me know which skills are most important for this role?', 'created_at' => '2026-04-10 10:16:00'],
            ['sender_id' => 999, 'content' => 'We mainly look for strong wireframing, prototyping, and user flow improvement skills. If you have a case study, feel free to send it to us.', 'created_at' => '2026-04-10 10:24:00'],
            ['sender_id' => 102, 'content' => 'I have a case study about redesigning an e-commerce website. I can send it later today.', 'created_at' => '2026-04-10 10:31:00'],
            ['sender_id' => 999, 'content' => 'Perfect. Our design team will review it and get back to you soon.', 'created_at' => '2026-04-10 10:35:00'],
        ],
        103 => [
            ['sender_id' => 999, 'content' => 'Hi Kim, congratulations. Your application for the Marketing Intern position has been approved.', 'created_at' => '2026-04-09 14:00:00'],
            ['sender_id' => 103, 'content' => 'Thank you very much. Is there anything I should prepare for the next step?', 'created_at' => '2026-04-09 14:07:00'],
            ['sender_id' => 999, 'content' => 'Next week we will send you an onboarding email. Please prepare your final CV, portfolio if available, and your possible internship start date.', 'created_at' => '2026-04-09 14:14:00'],
            ['sender_id' => 103, 'content' => 'I can start at the beginning of June. I will send the updated documents later today.', 'created_at' => '2026-04-09 14:20:00'],
        ],
        104 => [
            ['sender_id' => 999, 'content' => 'Hello Yen, we would like to let you know that our recruitment process for this round has now been closed.', 'created_at' => '2026-04-08 16:20:00'],
            ['sender_id' => 104, 'content' => 'I understand. Thank you for taking the time to review my application.', 'created_at' => '2026-04-08 16:28:00'],
            ['sender_id' => 999, 'content' => 'Your profile shows good potential in business analysis. If we open another role that is a better fit, we would be happy to keep your information on file.', 'created_at' => '2026-04-08 16:35:00'],
            ['sender_id' => 104, 'content' => 'Thank you. I really appreciate your feedback.', 'created_at' => '2026-04-08 16:40:00'],
        ],
        105 => [
            ['sender_id' => 999, 'content' => 'Hi Riley, we have received your application for the Data Analyst Intern role.', 'created_at' => '2026-04-07 11:00:00'],
            ['sender_id' => 105, 'content' => 'Thank you. I would like to ask whether this role focuses more on SQL or on dashboard and reporting work.', 'created_at' => '2026-04-07 11:06:00'],
            ['sender_id' => 999, 'content' => 'It includes both. SQL is required at a solid level, and experience with Power BI or Tableau would be a strong advantage.', 'created_at' => '2026-04-07 11:12:00'],
            ['sender_id' => 105, 'content' => 'I have built dashboards in Power BI for university assignments. I can send a sample file if needed.', 'created_at' => '2026-04-07 11:18:00'],
            ['sender_id' => 999, 'content' => 'That would be great. Please send it over, and our team will continue the review within the next one or two days.', 'created_at' => '2026-04-07 11:24:00'],
        ],
    ];

    $messages = $previewConversations[$studentUserId] ?? [];
    $user = ['id' => 999, 'role' => 'company'];
    $success = null;
    $error = null;
} else {
    if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'company') {
        safeRedirect('../public/login.php');
    }

    $user = $_SESSION['user'];

    $stmt = $pdo->prepare("SELECT * FROM companies WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$company) {
        if (function_exists('setFlash')) {
            setFlash('error', 'Company profile not found.');
        }
        safeRedirect('../public/login.php');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $receiverUserId = (int)($_POST['receiver_user_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');

        if (!$receiverUserId || $content === '') {
            if (function_exists('setFlash')) {
                setFlash('error', 'Message cannot be empty.');
            }
            safeRedirect('messages.php');
        }

        try {
            $stmt = $pdo->prepare("
                INSERT INTO messages (sender_id, receiver_id, content, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$user['id'], $receiverUserId, $content]);

            if (function_exists('setFlash')) {
                setFlash('success', 'Message sent successfully.');
            }
            safeRedirect('messages.php?student_user_id=' . $receiverUserId);
        } catch (Exception $e) {
            if (function_exists('setFlash')) {
                setFlash('error', 'Failed to send message.');
            }
            safeRedirect('messages.php');
        }
    }

    $studentUserId = isset($_GET['student_user_id']) ? (int)($_GET['student_user_id']) : 0;
    $selectedStudent = null;
    $messages = [];
=======
// Danh sách tất cả students
$stmt = $pdo->prepare("
    SELECT DISTINCT
        u.id,
        u.full_name AS display_name,
        u.avatar_url,
        (
            SELECT COUNT(*) FROM messages
            WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0
        ) AS unread
    FROM students s
    INNER JOIN users u ON s.user_id = u.id
    ORDER BY u.full_name ASC
");
$stmt->execute([$user['id']]);
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$receiver_id = (int)($_GET['receiver_id'] ?? 0);
$messages    = [];
$receiver    = null;
>>>>>>> Stashed changes

    $stmt = $pdo->prepare("
<<<<<<< Updated upstream
        SELECT DISTINCT u.id AS user_id, u.full_name, u.email
        FROM applications a
        JOIN students s ON a.student_id = s.id
        JOIN users u ON s.user_id = u.id
        JOIN jobs j ON a.job_id = j.id
        WHERE j.company_id = ?
        ORDER BY u.full_name ASC
    ");
    $stmt->execute([$company['id']]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($studentUserId > 0) {
        $stmt = $pdo->prepare("
            SELECT u.id AS user_id, u.full_name, u.email
            FROM users u
            JOIN students s ON s.user_id = u.id
            JOIN applications a ON a.student_id = s.id
            JOIN jobs j ON a.job_id = j.id
            WHERE u.id = ? AND j.company_id = ?
            LIMIT 1
        ");
        $stmt->execute([$studentUserId, $company['id']]);
        $selectedStudent = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($selectedStudent) {
            $stmt = $pdo->prepare("
                SELECT *
                FROM messages
                WHERE (sender_id = ? AND receiver_id = ?)
                   OR (sender_id = ? AND receiver_id = ?)
                ORDER BY created_at ASC
            ");
            $stmt->execute([$user['id'], $studentUserId, $studentUserId, $user['id']]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    $success = function_exists('getFlash') ? getFlash('success') : null;
    $error = function_exists('getFlash') ? getFlash('error') : null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Messages</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
=======
        SELECT u.id, u.full_name AS display_name, u.avatar_url
        FROM users u WHERE u.id = ?
    ");
    $stmt->execute([$receiver_id]);
    $receiver = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT * FROM messages
        WHERE (sender_id = ? AND receiver_id = ?)
           OR (sender_id = ? AND receiver_id = ?)
        ORDER BY id ASC
    ");
    $stmt->execute([$user['id'], $receiver_id, $receiver_id, $user['id']]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Đánh dấu đã đọc
    $pdo->prepare("
        UPDATE messages SET is_read = 1
        WHERE sender_id = ? AND receiver_id = ? AND is_read = 0
    ")->execute([$receiver_id, $user['id']]);
}

// Tổng unread badge
$stmtU = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
$stmtU->execute([$user['id']]);
$totalUnread = (int)$stmtU->fetchColumn();

include '../includes/header.php';
?>
<style>
.chat-wrap{
    display:grid;
    grid-template-columns:300px 1fr;
    gap:0;
    height:calc(100vh - 72px - 52px - 48px);
    min-height:520px;
    background:#fff;
    border-radius:24px;
    overflow:hidden;
    box-shadow:0 12px 32px rgba(31,34,51,.08);
    border:1px solid #eceef6;
}
.chat-contacts{
    border-right:1px solid #eceef6;
    display:flex;
    flex-direction:column;
    overflow:hidden;
}
.chat-contacts__header{
    padding:20px 18px 14px;
    border-bottom:1px solid #eceef6;
}
.chat-contacts__header h2{
    margin:0 0 12px;
    font-size:18px;
    font-weight:800;
    color:#17172b;
}
.chat-contacts__search{
    width:100%;
    height:36px;
    border:1px solid #e1e3ee;
    border-radius:10px;
    padding:0 12px;
    font-size:13px;
    outline:none;
    background:#f8f9fc;
    font-family:inherit;
}
.chat-contacts__list{ overflow-y:auto; flex:1; }
.chat-contact-item{
    display:flex;
    align-items:center;
    gap:12px;
    padding:14px 18px;
    cursor:pointer;
    text-decoration:none;
    border-bottom:1px solid #f4f4f9;
    transition:.15s ease;
}
.chat-contact-item:hover{ background:#f8f7ff; }
.chat-contact-item.active{ background:#ede9ff; }
.chat-contact-avatar{
    width:42px; height:42px;
    border-radius:13px;
    background:linear-gradient(135deg,#7c6fcf,#a99de8);
    color:#fff;
    display:flex; align-items:center; justify-content:center;
    font-size:17px; font-weight:800;
    flex-shrink:0; overflow:hidden;
}
.chat-contact-avatar img{ width:100%; height:100%; object-fit:cover; }
.chat-contact-info{ flex:1; min-width:0; }
.chat-contact-name{
    font-size:14px; font-weight:700; color:#17172b;
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.chat-contact-sub{ font-size:12px; color:#8a8fa3; margin-top:2px; }
.chat-unread-badge{
    min-width:20px; height:20px;
    border-radius:999px;
    background:#7c6fcf; color:#fff;
    font-size:11px; font-weight:800;
    display:flex; align-items:center; justify-content:center;
    padding:0 5px; flex-shrink:0;
}
.chat-conversation{ display:flex; flex-direction:column; overflow:hidden; }
.chat-conv-header{
    padding:16px 22px;
    border-bottom:1px solid #eceef6;
    display:flex; align-items:center; gap:14px;
    background:#fafbff;
}
.chat-conv-header-avatar{
    width:40px; height:40px;
    border-radius:12px;
    background:linear-gradient(135deg,#7c6fcf,#a99de8);
    color:#fff;
    display:flex; align-items:center; justify-content:center;
    font-size:16px; font-weight:800;
    overflow:hidden; flex-shrink:0;
}
.chat-conv-header-avatar img{ width:100%; height:100%; object-fit:cover; }
.chat-conv-header-name{ font-size:16px; font-weight:800; color:#17172b; }
.chat-conv-header-sub{ font-size:12px; color:#8a8fa3; margin-top:2px; }
.chat-messages{
    flex:1; overflow-y:auto;
    padding:20px 22px;
    display:flex; flex-direction:column; gap:12px;
}
.chat-bubble-wrap{ display:flex; align-items:flex-end; gap:8px; }
.chat-bubble-wrap.you{ flex-direction:row-reverse; }
.chat-bubble-avatar{
    width:30px; height:30px;
    border-radius:10px;
    background:linear-gradient(135deg,#7c6fcf,#a99de8);
    color:#fff;
    display:flex; align-items:center; justify-content:center;
    font-size:12px; font-weight:800;
    flex-shrink:0; overflow:hidden;
}
.chat-bubble-avatar img{ width:100%; height:100%; object-fit:cover; }
.chat-bubble{
    max-width:68%;
    padding:11px 14px;
    border-radius:18px;
    font-size:14px; line-height:1.6;
    color:#17172b; word-break:break-word;
}
.chat-bubble-wrap:not(.you) .chat-bubble{
    background:#f1effe;
    border-bottom-left-radius:4px;
}
.chat-bubble-wrap.you .chat-bubble{
    background:#cfc6f6;
    border-bottom-right-radius:4px;
}
.chat-bubble-time{ font-size:11px; color:#a0a3b1; margin-top:4px; text-align:right; }
.chat-bubble-wrap:not(.you) .chat-bubble-time{ text-align:left; }
.chat-input-area{
    padding:14px 18px;
    border-top:1px solid #eceef6;
    background:#fafbff;
    display:flex; align-items:flex-end; gap:10px;
}
.chat-input-area textarea{
    flex:1; min-height:42px; max-height:120px;
    border:1px solid #e1e3ee; border-radius:14px;
    padding:10px 14px; font-size:14px;
    outline:none; resize:none; font-family:inherit;
    background:#fff; color:#17172b; transition:.2s;
}
.chat-input-area textarea:focus{
    border-color:#b8adff;
    box-shadow:0 0 0 3px rgba(180,169,255,.15);
}
.chat-send-btn{
    width:44px; height:44px;
    border:none; border-radius:13px;
    background:#cfc6f6; color:#17172b;
    font-size:20px; cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0; transition:.2s;
}
.chat-send-btn:hover{ background:#c1b3ff; transform:translateY(-1px); }
.chat-empty{
    flex:1; display:flex;
    flex-direction:column;
    align-items:center; justify-content:center;
    color:#a0a3b1; gap:10px;
}
.chat-empty-icon{ font-size:48px; opacity:.35; }
.nav-badge{
    display:inline-flex; align-items:center; justify-content:center;
    min-width:18px; height:18px;
    border-radius:999px;
    background:#7c6fcf; color:#fff;
    font-size:10px; font-weight:800;
    margin-left:6px; padding:0 4px;
}
@media (max-width:900px){
    .chat-wrap{ grid-template-columns:1fr; height:auto; }
    .chat-contacts{ height:220px; }
}
</style>

>>>>>>> Stashed changes
<div class="company-shell">
    <aside class="company-sidebar">
        <div>
            <div class="company-brand">
<<<<<<< Updated upstream
                <h2>Uniworks</h2>
                <p>Recruiter Portal</p>
=======
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
>>>>>>> Stashed changes
            </div>
            <nav class="company-nav">
<<<<<<< Updated upstream
                <a href="dashboard.php">Dashboard</a>
                <a href="applications.php">Applicants</a>
                <a href="manage_job.php">Jobs</a>
                <a class="active" href="messages.php">Messages</a>
                <a href="profile.php">Profile</a>
            </nav>
        </div>

        <div class="company-signout">
            <a href="../public/logout.php">Sign Out</a>
=======
                <a href="/Uniworksmohinhhoa/company/dashboard.php">Dashboard</a>
                <a href="/Uniworksmohinhhoa/company/applications.php">Applicants</a>
                <a href="/Uniworksmohinhhoa/company/manage_job.php">Jobs</a>
                <a href="/Uniworksmohinhhoa/company/internship_history.php">History</a>
                <a href="/Uniworksmohinhhoa/company/evaluations.php">Evaluations</a>
                <a href="/Uniworksmohinhhoa/company/messages.php" class="active">
                    Messages<?php if ($totalUnread > 0): ?><span class="nav-badge"><?= $totalUnread ?></span><?php endif; ?>
                </a>
                <a href="/Uniworksmohinhhoa/company/profile.php">Profile</a>
            </nav>
        </div>
        <div class="company-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">↩ Logout</a>
>>>>>>> Stashed changes
        </div>
    </aside>

    <main class="company-main">
<<<<<<< Updated upstream
        <div class="topbar">
            <div></div>
            <div class="topbar-actions">
                <a class="btn btn-primary" href="dashboard.php">Back to Dashboard</a>
            </div>
        </div>

        <?php if (!empty($success)): ?>
            <div class="flash success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="flash error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <h1 class="page-title">Messages</h1>
        <p class="page-subtitle">Communicate with students who applied to your jobs.</p>

        <div class="stats-3">
            <div class="stat-card yellow">
                <span class="stat-pill">Inbox</span>
                <h4>Total Students</h4>
                <div class="stat-value"><?php echo count($students); ?></div>
            </div>

            <div class="stat-card purple">
                <span class="stat-pill">Live</span>
                <h4>Selected Chat</h4>
                <div class="stat-value"><?php echo !empty($selectedStudent) ? '1' : '0'; ?></div>
            </div>

            <div class="stat-card yellow">
                <span class="stat-pill">Thread</span>
                <h4>Total Messages</h4>
                <div class="stat-value"><?php echo count($messages); ?></div>
            </div>
        </div>

        <div class="detail-grid">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3>Students</h3>
                        <p>Select a student to open the conversation.</p>
                    </div>
                </div>

                <div id="student-list-scroll" style="max-height: 520px; overflow-y: auto; padding-right: 4px;">
                    <?php if (empty($students)): ?>
                        <p class="small-muted">No student conversations yet.</p>
                    <?php else: ?>
                        <?php foreach ($students as $s): ?>
                            <?php $isActive = (!empty($selectedStudent) && (int)$selectedStudent['user_id'] === (int)$s['user_id']); ?>
                            <div class="detail-item" style="margin-bottom:12px; <?php echo $isActive ? 'background:#ece5ff;border-color:#cdbbff;' : ''; ?>">
                                <a class="student-chat-link" href="messages.php?student_user_id=<?php echo $s['user_id']; ?>" style="display:block;">
                                    <div class="applicant-cell">
                                        <div class="avatar"><?php echo htmlspecialchars(makeInitials($s['full_name'])); ?></div>
                                        <div class="applicant-meta">
                                            <strong><?php echo htmlspecialchars($s['full_name']); ?></strong>
                                            <span><?php echo htmlspecialchars($s['email']); ?></span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <?php if (empty($selectedStudent)): ?>
                    <div class="detail-item">
                        <strong>Select a student</strong>
                        Choose a student on the left to view messages and continue the conversation.
                    </div>
                <?php else: ?>
                    <div class="card-header">
                        <div>
                            <h3><?php echo htmlspecialchars($selectedStudent['full_name']); ?></h3>
                            <p><?php echo htmlspecialchars($selectedStudent['email']); ?></p>
                        </div>
                    </div>

                    <div id="chat-scroll-box" style="max-height:420px; overflow-y:auto; padding-right:4px; margin-bottom:18px;">
                        <?php if (empty($messages)): ?>
                            <div class="detail-item">
                                <strong>No messages yet</strong>
                                Start the conversation by sending the first message below.
                            </div>
                        <?php else: ?>
                            <?php foreach ($messages as $msg): ?>
                                <?php $isYou = ((int)$msg['sender_id'] === (int)$user['id']); ?>
                                <div style="display:flex; justify-content:<?php echo $isYou ? 'flex-end' : 'flex-start'; ?>; margin-bottom:12px;">
                                    <div
                                        class="message-box"
                                        style="
                                            max-width:72%;
                                            margin-bottom:0;
                                            <?php echo $isYou
                                                ? 'background:#ece5ff;border-color:#cdbbff;border-bottom-right-radius:8px;'
                                                : 'background:#faf6e8;border-color:#f3d86e;border-bottom-left-radius:8px;';
                                            ?>
                                        "
                                    >
                                        <div class="message-meta">
                                            <?php echo $isYou ? 'You' : htmlspecialchars($selectedStudent['full_name']); ?> • <?php echo htmlspecialchars($msg['created_at']); ?>
                                        </div>
                                        <div><?php echo nl2br(htmlspecialchars($msg['content'])); ?></div>
=======
        <?php if ($flash): ?>
            <div class="flash <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:14px;">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <div class="chat-wrap">
            <!-- Contact list -->
            <div class="chat-contacts">
                <div class="chat-contacts__header">
                    <h2>Messages</h2>
                    <input type="text" class="chat-contacts__search" placeholder="Search students..." id="contactSearch">
                </div>
                <div class="chat-contacts__list" id="contactList">
                    <?php foreach ($contacts as $c): ?>
                        <a href="/Uniworksmohinhhoa/company/messages.php?receiver_id=<?= $c['id'] ?>"
                           class="chat-contact-item <?= $receiver_id === (int)$c['id'] ? 'active' : '' ?>"
                           data-name="<?= htmlspecialchars(strtolower($c['display_name'])) ?>">
                            <div class="chat-contact-avatar">
                                <?php if (!empty($c['avatar_url'])): ?>
                                    <img src="/Uniworksmohinhhoa/<?= htmlspecialchars($c['avatar_url']) ?>">
                                <?php else: ?>
                                    <?= strtoupper(substr($c['display_name'], 0, 1)) ?>
                                <?php endif; ?>
                            </div>
                            <div class="chat-contact-info">
                                <div class="chat-contact-name"><?= htmlspecialchars($c['display_name']) ?></div>
                                <div class="chat-contact-sub">Student</div>
                            </div>
                            <?php if ((int)$c['unread'] > 0): ?>
                                <div class="chat-unread-badge"><?= $c['unread'] ?></div>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Conversation -->
            <div class="chat-conversation">
                <?php if ($receiver_id > 0 && $receiver): ?>
                    <div class="chat-conv-header">
                        <div class="chat-conv-header-avatar">
                            <?php if (!empty($receiver['avatar_url'])): ?>
                                <img src="/Uniworksmohinhhoa/<?= htmlspecialchars($receiver['avatar_url']) ?>">
                            <?php else: ?>
                                <?= strtoupper(substr($receiver['display_name'], 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div class="chat-conv-header-name"><?= htmlspecialchars($receiver['display_name']) ?></div>
                            <div class="chat-conv-header-sub">Student</div>
                        </div>
                    </div>

                    <div class="chat-messages" id="chatMessages">
                        <?php if (empty($messages)): ?>
                            <div class="chat-empty">
                                <div class="chat-empty-icon">💬</div>
                                <div>No messages yet. Say hello!</div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($messages as $msg): ?>
                                <?php $isMe = (int)$msg['sender_id'] === (int)$user['id']; ?>
                                <div class="chat-bubble-wrap <?= $isMe ? 'you' : '' ?>">
                                    <div class="chat-bubble-avatar">
                                        <?php if ($isMe && !empty($user['avatar_url'])): ?>
                                            <img src="/Uniworksmohinhhoa/<?= htmlspecialchars($user['avatar_url']) ?>">
                                        <?php elseif ($isMe): ?>
                                            <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                                        <?php elseif (!empty($receiver['avatar_url'])): ?>
                                            <img src="/Uniworksmohinhhoa/<?= htmlspecialchars($receiver['avatar_url']) ?>">
                                        <?php else: ?>
                                            <?= strtoupper(substr($receiver['display_name'], 0, 1)) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="chat-bubble"><?= nl2br(htmlspecialchars($msg['content'])) ?></div>
                                        <div class="chat-bubble-time"><?= date('M d, H:i', strtotime($msg['created_at'])) ?></div>
>>>>>>> Stashed changes
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

<<<<<<< Updated upstream
                    <form action="messages.php?student_user_id=<?php echo $selectedStudent['user_id']; ?>" method="POST" id="message-form">
                        <input type="hidden" name="receiver_user_id" value="<?php echo $selectedStudent['user_id']; ?>">

                        <div class="form-group">
                            <label>New Message</label>
                            <textarea name="content" required placeholder="Write your message here..."></textarea>
                        </div>

                        <?php if ($previewMode): ?>
                            <button class="btn btn-primary" type="button">Send Message</button>
                        <?php else: ?>
                            <button class="btn btn-primary" type="submit">Send Message</button>
                        <?php endif; ?>
=======
                    <form action="/Uniworksmohinhhoa/actions/messages/send_message_action.php" method="POST" class="chat-input-area">
                        <input type="hidden" name="receiver_id" value="<?= $receiver_id ?>">
                        <input type="hidden" name="redirect_to" value="/Uniworksmohinhhoa/company/messages.php?receiver_id=<?= $receiver_id ?>">
                        <textarea name="content" placeholder="Type a message..." required
                                  onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();this.form.submit();}"></textarea>
                        <button type="submit" class="chat-send-btn" title="Send">➤</button>
>>>>>>> Stashed changes
                    </form>

                <?php else: ?>
                    <div class="chat-empty" style="height:100%;">
                        <div class="chat-empty-icon">💬</div>
                        <div>Select a student to start chatting</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script>
<<<<<<< Updated upstream
document.addEventListener('DOMContentLoaded', function () {
    const studentList = document.getElementById('student-list-scroll');
    const chatBox = document.getElementById('chat-scroll-box');
    const studentLinks = document.querySelectorAll('.student-chat-link');
    const messageForm = document.getElementById('message-form');

    const PAGE_SCROLL_KEY = 'company_messages_page_scroll';
    const STUDENT_SCROLL_KEY = 'company_messages_student_scroll';

    const savedPageScroll = sessionStorage.getItem(PAGE_SCROLL_KEY);
    if (savedPageScroll !== null) {
        window.scrollTo(0, parseInt(savedPageScroll, 10));
        sessionStorage.removeItem(PAGE_SCROLL_KEY);
    }

    if (studentList) {
        const savedStudentScroll = sessionStorage.getItem(STUDENT_SCROLL_KEY);
        if (savedStudentScroll !== null) {
            studentList.scrollTop = parseInt(savedStudentScroll, 10);
        }

        studentList.addEventListener('scroll', function () {
            sessionStorage.setItem(STUDENT_SCROLL_KEY, studentList.scrollTop);
        });
    }

    studentLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            sessionStorage.setItem(PAGE_SCROLL_KEY, String(window.scrollY));
            if (studentList) {
                sessionStorage.setItem(STUDENT_SCROLL_KEY, String(studentList.scrollTop));
            }
        });
    });

    if (messageForm) {
        messageForm.addEventListener('submit', function() {
            sessionStorage.setItem(PAGE_SCROLL_KEY, String(window.scrollY));
            if (studentList) {
                sessionStorage.setItem(STUDENT_SCROLL_KEY, String(studentList.scrollTop));
            }
        });
    }

    if (chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }
});
</script>
</body>
</html>
=======
var cm = document.getElementById('chatMessages');
if (cm) cm.scrollTop = cm.scrollHeight;

document.getElementById('contactSearch')?.addEventListener('input', function(){
    var q = this.value.toLowerCase();
    document.querySelectorAll('.chat-contact-item').forEach(function(el){
        el.style.display = el.dataset.name.includes(q) ? '' : 'none';
    });
});
</script>

<?php include '../includes/footer.php'; ?>
>>>>>>> Stashed changes
