<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if (!isLoggedIn() || $_SESSION['user']['role'] !== 'student') {
    redirect('/Uniworksmohinhhoa/public/login.php');
}

$user_id      = $_SESSION['user']['id'];
$student_code = sanitize($_POST['student_code'] ?? '');
$major_id     = (int)($_POST['major_id']        ?? 0);
$class_name   = sanitize($_POST['class_name']   ?? '');
$gpa          = $_POST['gpa']                   ?? null;

if ($student_code === '' || $major_id <= 0) {
    setFlash('error', 'Student Code and Major are required.');
    redirect('/Uniworksmohinhhoa/student/profile.php?setup=1');
}

if ($gpa !== '' && $gpa !== null) {
    $gpa = (float)$gpa;
    if ($gpa < 0 || $gpa > 10) {
        setFlash('error', 'GPA must be between 0 and 10.');
        redirect('/Uniworksmohinhhoa/student/profile.php?setup=1');
    }
} else {
    $gpa = null;
}

try {
    $stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $pdo->prepare("
            UPDATE students
            SET student_code = ?, major_id = ?, class_name = ?, gpa = ?
            WHERE user_id = ?
        ")->execute([
            $student_code,
            $major_id,
            $class_name !== '' ? $class_name : null,
            $gpa,
            $user_id
        ]);
    } else {
        $pdo->prepare("
            INSERT INTO students (user_id, student_code, major_id, class_name, gpa)
            VALUES (?, ?, ?, ?, ?)
        ")->execute([
            $user_id,
            $student_code,
            $major_id,
            $class_name !== '' ? $class_name : null,
            $gpa
        ]);
    }

    setFlash('success', 'Profile saved successfully.');
    redirect('/Uniworksmohinhhoa/student/profile.php?success=1');

} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        setFlash('error', 'Student Code already exists or Major is invalid.');
    } else {
        setFlash('error', 'Database error: ' . $e->getMessage());
    }
    redirect('/Uniworksmohinhhoa/student/profile.php?setup=1');
} catch (Exception $e) {
    setFlash('error', 'Failed to save profile.');
    redirect('/Uniworksmohinhhoa/student/profile.php?setup=1');
}
