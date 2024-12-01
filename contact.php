<?php
ob_start(); // Starts output buffering to prevent "headers already sent" error

require_once "navbar.php";

// $host = 'localhost';
// $dbname = 'accompanyme';
// $username = 'root';
// $password = ''; 

// kesug
$host = 'sql307.infinityfree.com';
$username = 'if0_36896748';
$password = 'rzQg0dnCh2BT';
$dbname = 'if0_36896748_accompanyme';

// infinity
// $host = 'sql202.infinityfree.com';
// $user = 'if0_37495817';
// $password = 'TQY8mKoPDq';
// $dbname = 'if0_37495817_accompanyme';

try {
    // Connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Capture form data
        $name = htmlspecialchars(trim($_POST['name']));
        $email = htmlspecialchars(trim($_POST['email']));
        $message = htmlspecialchars(trim($_POST['message']));

        // Prepare SQL query to insert data into messages table
        $sql = "INSERT INTO messages (name, email, message) VALUES (:name, :email, :message)";
        $stmt = $pdo->prepare($sql);

        // Bind parameters to the query
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':message', $message);

        // Execute the query
        $stmt->execute();

        // Redirect to the same page with success
        header("Location: contact.php?success=1");
        exit;
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage(); // Error handling if DB connection fails
    header("Location: contact.php?error=1");
    exit;
}

ob_end_flush(); // Ends output buffering
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mabuhay, Laguna</title>
    <link rel="stylesheet" href="styles.css">
    <style>
    .content-sections {
        max-width: 800px; 
        margin: 0 auto; 
        padding: 20px;
    }

    .head {
        max-width: 800px; 
        padding: 30px 50px;
    }
    </style>
</head>
<body>

<div class="head">
    <h1>Contact Section</h1>
    <h3 style="color: grey">Have questions or need assistance? Reach out to us:<br>
    Email: <a href="mailto:accompanymelaguna@gmail.com" style="color: grey">accompanymelaguna@gmail.com</a><br>
    Phone: +63 123 456 7890</h3>
</div>
<hr> 
<h1 style="padding-left: 30px; color: #4d4d4d; font-style: arial, sans-serif">Your Feedback matters with us</h1>

<div class="content-sections">

    <form action="contact.php" method="post" class="contact-form">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" placeholder="Your Name" required>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" placeholder="Your Email" required>
        <label for="message">Message:</label>
        <textarea id="message" name="message" placeholder="Your Feedback here" required></textarea>

        <button type="submit" class="btn">Send Feedback</button>
        <?php if(isset($_GET['success'])): ?>
            <p style="color: green;">Your message has been sent successfully!</p>
        <?php endif; ?>
        <?php if(isset($_GET['error'])): ?>
            <p style="color: red;">Oops! There was an issue sending your message. Please try again.</p>
        <?php endif; ?>
    </form>
</div>

<?php
require_once "footer.php";
?>

</body>
</html>