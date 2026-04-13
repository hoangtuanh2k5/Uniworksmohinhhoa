<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/Uniworksmohinhhoa/public/login.php');
}

$email = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    setFlash('error', 'Please enter email and password.');
    redirect('/Uniworksmohinhhoa/public/login.php');
}

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        setFlash('error', 'Invalid email or password.');
        redirect('/Uniworksmohinhhoa/public/login.php');
    }

    // Tăng độ an toàn cho session sau khi login
    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id' => $user['id'],
        'email' => $user['email'],
        'full_name' => $user['full_name'],
        'phone' => $user['phone'] ?? null,
        'role' => $user['role']
    ];

   if ($user['role'] === 'student') {
    $stmt = $pdo->prepare("
        SELECT id, student_code, major_id
        FROM students
        WHERE user_id = ?
        LIMIT 1
    ");
    $stmt->execute([$user['id']]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    // Chỉ ép vào profile nếu chưa có hồ sơ hoặc hồ sơ chưa đủ dữ liệu tối thiểu
    if (
        !$student ||
        empty($student['student_code']) ||
        empty($student['major_id'])
    ) {
        redirect('/Uniworksmohinhhoa/student/profile.php?setup=1');
    }

    redirect('/Uniworksmohinhhoa/student/dashboard.php');
}

    if ($user['role'] === 'company') {
        $stmt = $pdo->prepare("SELECT id FROM companies WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $company = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$company) {
            redirect('/Uniworksmohinhhoa/company/profile.php?setup=1');
        }

        redirect('/Uniworksmohinhhoa/company/dashboard.php');
    }

    if ($user['role'] === 'admin') {
        redirect('/Uniworksmohinhhoa/admin/dashboard.php');
    }

    setFlash('error', 'Invalid role.');
    redirect('/Uniworksmohinhhoa/public/login.php');

} catch (Exception $e) {
    setFlash('error', 'Login failed.');
    redirect('/Uniworksmohinhhoa/public/login.php');
}