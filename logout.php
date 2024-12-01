<?php
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Redirect to index with a logout success message
header("Location: index.php?logout=success");
exit();
?>
