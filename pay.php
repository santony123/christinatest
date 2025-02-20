<?php
session_start();
include 'db.php';

$student_id = $_SESSION['student_id'];

// Check if the student has already paid
$query = $conn->prepare("SELECT * FROM payments WHERE student_id = ? AND status = 'captured'");
$query->bind_param("i", $student_id);
$query->execute();
$result = $query->get_result();
$already_paid = $result->num_rows > 0; // If any record is found, payment is already made
$query->close();

$api_key = "rzp_test_Im0cXH95GcrT4B"; 
$api_secret = "EU9ANAjybxRaRn0tkV0fRPOP"; 
$amount = 100 * 100; // ₹100 in paise
$currency = "INR";
$receipt = "rcptid_" . uniqid();

// Only create order if the student has NOT paid
if (!$already_paid) {
    $data = [
        "amount" => $amount,
        "currency" => $currency,
        "receipt" => $receipt,
        "payment_capture" => 1
    ];

    $ch = curl_init("https://api.razorpay.com/v1/orders");
    curl_setopt($ch, CURLOPT_USERPWD, $api_key . ":" . $api_secret);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $order = json_decode($response, true);

    if (!isset($order['id'])) {
        die("Error creating order");
    }

    $_SESSION['order_id'] = $order['id'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Now - ₹100</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
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
        }
        h2 {
            margin-bottom: 20px;
        }
        #pay-button, .dashboard-button {
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
        #pay-button:hover, .dashboard-button:hover {
            background: linear-gradient(-135deg, #71b7e6, #9b59b6);
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($already_paid): ?>
            <h2 style="color: green;">You have already paid the fee!</h2>
            <a href="dashboard.html" class="dashboard-button">Go to Dashboard</a>
        <?php else: ?>
            <h2>Pay ₹100 Now</h2>
            <button id="pay-button">Pay ₹100</button>
        <?php endif; ?>
    </div>

    <?php if (!$already_paid): ?>
    <script>
        var options = {
            "key": "<?php echo $api_key; ?>",
            "amount": "<?php echo $amount; ?>",
            "currency": "INR",
            "name": "Student Portal",
            "description": "Fee Payment",
            "order_id": "<?php echo $_SESSION['order_id']; ?>",
            "handler": function (response) {
                window.location.replace("verify_payment.php?payment_id=" + response.razorpay_payment_id);
            }
        };

        var rzp1 = new Razorpay(options);

        document.getElementById('pay-button').onclick = function () {
            rzp1.open();
        };
    </script>
    <?php endif; ?>
</body>
</html>
