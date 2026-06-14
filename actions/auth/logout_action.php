<?php
require_once '../../includes/functions.php';

session_unset();
session_destroy();

<<<<<<< Updated upstream
session_start();
setFlash('success', 'Logged out successfully.');

redirect('/Uniworksmohinhhoa/public/login.php');
=======
// Restart session chỉ để set flash
session_start();
session_regenerate_id(true);

$_SESSION['flash'] = [
    'type'    => 'success',
    'message' => 'You have been logged out successfully.'
];

header("Location: /Uniworksmohinhhoa/public/login.php");
exit;
>>>>>>> Stashed changes
