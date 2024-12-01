<?php
session_start();
require_once "sideBar.php";

// Set the correct content-type header
header('Content-Type: text/html; charset=UTF-8');

// Ensure the admin is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// local
// $conn = new mysqli('localhost', 'root', '', 'accompanyme');

// kesug
$conn = new mysqli('sql307.infinityfree.com', 'if0_36896748', 'rzQg0dnCh2BT', 'if0_36896748_accompanyme');


// Ensure the database connection uses UTF-8
$conn->set_charset("utf8mb4");

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Fetch contact form messages with search functionality
$sql = "SELECT name, email, message, created_at FROM messages";

// If a search term is provided, modify the query to include a WHERE clause
if (!empty($search)) {
    $sql .= " WHERE name LIKE ? OR email LIKE ? OR message LIKE ? OR created_at LIKE ?";
}

$stmt = $conn->prepare($sql);

if (!empty($search)) {
    $searchParam = '%' . $search . '%';
    // Correct the bind_param type specifiers to "ssss" for all string parameters
    $stmt->bind_param("ssss", $searchParam, $searchParam, $searchParam, $searchParam);
} else {
    // If no search term, execute the query without filtering
    $stmt->execute();
}

// Execute the query
$stmt->execute();
$result = $stmt->get_result();
?>


<style>
    .table-container { margin: 20px; }
    .table-container table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    .table-container th, .table-container td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    .table-container th { background-color: #333; color: #fff; }
    .table-container tr:nth-child(even) { background-color: #f2f2f2; }
    .view-button { background-color: #333; color: #fff; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; text-align: center; display: inline-block; text-decoration: none; }
    .view-button:hover { background-color: #555; }
    .search-form { margin-bottom: 20px; }
    .search-form input[type="text"] {
        padding: 8px;
        width: 250px;
        margin-right: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    .search-form button {
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        background-color: #007bff;
        color: #fff;
        cursor: pointer;
    }
    .search-form button:hover {
        background-color: #0056b3;
    }
</style>

<div class="table-container">
<h2>Manage Messages</h2>
    <form class="search-form" method="GET" action="manage_messages.php">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name or messages">
        <button type="submit">Search</button>
        <button type="button" id="refreshBtn" class="btn btn-outline-secondary">Refresh Search</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Message</th>
                <th>Date/Time</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows === 0): ?>
            <tr>
                <td colspan="4">No messages found.</td>
            </tr>
        <?php else: ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>
                    <td><?php echo date('F j, Y, g:i a', strtotime($row['created_at'])); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    // Refresh button functionality
    document.getElementById('refreshBtn').addEventListener('click', function() {
        document.querySelector('input[name="search"]').value = '';  // Clear search input
        document.querySelector('.search-form').submit();  // Submit the form
    });
</script>

<?php $conn->close(); ?>
