<?php
$host = 'localhost';
$dbname = 'internship_management';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
