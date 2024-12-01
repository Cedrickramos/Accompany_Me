<?php
// Start session
session_start();

// Set the correct content-type header
header('Content-Type: text/html; charset=UTF-8');

// Ensure the database connection uses UTF-8
// $conn->set_charset("utf8mb4");

// Check if admin is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login.php if admin has not yet logged in
    exit();
}
require_once "../config.php";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
        }
        .dashboard {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 600px;
            text-align: center;
        }
        .dashboard h1 {
            margin-bottom: 20px;
        }
        .dashboard a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #333;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
        }
        .dashboard a:hover {
            background-color: #555;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p>You are now logged in. Here you can manage your admin functions.</p>
        <!-- <a href="logout.php">Logout</a> -->
        <a href="manage_users.php">Manage AccompanyMe</a>
    </div>
</body>
</html>
