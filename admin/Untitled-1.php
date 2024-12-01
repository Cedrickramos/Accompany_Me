<?php
// require_once "navbar.php";
require_once "../config.php";

// error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$attr_id = $_GET['id'] ?? '';

if ($attr_id) {
    // Fetch the existing attraction details
    $stmt = $conn->prepare("SELECT a.*, c.city FROM attractions a JOIN cities c ON a.city_id = c.city_id WHERE a.attr_id = ?");
    $stmt->bind_param("i", $attr_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $attraction = $result->fetch_assoc();
    } else {
        echo "<p>Attraction not found.</p>";
        exit;
    }
} else {
    echo "<p>No attraction ID provided.</p>";
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $attraction_name = $_POST['attraction_name'];
    $description = $_POST['description'];
    $entry_fee = $_POST['entrance_fee'];
    $parking = $_POST['parking'];
    $dining = $_POST['dining'];
    $operating_hours_from = $_POST['operating_hours_from'];
    $operating_hours_to = $_POST['operating_hours_to'];
    $contact_info = $_POST['contact_details'];
    $historical_significance = $_POST['history'];
    
    // Check if a new image is being uploaded
if ($_FILES['attraction_image']['name']) {
    $image = $_FILES['attraction_image']['name'];
    $target_dir = "../images/";
    $target_file = $target_dir . basename($image);
    move_uploaded_file($_FILES['attraction_image']['tmp_name'], $target_file);

    // Ensure the correct path is saved in the database
    $image = $target_dir . basename($image);
} else {
    // If no new image is uploaded, keep the existing filename
    $image = $attraction['image'];
}

$stmt = $conn->prepare("UPDATE attractions SET attraction_name = ?, description = ?, entrance_fee = ?, parking = ?, dining = ?, operating_hours_from = ?, operating_hours_to = ?, contact_details = ?, history = ?, image = ? WHERE attr_id = ?");
$stmt->bind_param("ssssssssssi", $attraction_name, $description, $entry_fee, $parking, $dining, $operating_hours_from, $operating_hours_to, $contact_info, $historical_significance, $image, $attr_id);

if ($stmt->execute()) {
    echo "<p>Attraction updated successfully.</p>";
    header ("location: manage_attractions.php");

    // Update destinations if an entry exists for this attraction
    $updateDestStmt = $conn->prepare("UPDATE destinations SET attraction_name = ?, image = ? WHERE dest_id = ?");
    $updateDestStmt->bind_param("ssi", $attraction_name, $image, $attr_id); // Assuming attr_id is the same as dest_id

    if ($updateDestStmt->execute()) {
        echo "<p>Destinations table updated successfully.</p>";
    } else {
        echo "<p>Error updating destinations table.</p>";
    }

} else {
    echo "<p>Error updating attraction.</p>";
}

}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Attraction</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .form-container { 
            max-width: 600px; 
            margin: 0 auto; 
            padding: 20px; 
            background-color: #f9f9f9; 
            border-radius: 8px; 
        }

        .form-container label { 
            display: block; 
            margin-bottom: 10px; 
            font-weight: bold; 
        }

        .form-container input[type="text"], 
        .form-container textarea, 
        .form-container select { 
            width: 100%; 
            padding: 10px; 
            margin-bottom: 20px; 
            border: 1px solid #ccc; 
            border-radius: 4px; 
        }
        
        .form-container input[type="file"] { 
            margin-bottom: 20px; 
        }
       
        .form-container input[type="submit"] { 
            background-color: #333; 
            color: #fff; 
            padding: 10px 20px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
        }

        .form-container input[type="submit"]:hover { 
            background-color: #555; 
        }
    </style>
</head>
<body>
<div class="admin-page">
    <?php require_once "sideBar.php"; ?>
    <div class="main-content">
        <h2>Edit Attraction</h2>
        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <!-- Form fields remain unchanged -->
                <label for="city">City:</label>
                <input type="text" id="city" value="<?php echo htmlspecialchars($attraction['city']); ?>" readonly>

                <label for="attraction_name">Attraction Name</label>
                <input type="text" name="attraction_name" placeholder="Attraction Name" value="<?php echo htmlspecialchars($attraction['attraction_name']); ?>" required>

                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($attraction['description']); ?></textarea>

                <label for="entry_fee">Entry Fee:</label>
                <input type="text" id="entrance_fee" name="entrance_fee" value="<?php echo htmlspecialchars($attraction['entrance_fee']); ?>">

                <label for="parking">Parking:</label>
                <select id="parking" name="parking">
                    <option value="Available" <?php echo ($attraction['parking'] == 'Available') ? 'selected' : ''; ?>>Available</option>
                    <option value="Not Available" <?php echo ($attraction['parking'] == 'Not Available') ? 'selected' : ''; ?>>Not Available</option>
                    <option value="Unspecified" <?php echo ($attraction['parking'] == 'Unspecified') ? 'selected' : ''; ?>>Unspecified</option>
                </select>

                <label for="dining">Dining:</label>
                <select id="dining" name="dining">
                    <option value="Available" <?php echo ($attraction['dining'] == 'Available') ? 'selected' : ''; ?>>Available</option>
                    <option value="Not Available" <?php echo ($attraction['dining'] == 'Not Available') ? 'selected' : ''; ?>>Not Available</option>
                    <option value="Unspecified" <?php echo ($attraction['dining'] == 'Unspecified') ? 'selected' : ''; ?>>Unspecified</option>
                </select>

                <label for="operating_hours_from">Operating Hours From:</label>
                <input type="text" id="operating_hours_from" placeholder="am" name="operating_hours_from" value="<?php echo htmlspecialchars($attraction['operating_hours_from']); ?>">

                <label for="operating_hours_to">Operating Hours To:</label>
                <input type="text" id="operating_hours_to" placeholder="pm" name="operating_hours_to" value="<?php echo htmlspecialchars($attraction['operating_hours_to']); ?>">

                <label for="contact_info">Contact Info:</label>
                <input type="text" id="contact_details" name="contact_details" value="<?php echo htmlspecialchars($attraction['contact_details']); ?>">

                <label for="historical_significance">Historical Significance:</label>
                <textarea id="history" name="history" rows="5"><?php echo htmlspecialchars($attraction['history']); ?></textarea>

                <label for="attraction_image">Image:</label>
                <input type="file" id="attraction_image" name="attraction_image" onchange="previewImage(event)">

                <!-- Add this img element for previewing the uploaded image -->
                <img id="image_preview" alt="Image Preview" style="max-width: 100%; height: auto; display: none;">

               

                <button type="submit">Update Attraction</button>
            </form>
            <a href="attractions.php?city=<?php echo htmlspecialchars($attraction['city_id']); ?>">Back to Attractions</a>
        </div>
    </div>
</div>

<script>
function previewImage(event) {
    const imagePreview = document.getElementById('image_preview');
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            imagePreview.src = e.target.result;
            imagePreview.style.display = 'block'; // Show the preview
        }
        reader.readAsDataURL(file);
    } else {
        imagePreview.style.display = 'none'; // Hide the preview if no file is selected
    }
}
</script>


</body>
</html>