<?php
// Database Connection
$host = "localhost";
$user = "root";
$password = "";
$database = "student_dashboard";

$conn = new mysqli($host, $user, $password, $database);

// Check Connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed"]));
}

// Set JSON headers for API
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");
?>
