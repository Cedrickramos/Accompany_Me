<?php
require_once "../config.php";

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$sql = "SELECT uid, uname, uemail FROM users WHERE uname LIKE ? OR uemail LIKE ?";
$stmt = $conn->prepare($sql);
$searchParam = "%$search%";
$stmt->bind_param("ss", $searchParam, $searchParam);
$stmt->execute();
$result = $stmt->get_result();

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="sideBar.css">

    <!-- for tables lang yung stytle na to -->
    <style>
        .table-container {
            margin-bottom: 20px;
        }

        .table-container table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table-container th, .table-container td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        .table-container th {
            background-color: #333;
            color: #fff;
        }

        .table-container tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .action-buttons a, .action-buttons button {
            padding: 5px 10px;
            border: none;
            color: #fff;
            cursor: pointer;
            text-decoration: none;
        }
        
        .edit-button {
            background-color: #4CAF50;
        }
        
        .delete-button {
            background-color: #f44336;
        }

        .search-form {
        margin-bottom: 20px;
    }

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
</head>
<body>
<div class="admin-page">
    <?php
    require_once "sideBar.php"
    ?>
    <div class="main-content">
        <div class="table-container">
            <h2>Manage Users</h2>
            <form class="search-form" method="GET" action="manage_users.php">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by ID or Username">
                <button type="submit">Search</button>
                <!-- Add a Refresh button -->
                <button type="button" id="refreshBtn" class="btn btn-outline-secondary">Refresh Search</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        // Output data of each row
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['uid']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['uname']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['uemail']) . "</td>";
                            echo "<td class='action-buttons'>";
                            echo "<a href='edit_user.php?id=" . urlencode($row['uid']) . "' class='edit-button'>Edit</a>";
                            echo "<a href='delete_user.php?id=" . urlencode($row['uid']) . "' class='delete-button'>Delete</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No users found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    // Refresh button functionality
    document.getElementById('refreshBtn').addEventListener('click', function() {
        document.querySelector('input[name="search"]').value = '';  // Clear search input
        document.querySelector('.search-form').submit();  // Submit the form
    });
</script>
<?php
$conn->close();
?>
</body>
</html>
