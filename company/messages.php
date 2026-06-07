<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('company');

$user = currentUser();
$flash = getFlash();

$stmt = $pdo->prepare("SELECT id, company_name FROM companies WHERE user_id = ?");
$stmt->execute([$user['id']]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company) {
    redirect('/Uniworksmohinhhoa/company/profile.php?setup=1');
}

$stmt = $pdo->query("
    SELECT u.id, u.full_name
    FROM students s
    INNER JOIN users u ON s.user_id = u.id
    ORDER BY u.full_name ASC
");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                <a href="/Uniworksmohinhhoa/company/applications.php">Applicants</a>
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
                <p>Chat with students directly.</p>
            </div>
        </div>

        <?php if ($flash): ?>
            <div class="flash <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:18px;">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <div class="company-message-layout">
            <div class="company-card">
                <h3 style="margin-bottom:14px;">Students</h3>
                <?php foreach ($students as $student): ?>
                    <p style="margin-bottom:10px;">
                        <a href="/Uniworksmohinhhoa/company/messages.php?receiver_id=<?= $student['id'] ?>">
                            <?= htmlspecialchars($student['full_name']) ?>
                        </a>
                    </p>
                <?php endforeach; ?>
            </div>

            <div class="company-card">
                <h3 style="margin-bottom:14px;">Conversation</h3>

                <?php if ($receiver_id <= 0): ?>
                    <p>Select a student to start chatting.</p>
                <?php else: ?>
                    <div class="company-conversation">
                        <?php if (empty($messages)): ?>
                            <p>No messages yet.</p>
                        <?php else: ?>
                            <?php foreach ($messages as $msg): ?>
                                <div class="company-message-bubble <?= $msg['sender_id'] == $user['id'] ? 'you' : '' ?>">
                                    <strong><?= $msg['sender_id'] == $user['id'] ? 'You' : 'Student' ?></strong>
                                    <p><?= nl2br(htmlspecialchars($msg['content'])) ?></p>
                                    <small><?= htmlspecialchars($msg['created_at']) ?></small>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <form action="/Uniworksmohinhhoa/actions/messages/send_message_action.php" method="POST">
                        <input type="hidden" name="receiver_id" value="<?= $receiver_id ?>">
                        <input type="hidden" name="redirect_to" value="/Uniworksmohinhhoa/company/messages.php?receiver_id=<?= $receiver_id ?>">

                        <div class="company-form-group">
                            <label>Message</label>
                            <textarea name="content" class="company-form-control" required></textarea>
                        </div>

                        <button type="submit" class="company-btn">Send Message</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>