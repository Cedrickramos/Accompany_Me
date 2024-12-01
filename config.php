<?php
// $host = 'localhost';
// $user = 'root';
// $password = '';
// $dbname = 'accompanyme';

// kesug
$host = 'sql307.infinityfree.com';
$user = 'if0_36896748';
$password = 'rzQg0dnCh2BT';
$dbname = 'if0_36896748_accompanyme';

// infinity
// $host = 'sql202.infinityfree.com';
// $user = 'if0_37495817';
// $password = 'TQY8mKoPDq';
// $dbname = 'if0_37495817_accompanyme';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
