<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/Uniworksmohinhhoa/public/login.php');
}

$email = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
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

    $_SESSION['user'] = [
        'id' => $user['id'],
        'email' => $user['email'],
        'full_name' => $user['full_name'],
        'phone' => $user['phone'],
        'role' => $user['role']
    ];

    if ($user['role'] === 'student') {
        redirect('/Uniworksmohinhhoa/student/dashboard.php');
    } elseif ($user['role'] === 'company') {
        redirect('/Uniworksmohinhhoa/company/dashboard.php');
    } else {
        redirect('/Uniworksmohinhhoa/admin/dashboard.php');
    }

} catch (Exception $e) {
    setFlash('error', 'Login failed.');
    redirect('/Uniworksmohinhhoa/public/login.php');
}