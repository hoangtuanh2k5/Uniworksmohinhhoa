<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

/*
|--------------------------------------------------------------------------
| PREVIEW MODE
|--------------------------------------------------------------------------
| true  = xem giao diện ngay, không cần login/db đủ dữ liệu
| false = chạy thật với session + database
*/
$previewMode = true;

function safeRedirect($path) {
    header("Location: " . $path);
    exit;
}

if ($previewMode) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // preview mode: không submit thật
    }

    $students = [
        ['user_id' => 101, 'full_name' => 'Thị Trâm Nguyễn Kiều', 'email' => 'alex.j@example.com'],
        ['user_id' => 102, 'full_name' => 'Thị Hạnh Hồng Nguyễn', 'email' => 'm.garcia@example.com'],
        ['user_id' => 103, 'full_name' => 'Anh Tú Hoàng Kim', 'email' => 'slee@example.com'],
        ['user_id' => 104, 'full_name' => 'Lê Yến Nhi', 'email' => 'jordan@example.com'],
    ];

    $studentUserId = isset($_GET['student_user_id']) ? (int)$_GET['student_user_id'] : 101;

    $selectedStudent = null;
    foreach ($students as $s) {
        if ((int)$s['user_id'] === $studentUserId) {
            $selectedStudent = $s;
            break;
        }
    }

    if (!$selectedStudent) {
        $selectedStudent = $students[0];
    }

    $messages = [
        [
            'sender_id' => 999,
            'receiver_id' => $selectedStudent['user_id'],
            'content' => 'Hello! Thank you for applying to our internship program.',
            'created_at' => '2026-04-11 09:15:00'
        ],
        [
            'sender_id' => $selectedStudent['user_id'],
            'receiver_id' => 999,
            'content' => 'Thank you for your message. I am very interested in this opportunity.',
            'created_at' => '2026-04-11 09:20:00'
        ],
        [
            'sender_id' => 999,
            'receiver_id' => $selectedStudent['user_id'],
            'content' => 'Great. Please prepare for a short interview discussion next week.',
            'created_at' => '2026-04-11 09:30:00'
        ],
    ];

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

    $studentUserId = isset($_GET['student_user_id']) ? (int)$_GET['student_user_id'] : 0;
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
    <style>
        .messages-layout {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 18px;
        }

        .student-list-card,
        .chat-card {
            min-height: 620px;
        }

        .student-list {
            display: grid;
            gap: 10px;
        }

        .student-item {
            display: block;
            padding: 14px 16px;
            border: 1px solid #ececf4;
            border-radius: 18px;
            background: #fff;
            transition: 0.2s ease;
        }

        .student-item:hover {
            background: #f8f7ff;
            border-color: #d7c7ff;
        }

        .student-item.active {
            background: #ece5ff;
            border-color: #cdbbff;
        }

        .student-item strong {
            display: block;
            font-size: 14px;
            margin-bottom: 4px;
            color: #17172b;
        }

        .student-item span {
            font-size: 12px;
            color: #7f8098;
        }

        .chat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid #ececf4;
            margin-bottom: 18px;
        }

        .chat-header h3 {
            margin: 0 0 4px;
            font-size: 20px;
            color: #17172b;
        }

        .chat-window {
            height: 360px;
            overflow-y: auto;
            padding-right: 6px;
            margin-bottom: 18px;
        }

        .msg-row {
            display: flex;
            margin-bottom: 12px;
        }

        .msg-row.you {
            justify-content: flex-end;
        }

        .msg-bubble {
            max-width: 72%;
            padding: 12px 14px;
            border-radius: 18px;
            box-shadow: 0 8px 18px rgba(17, 17, 28, 0.04);
            font-size: 14px;
            line-height: 1.6;
        }

        .msg-row.you .msg-bubble {
            background: #d7c7ff;
            color: #2b2149;
            border-bottom-right-radius: 6px;
        }

        .msg-row.them .msg-bubble {
            background: #ffffff;
            color: #17172b;
            border: 1px solid #ececf4;
            border-bottom-left-radius: 6px;
        }

        .msg-time {
            margin-top: 6px;
            font-size: 11px;
            color: #7f8098;
        }

        .empty-chat {
            height: 360px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px dashed #dddcef;
            border-radius: 18px;
            background: #fbfbfe;
            color: #7f8098;
            text-align: center;
            padding: 20px;
        }

        .chat-form textarea {
            min-height: 120px;
        }

        @media (max-width: 1100px) {
            .messages-layout {
                grid-template-columns: 1fr;
            }

            .student-list-card,
            .chat-card {
                min-height: auto;
            }

            .chat-window {
                height: auto;
                max-height: 360px;
            }

            .msg-bubble {
                max-width: 88%;
            }
        }
    </style>
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

        <div class="messages-layout">
            <div class="card student-list-card">
                <div class="card-header">
                    <div>
                        <h3>Students</h3>
                        <p>Choose a student to open the conversation.</p>
                    </div>
                </div>

                <?php if (empty($students)): ?>
                    <p class="small-muted">No student conversations yet.</p>
                <?php else: ?>
                    <div class="student-list">
                        <?php foreach ($students as $s): ?>
                            <a
                                class="student-item <?php echo (!empty($selectedStudent) && (int)$selectedStudent['user_id'] === (int)$s['user_id']) ? 'active' : ''; ?>"
                                href="messages.php?student_user_id=<?php echo $s['user_id']; ?>"
                            >
                                <strong><?php echo htmlspecialchars($s['full_name']); ?></strong>
                                <span><?php echo htmlspecialchars($s['email']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card chat-card">
                <?php if (empty($selectedStudent)): ?>
                    <div class="empty-chat">
                        <div>
                            <h3 style="margin-bottom:8px;">Select a student</h3>
                            <p class="small-muted">Choose a student on the left to view messages and start a conversation.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="chat-header">
                        <div>
                            <h3><?php echo htmlspecialchars($selectedStudent['full_name']); ?></h3>
                            <p class="small-muted"><?php echo htmlspecialchars($selectedStudent['email']); ?></p>
                        </div>
                    </div>

                    <div class="chat-window">
                        <?php if (empty($messages)): ?>
                            <div class="empty-chat">
                                <div>
                                    <h3 style="margin-bottom:8px;">No messages yet</h3>
                                    <p class="small-muted">Start the conversation by sending the first message below.</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($messages as $msg): ?>
                                <?php $isYou = ((int)$msg['sender_id'] === (int)$user['id']); ?>
                                <div class="msg-row <?php echo $isYou ? 'you' : 'them'; ?>">
                                    <div class="msg-bubble">
                                        <div><?php echo nl2br(htmlspecialchars($msg['content'])); ?></div>
                                        <div class="msg-time">
                                            <?php echo $isYou ? 'You' : 'Student'; ?> • <?php echo htmlspecialchars($msg['created_at']); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <form class="chat-form" action="messages.php?student_user_id=<?php echo $selectedStudent['user_id']; ?>" method="POST">
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
</body>
</html>