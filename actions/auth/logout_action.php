<?php
require_once '../../includes/functions.php';

session_unset();
session_destroy();

session_start();
setFlash('success', 'Logged out successfully.');

redirect('/Uniworksmohinhhoa/public/login.php');