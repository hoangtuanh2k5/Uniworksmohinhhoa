<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('student');

$user  = currentUser();
$flash = getFlash();

// Lấy danh sách company đã có job mà student này đã apply
$stmt = $pdo->prepare("
    SELECT DISTINCT u.id, c.company_name, u.avatar
    FROM companies c
    INNER JOIN users u ON c.user_id = u.id
    INNER JOIN jobs j ON j.company_id = c.id
    INNER JOIN applications a ON a.job_id = j.id
    INNER JOIN students s ON a.student_id = s.id
    WHERE s.user_id = ?
    ORDER BY c.company_name ASC
");
$stmt->execute([$user['id']]);
$companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

$receiver_id = (int)($_GET['receiver_id'] ?? 0);
$messages    = [];
$receiver    = null;

if ($receiver_id > 0) {
    $stmt = $pdo->prepare("SELECT id, full_name, avatar FROM users WHERE id = ?");
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
}

define('NOTIF_PAGE', 'messages');
require_once '../includes/notifications.php';
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
                <a href="/Uniworksmohinhhoa/student/messages.php" class="active">Messages</a>
                <a href="/Uniworksmohinhhoa/student/profile.php">Profile</a>
                <a href="/Uniworksmohinhhoa/student/report.php">Final Report</a>
                <a href="/Uniworksmohinhhoa/student/evaluation.php">Evaluation<?php if(!empty($notif['evaluations']) && $notif['evaluations']>0): ?><span class="notif-badge"><?= $notif['evaluations'] ?></span><?php endif; ?></a>
            </nav>
        </div>
        <div class="student-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">↩ Logout</a>
        </div>
    </aside>

    <main class="student-main">
        <div class="student-topbar">
            <div>
                <h1>Messages</h1>
                <p>Chat with companies directly.</p>
            </div>
        </div>

        <?php if ($flash): ?>
            <div class="flash <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:18px;">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <div class="msg-layout">
            <!-- Sidebar danh sách -->
            <div class="msg-list-panel">
                <div class="msg-list-header">Companies</div>
                <?php if (empty($companies)): ?>
                    <p class="msg-empty-hint">Apply to a job first to chat with companies.</p>
                <?php else: ?>
                    <?php foreach ($companies as $c): ?>
                        <?php $isActive = ($c['id'] == $receiver_id); ?>
                        <a href="/Uniworksmohinhhoa/student/messages.php?receiver_id=<?= $c['id'] ?>"
                           class="msg-contact <?= $isActive ? 'active' : '' ?>">
                            <div class="msg-contact-avatar">
                                <?php if (!empty($c['avatar'])): ?>
                                    <img src="/Uniworksmohinhhoa/uploads/avatars/<?= htmlspecialchars($c['avatar']) ?>" alt="">
                                <?php else: ?>
                                    <?= strtoupper(substr($c['company_name'], 0, 1)) ?>
                                <?php endif; ?>
                            </div>
                            <span class="msg-contact-name"><?= htmlspecialchars($c['company_name']) ?></span>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Khung chat -->
            <div class="msg-chat-panel">
                <?php if (!$receiver): ?>
                    <div class="msg-placeholder">
                        <div class="msg-placeholder-icon">💬</div>
                        <p>Select a company to start chatting.</p>
                    </div>
                <?php else: ?>
                    <div class="msg-chat-header">
                        <div class="msg-contact-avatar small">
                            <?php if (!empty($receiver['avatar'])): ?>
                                <img src="/Uniworksmohinhhoa/uploads/avatars/<?= htmlspecialchars($receiver['avatar']) ?>" alt="">
                            <?php else: ?>
                                <?= strtoupper(substr($receiver['full_name'], 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <strong><?= htmlspecialchars($receiver['full_name']) ?></strong>
                    </div>

                    <div class="msg-bubbles" id="msg-bubbles">
                        <?php if (empty($messages)): ?>
                            <p class="msg-empty-hint">No messages yet. Say hello!</p>
                        <?php else: ?>
                            <?php foreach ($messages as $msg): ?>
                                <?php $isMine = ($msg['sender_id'] == $user['id']); ?>
                                <div class="msg-bubble-wrap <?= $isMine ? 'mine' : 'theirs' ?>">
                                    <div class="msg-bubble <?= $isMine ? 'mine' : 'theirs' ?>">
                                        <?= nl2br(htmlspecialchars($msg['content'])) ?>
                                    </div>
                                    <div class="msg-time"><?= date('d M H:i', strtotime($msg['created_at'])) ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <form class="msg-form" action="/Uniworksmohinhhoa/actions/messages/send_message_action.php" method="POST">
                        <input type="hidden" name="receiver_id" value="<?= $receiver_id ?>">
                        <input type="hidden" name="redirect_to" value="/Uniworksmohinhhoa/student/messages.php?receiver_id=<?= $receiver_id ?>">
                        <textarea name="content" class="msg-input" placeholder="Type a message..." required rows="1"></textarea>
                        <button type="submit" class="msg-send-btn">Send ↑</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script>
var bubbles = document.getElementById('msg-bubbles');
if (bubbles) bubbles.scrollTop = bubbles.scrollHeight;
</script>

<?php include '../includes/footer.php'; ?>
