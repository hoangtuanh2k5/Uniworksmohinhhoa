<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

$previewMode = true;

function safeRedirect($path) {
    header("Location: " . $path);
    exit;
}

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

    $stmt = $pdo->prepare("
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
<div class="company-shell">
    <aside class="company-sidebar">
        <div>
            <div class="company-brand">
                <h2>Uniworks</h2>
                <p>Recruiter Portal</p>
            </div>

            <nav class="company-nav">
                <a href="dashboard.php">Dashboard</a>
                <a href="applications.php">Applicants</a>
                <a href="manage_job.php">Jobs</a>
                <a class="active" href="messages.php">Messages</a>
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
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

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
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script>
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