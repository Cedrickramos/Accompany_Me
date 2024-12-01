<?php
require_once "config.php";
if (!$conn->set_charset("utf8mb4")) {
    // Handle error if setting charset fails
    echo "Error loading character set utf8mb4: " . $conn->error;
}
require_once "navbar.php";


// Check if the user is logged in
$uid = $_SESSION['uid'] ?? null; 
$uname = $_SESSION['uname'] ?? null; 

// Get the resto_id from the URL
$resto_id = $_GET['resto_id'] ?? '';

if (empty($resto_id)) {
    echo "Invalid restaurant ID.";
    exit;
}

// Fetch restaurant details
$stmt = $conn->prepare("
    SELECT * 
    FROM restaurants 
    WHERE resto_id = ?
");
$stmt->bind_param("i", $resto_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $restaurant = $result->fetch_assoc();
} else {
    echo "Restaurant not found.";
    exit;
}

$longitude = $restaurant['resto_longitude'];
$latitude = $restaurant['resto_latitude'];

// Fetch weather data using WeatherAPI
$weatherApiKey = 'a7169d65775d4f55b6f104707242411'; 
$weatherUrl = "http://api.weatherapi.com/v1/current.json?key=$weatherApiKey&q=$longitude,$latitude";
$weatherResponse = file_get_contents($weatherUrl);
$weatherData = json_decode($weatherResponse, true);

$weatherDescription = $weatherData['current']['condition']['text'] ?? 'No weather data available';
$temperature = $weatherData['current']['temp_c'] ?? 'N/A';
$humidity = $weatherData['current']['humidity'] ?? 'N/A';
$weatherIcon = $weatherData['current']['condition']['icon'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($restaurant['resto_name']); ?> - Details</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .resto-header {
            text-align: center;
            margin-bottom: 30px;
            position: relative; /* Added to allow positioning of child elements inside it */
        }
        /* .attraction-header h1 {
            font-size: 36px;
            color: #333;
        } */
        .weather-info {
            position: absolute;
            width: 230px;
            top: 40px;
            right: 2px;
            background-color: white;
            opacity: 90%;
            padding: 5px 10px 3px 10px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.9);
            text-align: center;
            z-index: 10;
            transition: opacity 0.3s ease-in-out;
        }

        .weather-info img {
            width: 70px;
            height: 70px;
            margin-bottom: 10px; /* Optional, add some space below the icon */
        }

        #weather-content {
            display: block;
        }

        #weather-content.hidden {
            display: none;
        }

        /* Toggle button positioned at the top-right corner */
        .toggle-btn {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #333;
            position: absolute;
            top: 1px;
            right: 1px; /* Position at the top-right corner */
            padding: 5px;
        }

        .toggle-btn:focus {
            outline: none;
        }
        .resto-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 20px;
            position: relative; /* This ensures the .weather-info is placed relative to this element */
        }
        .resto-description {
            font-size: 18px;
            line-height: 1.8;
            margin-bottom: 20px;
        }
        .resto-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 40px;
        }
        .adbutton {
            display: inline-block;
            padding: 10px 20px;
            background-color: #333;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
            margin-top: 10px;
        }
        .adbutton:hover {
            background-color: #555;
        }
    </style>
</head>
<body>
    <br>
    <?php require_once "back.php"; ?>

    <div class="main-container">
        <div class="resto-header">
            <h1><?php echo htmlspecialchars($restaurant['resto_name']); ?></h1>
        
            <img class="resto-image" src="resto/<?php echo htmlspecialchars($restaurant['resto_image']); ?>" alt="<?php echo htmlspecialchars($restaurant['resto_name']); ?>">
            
            <!-- Directions Link -->
            <?php if (!empty($longitude) && !empty($latitude)): ?>
                <a id="get-directions" class="adbutton" href="rmap.php?longitude=<?php echo urlencode($longitude); ?>&latitude=<?php echo urlencode($latitude); ?>">Get Directions</a>
            <?php else: ?>
                <p>Coordinates not available for this place.</p>
            <?php endif; ?>

            <div class="weather-info">
                <h3><?php echo htmlspecialchars($restaurant['resto_name']); ?> Weather</h3>
                <button id="toggle-weather" class="toggle-btn">&#9650;</button>
 
                <div id="weather-content" class="hidden"> <!-- Add 'hidden' class here to start hidden -->
                    <?php
                        if (!empty($weatherIcon)) {
                            echo '<img src="https:' . $weatherIcon . '" alt="Weather Icon">';
                        } else {
                            echo '<p>No weather icon available.</p>';
                        }
                    ?>

                    <p><h3><?php echo $weatherDescription; ?></h3></p>
                    <p><strong>Temperature:</strong> <?php echo $temperature; ?>°C</p>
                    <p><strong>Humidity:</strong> <?php echo $humidity; ?>%</p>
                </div>
            </div>
        </div>

        <div class="resto-details">
            <div class="resto-description">
                <h2>Description</h2>
                <p><?php echo nl2br(htmlspecialchars($restaurant['resto_description'])); ?></p>
            </div>
            <div class="resto-description">
                <h2>Details</h2>
                <p><strong>Parking:</strong> <?php echo htmlspecialchars($restaurant['resto_parking']); ?></p>
                <p><strong>Restaurant Hours:</strong> <?php echo htmlspecialchars($restaurant['resto_open']); ?>am to <?php echo htmlspecialchars($restaurant['resto_close']); ?>pm</p>
                <p><strong>Contact Details:</strong> <?php echo nl2br(htmlspecialchars($restaurant['resto_contacts'])); ?></p>
            </div>
        </div>
        
<?php if ($uid): ?>
    <a href="reviews.php?resto_id=<?php echo $resto_id; ?>" class="adbutton">Review <?php echo htmlspecialchars($restaurant['resto_name']); ?></a>
<?php else: ?>
    <p><em><a href="signin.php" style="color: black">Login</a> to leave a review.</em></p>
<?php endif; ?>

        <a href="resto_users_reviews.php?resto_id=<?php echo $resto_id; ?>" class="adbutton">Show Reviews</a>

    </div>
    <script>
        // JavaScript to toggle weather visibility
document.getElementById('toggle-weather').addEventListener('click', function() {
    var weatherContent = document.getElementById('weather-content');
    var button = document.getElementById('toggle-weather');
    
    // Toggle the visibility of the weather content
    if (weatherContent.classList.contains('hidden')) {
        weatherContent.classList.remove('hidden');
        button.innerHTML = '&#9660;'; // Down arrow
    } else {
        weatherContent.classList.add('hidden');
        button.innerHTML = '&#9650;'; // Up arrow
    }
});

    </script>
</body>
</html>
