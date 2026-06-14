<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireCompanyComplete($pdo);

$user  = currentUser();
$flash = getFlash();

$stmt = $pdo->prepare("SELECT id, company_name FROM companies WHERE user_id = ?");
$stmt->execute([$user['id']]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company) {
    redirect('/Uniworksmohinhhoa/company/profile.php?setup=1');
}

// Lấy danh sách student đã từng liên hệ HOẶC đã apply vào job của company
$stmt = $pdo->prepare("
    SELECT DISTINCT u.id, u.full_name, u.avatar
    FROM users u
    INNER JOIN students s ON s.user_id = u.id
    WHERE s.id IN (
        SELECT a.student_id FROM applications a
        INNER JOIN jobs j ON a.job_id = j.id
        WHERE j.company_id = ?
    )
    ORDER BY u.full_name ASC
");
$stmt->execute([$company['id']]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

<div class="company-shell">
    <aside class="company-sidebar">
        <div>
            <div class="company-brand">
                <div class="company-brand__logo">✦</div>
                <div class="company-brand__text">
                    <h3><?= htmlspecialchars($company['company_name']) ?></h3>
                    <p>Recruiter Portal</p>
                </div>
            </div>
            <nav class="company-nav">
                <a href="/Uniworksmohinhhoa/company/dashboard.php">Dashboard</a>
                <a href="/Uniworksmohinhhoa/company/applications.php">Applicants<?php if(!empty($notif['reports']) && $notif['reports']>0): ?><span class="notif-badge"><?= $notif['reports'] ?></span><?php endif; ?></a>
                <a href="/Uniworksmohinhhoa/company/manage_job.php">Jobs</a>
                <a href="/Uniworksmohinhhoa/company/messages.php" class="active">Messages</a>
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
                <h1>Messages</h1>
                <p>Chat directly with your applicants.</p>
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
                <div class="msg-list-header">Applicants</div>
                <?php if (empty($students)): ?>
                    <p class="msg-empty-hint">No applicants yet.</p>
                <?php else: ?>
                    <?php foreach ($students as $s): ?>
                        <?php $isActive = ($s['id'] == $receiver_id); ?>
                        <a href="/Uniworksmohinhhoa/company/messages.php?receiver_id=<?= $s['id'] ?>"
                           class="msg-contact <?= $isActive ? 'active' : '' ?>">
                            <div class="msg-contact-avatar">
                                <?php if (!empty($s['avatar'])): ?>
                                    <img src="/Uniworksmohinhhoa/uploads/avatars/<?= htmlspecialchars($s['avatar']) ?>" alt="">
                                <?php else: ?>
                                    <?= strtoupper(substr($s['full_name'], 0, 1)) ?>
                                <?php endif; ?>
                            </div>
                            <span class="msg-contact-name"><?= htmlspecialchars($s['full_name']) ?></span>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Khung chat -->
            <div class="msg-chat-panel">
                <?php if (!$receiver): ?>
                    <div class="msg-placeholder">
                        <div class="msg-placeholder-icon">💬</div>
                        <p>Select an applicant to start chatting.</p>
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
                        <input type="hidden" name="redirect_to" value="/Uniworksmohinhhoa/company/messages.php?receiver_id=<?= $receiver_id ?>">
                        <textarea name="content" class="msg-input" placeholder="Type a message..." required rows="1"></textarea>
                        <button type="submit" class="msg-send-btn">Send ↑</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script>
// Auto scroll to bottom of chat
var bubbles = document.getElementById('msg-bubbles');
if (bubbles) bubbles.scrollTop = bubbles.scrollHeight;
</script>

<?php include '../includes/footer.php'; ?>
