<?php
session_start();
// Database connection
require_once "config.php";

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uemail = $_POST['email'];
    $uname = $_POST['username'];
    $upassword = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Check if username or email already exists
    $stmt = $conn->prepare('SELECT uid FROM users WHERE uemail = ? OR uname = ?');
    $stmt->bind_param('ss', $uemail, $uname);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo 'Email or Username already exists!';
    } else {
        // Insert new user
        $stmt = $conn->prepare('INSERT INTO users (uemail, uname, upassword) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $uemail, $uname, $upassword);

        if ($stmt->execute()) {
            // Fetch the uid of the newly created user
            $newUserId = $conn->insert_id;

            // Set the user uid in the session (log them in)
            $_SESSION['uid'] = $newUserId;

            // Redirect to index.php after the user is confirmed
            header('Location: index.php');
            exit();
        } else {
            echo 'Sign-up failed!';
        }
    }
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="background"></div>
    <div class="login-container">
        <div class="login-box">

            <h2>Sign Up</h2>
            <form action="signup.php" method="POST">
                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn">Sign Up</button>
            </form>
            <p class="signup-link">Already have an account? <a href="signin.php">Sign In</a></p>
        </div>
    </div>
    <!-- <script>
    const images = [
        'attractions/background1.jpg', 
        'attractions/background2.jpg', 
        'attractions/background.jpg' 
    ];

    let currentIndex = 0;
    const backgroundElement = document.querySelector('.background');

    // Immediately set the first background image on page load
    backgroundElement.style.backgroundImage = `url(${images[currentIndex]})`;

    function changeBackground() {
        currentIndex = (currentIndex + 1) % images.length;
        backgroundElement.style.opacity = 0; // Fade out
        setTimeout(() => {
            backgroundElement.style.backgroundImage = `url(${images[currentIndex]})`;
            backgroundElement.style.opacity = 1; // Fade in
        }, 1000); 
    }

    // Start the interval to change the background
    setInterval(changeBackground, 10000); // Change image every 10 seconds
</script> -->
</body>
</html>
