<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if (!isLoggedIn() || $_SESSION['user']['role'] !== 'student') {
    redirect('/Uniworksmohinhhoa/public/login.php');
}

$user_id = $_SESSION['user']['id'];
$student_code = sanitize($_POST['student_code'] ?? '');
$major_id = $_POST['major_id'] ?? null;
$class_name = sanitize($_POST['class_name'] ?? '');
$gpa = $_POST['gpa'] ?? null;

if (!$student_code || !$major_id) {
    setFlash('error', 'Please fill in all required fields.');
    redirect('/Uniworksmohinhhoa/student/profile.php?setup=1');
}

try {
    $stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $stmt = $pdo->prepare("
            UPDATE students
            SET student_code = ?, major_id = ?, class_name = ?, gpa = ?
            WHERE user_id = ?
        ");
        $stmt->execute([
            $student_code,
            $major_id,
            $class_name !== '' ? $class_name : null,
            $gpa !== '' ? $gpa : null,
            $user_id
        ]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO students (user_id, student_code, major_id, class_name, gpa)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user_id,
            $student_code,
            $major_id,
            $class_name !== '' ? $class_name : null,
            $gpa !== '' ? $gpa : null
        ]);
    }

    setFlash('success', 'Profile saved successfully.');
    redirect('/Uniworksmohinhhoa/student/dashboard.php');

} catch (Exception $e) {
    setFlash('error', 'Failed to save profile.');
    redirect('/Uniworksmohinhhoa/student/profile.php?setup=1');
}