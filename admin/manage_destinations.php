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

// Initialize destinations array
$destinations = [];

// Handle form submission to add an attraction
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $city_id = $_POST['city_id'] ?? null;
    $attraction_name = $_POST['attraction_name'] ?? null;

    // Validate city_id
    if (empty($city_id)) {
        $_SESSION['error'] = "City is required.";
        header("Location: manage_destinations.php");
        exit();
    }

    // Validate if city exists
    $sql_check_city = "SELECT * FROM cities WHERE city_id = ?";
    $stmt_check_city = $conn->prepare($sql_check_city);
    $stmt_check_city->bind_param("i", $city_id);
    $stmt_check_city->execute();
    $result_check_city = $stmt_check_city->get_result();

    if ($result_check_city->num_rows == 0) {
        die("Invalid city selected.");
    }

    // Insert the attraction
    $sql = "INSERT INTO attractions (city_id, attraction_name) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $city_id, $attraction_name);

    if ($stmt->execute()) {
        echo "Attraction added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Fetch destinations with search functionality
$sql_destinations = "
    SELECT d.dest_id, d.attraction_name, d.image, d.map, d.weather, a.longitude, a.latitude, c.city 
    FROM destinations d 
    JOIN cities c ON d.city_id = c.city_id
    JOIN attractions a ON d.attraction_name = a.attraction_name";

if (!empty($search)) {
    $sql_destinations .= " WHERE d.attraction_name LIKE ? OR c.city LIKE ?";
    $stmt_destinations = $conn->prepare($sql_destinations);
    $searchTerm = "%$search%";
    $stmt_destinations->bind_param("ss", $searchTerm, $searchTerm);
} else {
    $stmt_destinations = $conn->prepare($sql_destinations);
}

// Execute the query and fetch results
$stmt_destinations->execute();
$result_destinations = $stmt_destinations->get_result();

if ($result_destinations->num_rows > 0) {
    while ($row = $result_destinations->fetch_assoc()) {
        $destinations[] = $row;
    }
}

// Close the database connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Destinations</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .btn { background-color: #333; color: #fff; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; text-align: center; text-decoration: none; }
        .btn:hover { background-color: #555; }
        .table-container table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table-container th, .table-container td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .table-container th { background-color: #333; color: #fff; }
        .table-container tr:nth-child(even) { background-color: #f2f2f2; }
        .table-container td img { max-width: 100px; height: auto; cursor: pointer; }
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
        .weather-info { padding: 10px; background-color: transparent; border-radius: 8px; text-align: center; }
        .weather-info img { width: 50px; height: 50px; }

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
            <div class="table-container">
                <h2>Manage Destinations</h2>

                <!-- Search Form -->
                <form class="search-form" method="GET" action="manage_destinations.php">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search for City or Destination Name">
                    <button type="submit">Search</button>
                    <button type="button" id="refreshBtn" class="btn btn-outline-secondary">Refresh Search</button>
                </form>

                <table>
                    <thead>
                        <tr>
                            <th>City</th>
                            <th>Destination Name</th>
                            <th>Image</th>
                            <th>Map Link</th>
                            <th>Weather Info</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($destinations)): ?>
                            <tr>
                                <td colspan="6">No destinations found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($destinations as $destination): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($destination['city']); ?></td>
                                    <td><?php echo htmlspecialchars($destination['attraction_name']); ?></td>
                                    <td>
                                        <img src="<?php echo htmlspecialchars($destination['image']); ?>" alt="Destination Image" class="clickable-image">
                                    </td>
                                    <td>
                                        <?php if (!empty($destination['longitude']) && !empty($destination['latitude'])): ?>
                                            <p><b>Longitude:</b> <?php echo htmlspecialchars($destination['longitude']); ?></p>
                                            <hr>
                                            <p><b>Latitude:</b> <?php echo htmlspecialchars($destination['latitude']); ?></p>
                                            <a href="https://www.google.com/maps?q=<?php echo htmlspecialchars($destination['longitude']) . ',' . htmlspecialchars($destination['latitude']); ?>" target="_blank" class="btn">View on Map</a>
                                            <br><br>
                                        <?php else: ?>
                                            No Map Available
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="weather-info">
                                            <?php
                                            if (!empty($destination['longitude']) && !empty($destination['latitude'])) {
                                                $weatherApiKey = 'a7169d65775d4f55b6f104707242411';
                                                $weatherUrl = "http://api.weatherapi.com/v1/current.json?key=$weatherApiKey&q={$destination['longitude']},{$destination['latitude']}";
                                                $weatherResponse = file_get_contents($weatherUrl);
                                                $weatherData = json_decode($weatherResponse, true);
                                                $weatherDescription = $weatherData['current']['condition']['text'] ?? 'No weather data available';
                                                $temperature = $weatherData['current']['temp_c'] ?? 'N/A';
                                                $humidity = $weatherData['current']['humidity'] ?? 'N/A';
                                                $weatherIcon = $weatherData['current']['condition']['icon'] ?? '';
                                            } else {
                                                $weatherDescription = 'No weather data available';
                                                $temperature = 'N/A';
                                                $humidity = 'N/A';
                                                $weatherIcon = '';
                                            }
                                            ?>
                                            <?php if (!empty($weatherIcon)): ?>
                                                <img src="https:<?php echo $weatherIcon; ?>" alt="Weather Icon">
                                            <?php endif; ?>
                                            <p><?php echo $weatherDescription; ?></p>
                                            <p><strong>Temperature:</strong> <?php echo $temperature; ?>°C</p>
                                            <p><strong>Humidity:</strong> <?php echo $humidity; ?>%</p>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="edit_attraction.php?id=<?php echo $destination['dest_id']; ?>" class="btn">Edit</a>
                                        <a href="delete_attraction.php?id=<?php echo $destination['dest_id']; ?>" class="btn">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Modal for Image Viewing -->
            <div class="modal" id="imageModal">
                <span class="modal-close" onclick="closeModal()">X</span>
                <img id="modalImage" src="" alt="Destination Image">
            </div>

        </div>
    </div>

    <script>
        // Open modal to view image
        document.querySelectorAll('.clickable-image').forEach(item => {
            item.addEventListener('click', event => {
                const src = event.target.src;
                const modal = document.getElementById('imageModal');
                const modalImage = document.getElementById('modalImage');
                modalImage.src = src;
                modal.style.display = 'flex';
            });
        });

        // Close modal
        function closeModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        // Refresh button functionality
    document.getElementById('refreshBtn').addEventListener('click', function() {
        document.querySelector('input[name="search"]').value = '';  // Clear search input
        document.querySelector('.search-form').submit();  // Submit the form
    });
    </script>
</body>
</html>
