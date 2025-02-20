<?php
include 'db.php';
session_start();

if (!isset($_GET['payment_id']) || !isset($_SESSION['order_id'])) {
    die("<h2 style='color: red;'>Invalid request</h2>");
}

$api_key = "rzp_test_Im0cXH95GcrT4B";
$api_secret = "EU9ANAjybxRaRn0tkV0fRPOP";
$payment_id = $_GET['payment_id'];

// Fetch payment details using cURL
$ch = curl_init("https://api.razorpay.com/v1/payments/" . $payment_id);
curl_setopt($ch, CURLOPT_USERPWD, $api_key . ":" . $api_secret);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$payment = json_decode($response, true);

// Check if payment was successful
$payment_successful = ($payment && isset($payment['status']) && $payment['status'] == 'captured');

if ($payment_successful) {
    $student_id = $_SESSION['user_id'];
    $amount = $payment['amount'] / 100; // Converts paise to INR (₹1 = 100 paise)

    $stmt = $conn->prepare("INSERT INTO payments (student_id, payment_id, amount, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $student_id, $payment_id, $amount, $payment['status']);
    $stmt->execute();
    $stmt->close();

    $message = "Payment Successful! You paid ₹" . $amount;
    $color = "green";
} else {
    $message = "Payment Failed!";
    $color = "red";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
            background: linear-gradient(135deg, #71b7e6, #9b59b6);
            color: white;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            display: inline-block;
            color: black;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            width: 50%;
        }
        h2 {
            color: <?php echo $color; ?>;
        }
        .btn {
            background: linear-gradient(135deg, #71b7e6, #9b59b6);
            border: none;
            padding: 12px 20px;
            font-size: 18px;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .btn:hover {
            background: linear-gradient(-135deg, #71b7e6, #9b59b6);
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><?php echo $message; ?></h2>
        <a href="dashboard.html" class="btn">Go to Dashboard</a>
        
    </div>
</body>
</html>
