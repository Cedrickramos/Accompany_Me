<?php
session_start();

// Set the correct content-type header
// header('Content-Type: text/html; charset=UTF-8');

// Ensure the database connection uses UTF-8
// $conn->set_charset("utf8mb4");

// Check if admin is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require_once '../config.php';
if (!$conn->set_charset("utf8mb4")) {
    // Handle error if setting charset fails
    echo "Error loading character set utf8mb4: " . $conn->error;
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Fetch restaurants data with city names
$sql = "SELECT r.*, c.city FROM restaurants r
JOIN cities c ON r.city_id = c.city_id";
$result = $conn->query($sql);

if (!empty($search)) {
    $sql .= " WHERE r.resto_name LIKE ? OR c.city LIKE ?";
}

$stmt = $conn->prepare($sql);

if (!empty($search)) {
    $searchParam = '%' . $search . '%';
    $stmt->bind_param("ss", $searchParam, $searchParam);
}

if (!$result) {
    die("Query failed: " . $conn->error);
}

$restaurants = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Restaurants</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .add-button {
            display: inline-block;
            padding: 10px 15px;
            margin-bottom: 20px;
            background-color: #333;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            text-align: center;
            font-size: 16px;
            transition: background-color 0.3s;
            float: right;
        }

        .add-button:hover {
            background-color: #555;
        }

        .table-container table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table-container th, .table-container td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        .table-container th {
            background-color: #333;
            color: #fff;
        }

        .table-container tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .table-container td img {
            max-width: 100px;
            height: auto;
            cursor: pointer;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal img {
            width: 400px; /* Fixed width for the image */
            height: 300px; /* Fixed height for the image */
            object-fit: cover; /* Ensure image fits within the dimensions */
            border: 2px solid #fff;
            border-radius: 8px;
        }

        .modal-close {
            position: absolute;
            text-align: center;
            border-radius: 8px;
            top: 40px;
            right: 40px;
            font-size: 35px;
            width: 50px;
            height: 40px;
            background-color: red;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }
        .update-button {
            background-color: #007bff;
            color: #fff;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            display: inline-block;
            text-decoration: none;
        }

        .update-button:hover {
            background-color: #0056b3;
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
        <?php require_once "sideBar.php"; ?>
        <div class="main-content">
            <a href="add_restaurant.php" class="add-button">Add New Restaurant</a>
            <div class="table-container">
                <h2>Manage Restaurants</h2>
                <form class="search-form" method="GET" action="manage_restaurants.php">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by ID or Username">
                    <button type="submit">Search</button>
                    <button type="button" id="refreshBtn" class="btn btn-outline-secondary">Refresh Search</button>
                </form>
                <table>
                    <thead>
                        <tr>
                            <th>City</th>
                            <th>Restaurant Name</th>
                            <th>Description</th>
                            <th>Image</th>
                            <th>Parking</th>
                            <th>Restaurant Hours</th>
                            <th>Contact Info</th>
                            <th>Location</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($restaurants)): ?>
                            <tr>
                                <td colspan="9">No restaurants found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($restaurants as $restaurant): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($restaurant['city']); ?></td>
                                    <td><?php echo htmlspecialchars($restaurant['resto_name']); ?></td>
                                    <td><?php echo htmlspecialchars($restaurant['resto_description']); ?></td>
                                    <td>
                                        <?php if ($restaurant['resto_image']): ?>
                                            <img src="<?php echo htmlspecialchars($restaurant['resto_image']); ?>" alt="Restaurant Image" class="clickable-image">
                                        <?php else: ?>
                                            No Image Available
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($restaurant['resto_parking']); ?></td>
                                    <td>
                                        <?php
                                        $open_time = new DateTime($restaurant['resto_open']);
                                        $close_time = new DateTime($restaurant['resto_close']);
                                        echo $open_time->format('h:i a') . ' - ' . $close_time->format('h:i a');
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($restaurant['resto_contacts']); ?></td>
                                    <td>
                                        <p><b>Longitude:</b> <?php echo htmlspecialchars($restaurant['resto_longitude']); ?></p>
                                        <p><b>Latitude:</b> <?php echo htmlspecialchars($restaurant['resto_latitude']); ?></p>
                                    </td>
                                    <td>
                                        <a href="edit_restaurant.php?id=<?php echo $restaurant['resto_id']; ?>" class="update-button">Edit</a>
                                        <a href="delete_restaurant.php?id=<?php echo $restaurant['resto_id']; ?>" class="update-button">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal" id="image-modal">
        <span class="modal-close" id="modal-close">&times;</span>
        <img id="modal-image" src="" alt="Popup Image">
    </div>

    <script>
        const modal = document.getElementById('image-modal');
        const modalImage = document.getElementById('modal-image');
        const modalClose = document.getElementById('modal-close');

        document.querySelectorAll('.clickable-image').forEach(img => {
            img.addEventListener('click', () => {
                modalImage.src = img.src;
                modal.style.display = 'flex';
            });
        });

        modalClose.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });

        // Refresh button functionality
    document.getElementById('refreshBtn').addEventListener('click', function() {
        document.querySelector('input[name="search"]').value = '';  // Clear search input
        document.querySelector('.search-form').submit();  // Submit the form
    });
    </script>
</body>
</html>
