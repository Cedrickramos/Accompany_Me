<?php
require_once "config.php";
require_once "navbar.php";

// Set the correct content-type header
$conn->set_charset("utf8mb4");

// Fetch longitude and latitude from URL parameters
$attraction_longitude = $_GET['longitude'] ?? null;
$attraction_latitude = $_GET['latitude'] ?? null;

// Check if the coordinates are provided
if (!$attraction_longitude || !$attraction_latitude) {
    echo "Coordinates not provided.";
    exit;
}

// Query the database to fetch the attraction_name based on the provided coordinates
$stmt = $conn->prepare("SELECT attraction_name FROM attractions WHERE longitude = ? AND latitude = ?");
$stmt->bind_param("dd", $attraction_longitude, $attraction_latitude);
$stmt->execute();
$result = $stmt->get_result();

// Check if the attraction was found
if ($result->num_rows > 0) {
    $attraction = $result->fetch_assoc();
    $attraction_name = $attraction['attraction_name'];
} else {
    echo "Attraction not found.";
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($attraction_name); ?> Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <!-- <link rel="stylesheet" href="dist_leaflet.css" /> -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css" />
    <!-- <link rel="stylesheet" href="dist_leaflet-routing-machine.css" /> -->
    <link rel="stylesheet" href="styles.css" />
    <style>
        #map { height: 800px; width: 100%; }
        #startButton {
            position: absolute; top: 280px; right: 20px;
            z-index: 900; padding: 10px 20px; background-color: #007bff;
            color: white; border: none; border-radius: 5px; cursor: pointer;
        }
        #startButton:hover { background-color: #0056b3; }
        .weather-info {
            position: absolute; top: 100px; right: 10px;
            z-index: 900; background-color: white; padding: 10px 15px;
            border-radius: 8px; box-shadow: 0 6px 8px rgba(0, 0, 0, 0.9);
            width: 250px; height: 230px;
        }
        .weather-info img { width: 50px; height: 50px; margin-bottom: 10px; }

        .leaflet-routing-container {
            top: 250px;
            bottom: 20px; /* Moves it 20px above the bottom */
            left: 5px;
            display: none;
        }

    </style>
</head>
<body>
    <h2 style="padding-left: 30px;"><?php echo htmlspecialchars($attraction_name); ?> Location</h2>

    <?php
    // Fetch weather data dynamically for the attraction location
    $weatherApiKey = 'a7169d65775d4f55b6f104707242411';
    $weatherUrl = "http://api.weatherapi.com/v1/current.json?key=$weatherApiKey&q={$attraction_longitude},{$attraction_latitude}";
    $weatherResponse = @file_get_contents($weatherUrl);
    $weatherData = $weatherResponse ? json_decode($weatherResponse, true) : null;

    $weatherDescription = $weatherData['current']['condition']['text'] ?? 'No weather data available';
    $temperature = $weatherData['current']['temp_c'] ?? 'N/A';
    $humidity = $weatherData['current']['humidity'] ?? 'N/A';
    $weatherIcon = $weatherData['current']['condition']['icon'] ?? '';
    ?>

    <div class="weather-info">
        <h3>Weather in <?php echo htmlspecialchars($attraction_name); ?></h3>
        <?php if (!empty($weatherIcon)): ?>
            <img src="https:<?php echo $weatherIcon; ?>" alt="Weather Icon">
        <?php else: ?>
            <p>No weather icon available.</p>
        <?php endif; ?>
        <p><strong><?php echo $weatherDescription; ?></strong></p>
        <p><strong>Temperature:</strong> <?php echo $temperature; ?>°C</p>
        <p><strong>Humidity:</strong> <?php echo $humidity; ?>%</p>
    </div>

    <button id="startButton">Accompany Me</button>
    <div id="map"></div>

    <script src="dist_leaflet.js"></script>
    <script src="dist_leaflet-routing-machine.js"></script>
    <script>
        var attractionLatLng = [<?php echo htmlspecialchars($attraction_longitude); ?>, <?php echo htmlspecialchars($attraction_latitude); ?>];

        // Initialize the map
        var map = L.map('map').setView(attractionLatLng, 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

        // Add a marker for the attraction
        var attractionIcon = L.icon({
            iconUrl: 'images/mark.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34]
        });
        var attractionMarker = L.marker(attractionLatLng, { icon: attractionIcon }).addTo(map);
        attractionMarker.bindPopup('<b><?php echo htmlspecialchars($attraction_name); ?></b>').openPopup();

        var routeControl;

        // "Start" button event listener
        document.getElementById('startButton').addEventListener('click', function() {
            map.locate({ setView: true, maxZoom: 15 });
            map.on('locationfound', function(e) {
                var userLatLng = e.latlng;
                L.circleMarker(userLatLng, { color: 'blue', radius: 8 }).addTo(map).bindPopup("Your Location").openPopup();

                if (routeControl) { map.removeControl(routeControl); }
                routeControl = L.Routing.control({
                    waypoints: [userLatLng, attractionLatLng],
                    routeWhileDragging: true,
                }).addTo(map);
            });

            map.on('locationerror', function(e) {
                alert("Unable to retrieve your location: " + e.message);
            });
        });
    </script>

    <?//php require_once "footer.php"; ?>
</body>
</html>
