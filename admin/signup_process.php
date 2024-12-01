<?php
// Start session
session_start();

// Database connection
// $conn = new mysqli('localhost', 'root', '', 'accompanyme');

// kesug
$conn = new mysqli('sql307.infinityfree.com', 'if0_36896748', 'rzQg0dnCh2BT', 'if0_36896748_accompanyme');

// infinity
// $conn = new mysqli('sql202.infinityfree.com', 'if0_37495817', 'TQY8mKoPDq', 'if0_37495817_accompanyme');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user input
$username = $_POST['username'];
$password = $_POST['password']; // Fetch password input
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Check if username already exists
$checkSql = "SELECT * FROM admins WHERE username = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param('s', $username);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    echo "<script>alert('OOPS ☹ Username already exists. Please choose another one.'); 
                  window.location.href = 'signup.php';
                  </script>";
    exit();
}

// Insert new admin
$sql = "INSERT INTO admins (username, password) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $username, $passwordHash);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup Process</title>
    <style>
    </style>
</head>
<body>
    <div class="container">
        <?php
        if ($stmt->execute()) {
            echo "<script>alert('Sign Up successful! Please consider to log in to confirm your SignUp Credential.'); 
                  window.location.href = 'login.php';
                  </script>";
        } else {
            echo "<h1>Error</h1>";
            echo "<p>There was an error with your signup: " . $stmt->error . "</p>";
        }

        $stmt->close();
        $checkStmt->close();
        $conn->close();
        ?>
    </div>
</body>
</html>