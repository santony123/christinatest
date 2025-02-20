<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$student_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT name FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->bind_result($name);
$stmt->fetch();
$stmt->close();

echo json_encode(["name" => $name]);
?>
