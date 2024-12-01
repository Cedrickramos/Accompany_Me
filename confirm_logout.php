<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Logout</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
        }
        .logout-container {
            display: inline-block;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        h1 {
            color: #333;
        }
        button {
            padding: 10px 20px;
            margin: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .yes-btn {
            background-color: #d9534f;
            color: white;
        }
        .no-btn {
            background-color: #5bc0de;
            color: white;
        }
    </style>
</head>
<body>

<div class="logout-container">
    <h1>Are you sure you want to log out?</h1>
    <form action="logout.php" method="post" style="display: inline;">
        <button type="submit" class="yes-btn">Yes, Log Out</button>
    </form>
    <button class="no-btn" onclick="window.history.back()">No, Go Back</button>
</div>

</body>
</html>
