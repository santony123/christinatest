<?php
include '../db.php';

header("Content-Type: application/json");


$data = json_decode(file_get_contents("php://input"), true);
$name = $data['name'];
$email = $data['email'];
$password = password_hash($data['password'], PASSWORD_BCRYPT);
// $fees = 5000; // Example fee

// Check if email already exists
$sql_check = "SELECT * FROM students WHERE email = ?";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    echo json_encode(["error" => "Student with this email already exists"]);
    exit;
}

// Insert student data
$sql = "INSERT INTO students (name, email, password) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $name, $email, $password);
if ($stmt->execute()) {
    $student_id = $stmt->insert_id;
    echo json_encode(["id" => $student_id, "name" => $name, "email" => $email]);
    // header("Location: ../login.html");
} else {
    echo json_encode(["error" => "Failed to register student"]);
}
?>