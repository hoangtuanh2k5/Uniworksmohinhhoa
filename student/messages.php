<<<<<<< Updated upstream
=======
<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
requireRole('student');

$user  = currentUser();
$flash = getFlash();

// Danh sách company đã từng nhắn hoặc tất cả company
$stmt = $pdo->prepare("
    SELECT DISTINCT
        u.id,
        c.company_name AS display_name,
        u.avatar_url,
        (
            SELECT COUNT(*) FROM messages
            WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0
        ) AS unread
    FROM companies c
    INNER JOIN users u ON c.user_id = u.id
    ORDER BY c.company_name ASC
");
$stmt->execute([$user['id']]);
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$receiver_id = (int)($_GET['receiver_id'] ?? 0);
$messages    = [];
$receiver    = null;

if ($receiver_id > 0) {
    // Lấy info người nhận
    $stmt = $pdo->prepare("
        SELECT u.id, c.company_name AS display_name, u.avatar_url
        FROM users u
        INNER JOIN companies c ON c.user_id = u.id
        WHERE u.id = ?
    ");
    $stmt->execute([$receiver_id]);
    $receiver = $stmt->fetch(PDO::FETCH_ASSOC);

    // Lấy messages
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

// Tổng unread cho badge sidebar
$totalUnread = (int)$pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0")->execute([$user['id']]) ? 0 : 0;
$stmtU = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
$stmtU->execute([$user['id']]);
$totalUnread = (int)$stmtU->fetchColumn();

include '../includes/header.php';
?>
<style>
/* ── Chat shell ── */
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

/* ── Contact list ── */
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
.chat-contacts__list{
    overflow-y:auto;
    flex:1;
}
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
    flex-shrink:0;
    overflow:hidden;
}
.chat-contact-avatar img{
    width:100%; height:100%; object-fit:cover;
}
.chat-contact-info{ flex:1; min-width:0; }
.chat-contact-name{
    font-size:14px; font-weight:700; color:#17172b;
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.chat-contact-sub{
    font-size:12px; color:#8a8fa3; margin-top:2px;
}
.chat-unread-badge{
    min-width:20px; height:20px;
    border-radius:999px;
    background:#7c6fcf;
    color:#fff;
    font-size:11px; font-weight:800;
    display:flex; align-items:center; justify-content:center;
    padding:0 5px;
    flex-shrink:0;
}

/* ── Conversation ── */
.chat-conversation{
    display:flex;
    flex-direction:column;
    overflow:hidden;
}
.chat-conv-header{
    padding:16px 22px;
    border-bottom:1px solid #eceef6;
    display:flex;
    align-items:center;
    gap:14px;
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
.chat-conv-header-name{
    font-size:16px; font-weight:800; color:#17172b;
}
.chat-conv-header-sub{
    font-size:12px; color:#8a8fa3; margin-top:2px;
}
.chat-messages{
    flex:1; overflow-y:auto;
    padding:20px 22px;
    display:flex;
    flex-direction:column;
    gap:12px;
}
.chat-bubble-wrap{
    display:flex;
    align-items:flex-end;
    gap:8px;
}
.chat-bubble-wrap.you{
    flex-direction:row-reverse;
}
.chat-bubble-avatar{
    width:30px; height:30px;
    border-radius:10px;
    background:linear-gradient(135deg,#7c6fcf,#a99de8);
    color:#fff;
    display:flex; align-items:center; justify-content:center;
    font-size:12px; font-weight:800;
    flex-shrink:0;
    overflow:hidden;
}
.chat-bubble-avatar img{ width:100%; height:100%; object-fit:cover; }
.chat-bubble{
    max-width:68%;
    padding:11px 14px;
    border-radius:18px;
    font-size:14px;
    line-height:1.6;
    color:#17172b;
    word-break:break-word;
}
.chat-bubble-wrap:not(.you) .chat-bubble{
    background:#f1effe;
    border-bottom-left-radius:4px;
}
.chat-bubble-wrap.you .chat-bubble{
    background:#cfc6f6;
    border-bottom-right-radius:4px;
}
.chat-bubble-time{
    font-size:11px; color:#a0a3b1; margin-top:4px;
    text-align:right;
}
.chat-bubble-wrap:not(.you) .chat-bubble-time{
    text-align:left;
}

/* ── Input ── */
.chat-input-area{
    padding:14px 18px;
    border-top:1px solid #eceef6;
    background:#fafbff;
    display:flex;
    align-items:flex-end;
    gap:10px;
}
.chat-input-area textarea{
    flex:1;
    min-height:42px;
    max-height:120px;
    border:1px solid #e1e3ee;
    border-radius:14px;
    padding:10px 14px;
    font-size:14px;
    outline:none;
    resize:none;
    font-family:inherit;
    background:#fff;
    color:#17172b;
    transition:.2s;
}
.chat-input-area textarea:focus{
    border-color:#b8adff;
    box-shadow:0 0 0 3px rgba(180,169,255,.15);
}
.chat-send-btn{
    width:44px; height:44px;
    border:none; border-radius:13px;
    background:#cfc6f6;
    color:#17172b;
    font-size:20px;
    cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0;
    transition:.2s;
}
.chat-send-btn:hover{ background:#c1b3ff; transform:translateY(-1px); }

/* ── Empty state ── */
.chat-empty{
    flex:1; display:flex;
    flex-direction:column;
    align-items:center; justify-content:center;
    color:#a0a3b1; gap:10px;
}
.chat-empty-icon{ font-size:48px; opacity:.35; }

/* ── Sidebar badge ── */
.nav-badge{
    display:inline-flex; align-items:center; justify-content:center;
    min-width:18px; height:18px;
    border-radius:999px;
    background:#7c6fcf;
    color:#fff;
    font-size:10px; font-weight:800;
    margin-left:6px;
    padding:0 4px;
}

@media (max-width:900px){
    .chat-wrap{ grid-template-columns:1fr; height:auto; }
    .chat-contacts{ height:220px; }
}
</style>

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
                <a href="/Uniworksmohinhhoa/student/messages.php" class="active">
                    Messages<?php if ($totalUnread > 0): ?><span class="nav-badge"><?= $totalUnread ?></span><?php endif; ?>
                </a>
                <a href="/Uniworksmohinhhoa/student/profile.php">Profile</a>
                <a href="/Uniworksmohinhhoa/student/report.php">Final Report</a>
                <a href="/Uniworksmohinhhoa/student/evaluation.php">Evaluation</a>
            </nav>
        </div>
        <div class="student-sidebar__footer">
            <a href="/Uniworksmohinhhoa/public/logout.php">↩ Logout</a>
        </div>
    </aside>

    <main class="student-main">
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
                    <input type="text" class="chat-contacts__search" placeholder="Search companies..." id="contactSearch">
                </div>
                <div class="chat-contacts__list" id="contactList">
                    <?php foreach ($contacts as $c): ?>
                        <a href="/Uniworksmohinhhoa/student/messages.php?receiver_id=<?= $c['id'] ?>"
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
                                <div class="chat-contact-sub">Company</div>
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
                            <div class="chat-conv-header-sub">Company</div>
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
                                        <?php if ($isMe): ?>
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
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <form action="/Uniworksmohinhhoa/actions/messages/send_message_action.php" method="POST" class="chat-input-area" id="chatForm">
                        <input type="hidden" name="receiver_id" value="<?= $receiver_id ?>">
                        <input type="hidden" name="redirect_to" value="/Uniworksmohinhhoa/student/messages.php?receiver_id=<?= $receiver_id ?>">
                        <textarea name="content" placeholder="Type a message..." required id="msgInput"
                                  onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();this.form.submit();}"></textarea>
                        <button type="submit" class="chat-send-btn" title="Send">➤</button>
                    </form>

                <?php else: ?>
                    <div class="chat-empty" style="height:100%;">
                        <div class="chat-empty-icon">💬</div>
                        <div>Select a company to start chatting</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script>
// Auto scroll to bottom
var cm = document.getElementById('chatMessages');
if (cm) cm.scrollTop = cm.scrollHeight;

// Search contacts
document.getElementById('contactSearch')?.addEventListener('input', function(){
    var q = this.value.toLowerCase();
    document.querySelectorAll('.chat-contact-item').forEach(function(el){
        el.style.display = el.dataset.name.includes(q) ? '' : 'none';
    });
});
</script>

<?php include '../includes/footer.php'; ?>
>>>>>>> Stashed changes
