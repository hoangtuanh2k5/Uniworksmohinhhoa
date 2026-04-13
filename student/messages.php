<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('student');

$user = currentUser();
$flash = getFlash();

$stmt = $pdo->query("
    SELECT u.id, c.company_name
    FROM companies c
    INNER JOIN users u ON c.user_id = u.id
    ORDER BY c.company_name ASC
");
$companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

$receiver_id = (int)($_GET['receiver_id'] ?? 0);
$messages = [];

if ($receiver_id > 0) {
    $stmt = $pdo->prepare("
        SELECT *
        FROM messages
        WHERE (sender_id = ? AND receiver_id = ?)
           OR (sender_id = ? AND receiver_id = ?)
        ORDER BY id ASC
    ");
    $stmt->execute([$user['id'], $receiver_id, $receiver_id, $user['id']]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

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
            </nav>
        </div>

        <div class="student-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">↩ Sign Out</a>
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

        <div class="student-message-layout">
            <div class="student-card">
                <h3 style="margin-bottom:14px;">Companies</h3>
                <?php foreach ($companies as $company): ?>
                    <p style="margin-bottom:10px;">
                        <a href="/Uniworksmohinhhoa/student/messages.php?receiver_id=<?= $company['id'] ?>">
                            <?= htmlspecialchars($company['company_name']) ?>
                        </a>
                    </p>
                <?php endforeach; ?>
            </div>

            <div class="student-card">
                <h3 style="margin-bottom:14px;">Conversation</h3>

                <?php if ($receiver_id <= 0): ?>
                    <p>Select a company to start chatting.</p>
                <?php else: ?>
                    <div class="student-conversation">
                        <?php if (empty($messages)): ?>
                            <p>No messages yet.</p>
                        <?php else: ?>
                            <?php foreach ($messages as $msg): ?>
                                <div class="student-message-bubble <?= $msg['sender_id'] == $user['id'] ? 'you' : '' ?>">
                                    <strong><?= $msg['sender_id'] == $user['id'] ? 'You' : 'Company' ?></strong>
                                    <p><?= nl2br(htmlspecialchars($msg['content'])) ?></p>
                                    <small><?= htmlspecialchars($msg['created_at']) ?></small>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <form action="/Uniworksmohinhhoa/actions/messages/send_message_action.php" method="POST">
                        <input type="hidden" name="receiver_id" value="<?= $receiver_id ?>">
                        <input type="hidden" name="redirect_to" value="/Uniworksmohinhhoa/student/messages.php?receiver_id=<?= $receiver_id ?>">

                        <div class="student-form-group">
                            <label>Message</label>
                            <textarea name="content" class="student-form-control" required></textarea>
                        </div>

                        <button type="submit" class="student-btn">Send Message</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>