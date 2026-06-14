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
<<<<<<< Updated upstream
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
=======
        'id'         => $user['id'],
        'email'      => $user['email'],
        'full_name'  => $user['full_name'],
        'phone'      => $user['phone'] ?? null,
        'avatar_url' => $user['avatar_url'] ?? null,
        'role'       => $user['role']
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

    if (
        !$student ||
        empty($student['student_code']) ||
        empty($student['major_id'])
    ) {
        redirect('/Uniworksmohinhhoa/student/profile.php?setup=1');
    }

    setFlash('success', 'Login successful! Welcome back, ' . $user['full_name'] . '.');
    $_SESSION['redirect_to'] = '/Uniworksmohinhhoa/student/dashboard.php';
    $_SESSION['prefill_email'] = $user['email'];
    redirect('/Uniworksmohinhhoa/public/login.php');
}

    if ($user['role'] === 'company') {
        $stmt = $pdo->prepare("SELECT id, status FROM companies WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $company = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$company) {
            redirect('/Uniworksmohinhhoa/company/profile.php?setup=1');
        }

        if ($company['status'] === 'pending') {
            session_destroy();
            setFlash('error', 'Your company account is pending admin approval. Please wait.');
            redirect('/Uniworksmohinhhoa/public/login.php');
        }

        if ($company['status'] === 'rejected') {
            session_destroy();
            setFlash('error', 'Your company account has been rejected. Please contact support.');
            redirect('/Uniworksmohinhhoa/public/login.php');
        }

        if ($company['status'] === 'suspended') {
            session_destroy();
            setFlash('error', 'Your company account has been suspended. Please contact the administrator.');
            redirect('/Uniworksmohinhhoa/public/login.php');
        }

        setFlash('success', 'Login successful! Welcome back, ' . $user['full_name'] . '.');
        $_SESSION['redirect_to'] = '/Uniworksmohinhhoa/company/dashboard.php';
        $_SESSION['prefill_email'] = $user['email'];
        redirect('/Uniworksmohinhhoa/public/login.php');
    }

    if ($user['role'] === 'admin') {
        setFlash('success', 'Login successful! Welcome back, ' . $user['full_name'] . '.');
        $_SESSION['redirect_to'] = '/Uniworksmohinhhoa/admin/dashboard.php';
        $_SESSION['prefill_email'] = $user['email'];
        redirect('/Uniworksmohinhhoa/public/login.php');
>>>>>>> Stashed changes
    }

} catch (Exception $e) {
    setFlash('error', 'Login failed.');
    redirect('/Uniworksmohinhhoa/public/login.php');
}