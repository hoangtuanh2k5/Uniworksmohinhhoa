<?php
/**
 * notifications.php
 * - Tính badge counts cho từng role
 * - Tự động reset (last_seen_*) khi user đang ở trang tương ứng
 *
 * Mỗi trang khai báo constant NOTIF_PAGE trước khi include file này:
 *   define('NOTIF_PAGE', 'messages');   // hoặc 'evaluations', 'reports'
 */

$notif = [
    'messages'    => 0,
    'evaluations' => 0,
    'reports'     => 0,
];

if (!isLoggedIn() || !isset($pdo)) {
    return;
}

$u    = currentUser();
$uid  = (int)$u['id'];
$role = $u['role'];

// Trang hiện tại đang được xem (nếu có)
$currentPage = defined('NOTIF_PAGE') ? NOTIF_PAGE : '';

// -------------------------------------------------------
// AUTO-RESET: cập nhật last_seen_* khi user đang xem trang
// -------------------------------------------------------
if ($currentPage === 'messages') {
    $pdo->prepare("UPDATE users SET last_seen_messages = NOW() WHERE id = ?")
        ->execute([$uid]);
}
if ($currentPage === 'evaluations') {
    $pdo->prepare("UPDATE users SET last_seen_evaluations = NOW() WHERE id = ?")
        ->execute([$uid]);
}
if ($currentPage === 'reports') {
    $pdo->prepare("UPDATE users SET last_seen_reports = NOW() WHERE id = ?")
        ->execute([$uid]);
}

// -------------------------------------------------------
// STUDENT badges
// -------------------------------------------------------
if ($role === 'student') {
    $s = $pdo->prepare("
        SELECT COUNT(*) FROM messages
        WHERE receiver_id = ?
          AND created_at > IFNULL(
              (SELECT last_seen_messages FROM users WHERE id = ?), '2000-01-01')
    ");
    $s->execute([$uid, $uid]);
    $notif['messages'] = (int)$s->fetchColumn();

    $s = $pdo->prepare("
        SELECT COUNT(*) FROM evaluations e
        INNER JOIN internship_registrations ir ON e.registration_id = ir.id
        INNER JOIN applications a ON ir.application_id = a.id
        INNER JOIN students st ON a.student_id = st.id
        WHERE st.user_id = ?
          AND e.created_at > IFNULL(
              (SELECT last_seen_evaluations FROM users WHERE id = ?), '2000-01-01')
    ");
    $s->execute([$uid, $uid]);
    $notif['evaluations'] = (int)$s->fetchColumn();
}

// -------------------------------------------------------
// COMPANY badges
// -------------------------------------------------------
if ($role === 'company') {
    $s = $pdo->prepare("SELECT id FROM companies WHERE user_id = ?");
    $s->execute([$uid]);
    $cmp = $s->fetchColumn();

    if ($cmp) {
        $s = $pdo->prepare("
            SELECT COUNT(*) FROM messages
            WHERE receiver_id = ?
              AND created_at > IFNULL(
                  (SELECT last_seen_messages FROM users WHERE id = ?), '2000-01-01')
        ");
        $s->execute([$uid, $uid]);
        $notif['messages'] = (int)$s->fetchColumn();

        $s = $pdo->prepare("
            SELECT COUNT(*) FROM reports r
            INNER JOIN internship_registrations ir ON r.registration_id = ir.id
            INNER JOIN applications a ON ir.application_id = a.id
            INNER JOIN jobs j ON a.job_id = j.id
            WHERE j.company_id = ?
              AND r.submitted_at > IFNULL(
                  (SELECT last_seen_reports FROM users WHERE id = ?), '2000-01-01')
        ");
        $s->execute([$cmp, $uid]);
        $notif['reports'] = (int)$s->fetchColumn();
    }
}

// -------------------------------------------------------
// ADMIN badges
// -------------------------------------------------------
if ($role === 'admin') {
    $s = $pdo->prepare("
        SELECT COUNT(*) FROM reports
        WHERE submitted_at > IFNULL(
            (SELECT last_seen_reports FROM users WHERE id = ?), '2000-01-01')
    ");
    $s->execute([$uid]);
    $notif['reports'] = (int)$s->fetchColumn();

    $s = $pdo->prepare("
        SELECT COUNT(*) FROM messages
        WHERE created_at > IFNULL(
            (SELECT last_seen_messages FROM users WHERE id = ?), '2000-01-01')
    ");
    $s->execute([$uid]);
    $notif['messages'] = (int)$s->fetchColumn();
}
