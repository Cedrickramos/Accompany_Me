<?php
require_once "config.php";
ini_set('display_errors', 1);
error_reporting(E_ALL);


// Fetch all attractions with their details from the database
$stmt = $conn->prepare("SELECT attr_id, attraction_name, longitude, latitude FROM attractions");
$stmt->execute();
$result = $stmt->get_result();

$attractions = [];
if ($result->num_rows > 0) {
    // Fetch all attractions into an array
    while ($row = $result->fetch_assoc()) {
        $attractions[] = $row;
    }
}

// Fetch all restaurants with their details from the database
$stmt = $conn->prepare("SELECT resto_id, resto_name, resto_longitude, resto_latitude FROM restaurants");
$stmt->execute();
$result = $stmt->get_result();

$restaurants = [];
if ($result->num_rows > 0) {
    // Fetch all restaurants into an array
    while ($row = $result->fetch_assoc()) {
        $restaurants[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attractions and Restaurants Map</title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <!-- <link rel="stylesheet" href="dist_leaflet.css" /> -->

    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css" />
    <!-- <link rel="stylesheet" href="dist_leaflet-routing-machine.css" /> -->
    <link rel="stylesheet" href="styles.css" />
    <style>
        #map {
            height: 850px;
            width: 100%;
        }

        .weather-info {
            position: absolute;
            width: 100px;
            top: 100px;
            right: 10px;
            background-color: white;
            opacity: 90%;
            padding: 10px;
            border-radius: 8px;
            z-index: 1000;
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.9);
        }

        .weather-info img {
            width: 50px;
            height: 50px;
        }
        
    </style>
</head>
<body>
    <?php require_once "navbar.php"; ?>
    <h2 style="padding-left: 30px;">Attractions and Restaurants Map</h2>

    <div id="map"></div> <!-- Map container -->

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <!-- <script src="dist_leaflet.js"></script> -->

    <script src="https://unpkg.com/leaflet/dist/leaflet-routing-machine.js"></script>
    <!-- <script src="dist_leaflet-routing-machine.js"></script> -->
    <script>
        // Fetch all attractions and restaurants data from PHP
        var attractions = <?php echo json_encode($attractions); ?>;
        var restaurants = <?php echo json_encode($restaurants); ?>;

        // Initialize the map
        var map = L.map('map').setView([14, 121], 8.9); // Initial zoom level set to the world view

        // Add OpenStreetMap tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
        }).addTo(map);

        // Attraction markers (with mark.png)
        // Attraction markers (with mark.png)
attractions.forEach(function(attraction) {
    var marker = L.marker([attraction.longitude, attraction.latitude], {
        icon: L.icon({
            iconUrl: 'images/mark.png', // Custom icon for attractions
            iconSize: [30, 37],  // Adjust marker size
            iconAnchor: [20, 36],
            popupAnchor: [0, -32]
        })
    }).addTo(map);

    marker.bindPopup(`<b>${attraction.attraction_name}</b><br>Loading weather...`);

    // Fetch weather data dynamically for each attraction marker
    fetch(`http://api.weatherapi.com/v1/current.json?key=a7169d65775d4f55b6f104707242411&q=${attraction.longitude},${attraction.latitude}`)
        .then(response => response.json())
        .then(data => {
            var weatherDescription = data.current?.condition?.text || "No data";
            var temperature = data.current?.temp_c || "N/A";
            var humidity = data.current?.humidity || "N/A";
            var weatherIcon = data.current?.condition?.icon || "";

            var weatherHTML = `
                <b>${attraction.attraction_name}</b><br>
                <img src="https:${weatherIcon}" alt="Weather Icon"><br>
                <strong>Weather:</strong> ${weatherDescription}<br>
                <strong>Temperature:</strong> ${temperature}°C<br>
                <strong>Humidity:</strong> ${humidity}%<br>
                <a href="attraction_details.php?attr_id=${attraction.attr_id}" class="btn btn-details">Details</a>
            `;

            // Update the marker popup with weather info and buttons
            marker.setPopupContent(weatherHTML);
        })
        .catch(error => {
            console.error('Error fetching weather data:', error);
        });
});

// Restaurant markers (with resto.png)
restaurants.forEach(function(restaurant) {
    var marker = L.marker([restaurant.resto_longitude, restaurant.resto_latitude], {
        icon: L.icon({
            iconUrl: 'images/resto.webp', // Custom icon for restaurants
            iconSize: [40, 43],  // Adjust marker size
            iconAnchor: [20, 36],
            popupAnchor: [0, -32]
        })
    }).addTo(map);

    marker.bindPopup(`<b>${restaurant.resto_name}</b><br>Loading weather...`);

    // Fetch weather data dynamically for each restaurant marker
    fetch(`http://api.weatherapi.com/v1/current.json?key=a7169d65775d4f55b6f104707242411&q=${restaurant.resto_longitude},${restaurant.resto_latitude}`)
        .then(response => response.json())
        .then(data => {
            var weatherDescription = data.current?.condition?.text || "No data";
            var temperature = data.current?.temp_c || "N/A";
            var humidity = data.current?.humidity || "N/A";
            var weatherIcon = data.current?.condition?.icon || "";

            var weatherHTML = `
                <b>${restaurant.resto_name}</b><br>
                <img src="https:${weatherIcon}" alt="Weather Icon"><br>
                <strong>Weather:</strong> ${weatherDescription}<br>
                <strong>Temperature:</strong> ${temperature}°C<br>
                <strong>Humidity:</strong> ${humidity}%<br>
                <a href="restaurant_details.php?resto_id=${restaurant.resto_id}" class="btn btn-details">Details</a>
            `;

            // Update the marker popup with weather info and buttons
            marker.setPopupContent(weatherHTML);
        })
        .catch(error => {
            console.error('Error fetching weather data:', error);
        });
});


        // Adjust map bounds to include all markers
        var bounds = attractions.concat(restaurants).map(a => [a.longitude, a.latitude]);
        map.fitBounds(bounds);
    </script>

    <?//php require_once "footer.php"; ?>
</body>
</html>
