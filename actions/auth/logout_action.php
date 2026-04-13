<?php
session_start();

$_SESSION = [];
session_destroy();

header("Location: /Uniworksmohinhhoa/public/index.php");
exit;