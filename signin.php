<?php
session_start();

require_once "config.php";

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uname = $_POST['username'];
    $upassword = $_POST['password'];

    // Find the user
    $stmt = $conn->prepare('SELECT uid, upassword FROM users WHERE uname = ?');
    $stmt->bind_param('s', $uname);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Check if the user exists and the password is correct
    if ($user && password_verify($upassword, $user['upassword'])) {
        // Set the user uid in the session
        $_SESSION['uid'] = $user['uid'];
        
        // if the user is confirmed, direct to index.php
        header('Location: index.php');
        exit();
    } else {
        echo 'Invalid username or password!';
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="background"></div>
    <div class="login-container">
        <div class="login-box">
            <h2>Login</h2>
            <form action="signin.php" method="POST">
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn">Login</button>
            </form>
            <p class="signup-link">Don't have an account? <a href="signup.php">Sign up</a></p>
        </div>
</div>
</body>
</html>
