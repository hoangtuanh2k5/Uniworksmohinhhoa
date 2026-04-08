<?php
require_once __DIR__ . '/functions.php';

function requireLogin() {
    if (!isLoggedIn()) {
        setFlash('error', 'Please login first.');
        redirect('/Uniworksmohinhhoa/public/login.php');
    }
}

function requireRole($role) {
    requireLogin();

    if (!isRole($role)) {
        setFlash('error', 'You do not have permission to access this page.');
        redirect('/Uniworksmohinhhoa/public/unauthorized.php');
    }
}
?>