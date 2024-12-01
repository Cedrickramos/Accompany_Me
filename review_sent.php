<?php
require_once "navbar.php";
// Ensure the user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}

// Get the attraction ID to link back to its details page
$attr_id = $_GET['attr_id'] ?? null;
if (!$attr_id) {
    echo "Invalid access. No attraction ID provided.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Submitted</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }
        .main-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }
        p {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
        }
        .back-button {
            display: inline-block;
            padding: 12px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #333;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .back-button:hover {
            background-color: #555;
        }
    </style>
</head>
<body>
<div class="main-container">
    <h1>Thank You for Your Review!</h1>
    <p>Your feedback is valuable and helps us improve the experience for others.</p>
    <a class="back-button" href="index.php">Back to Main Page</a>
</div>
</body>
</html>
