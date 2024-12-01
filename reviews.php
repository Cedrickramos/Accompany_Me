<?php
require_once "config.php";
session_start();

// Set the correct content-type header
header('Content-Type: text/html; charset=UTF-8');

// Ensure user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['uid'];
$attr_id = $_GET['attr_id'] ?? null;
$resto_id = $_GET['resto_id'] ?? null;

// Redirect if both IDs are missing
if (!$attr_id && !$resto_id) {
    echo "Invalid review request. Please specify an attraction or restaurant.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = $_POST['rating'];
    $message = $_POST['message'];
    $existingImages = json_decode($_POST['existing_images'], true) ?? [];
    $images = $existingImages;

    // Handle image uploads (max 10 images total)
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if (count($images) < 10) { // Limit total to 10 images
                $file_name = $_FILES['images']['name'][$key];
                $file_tmp = $_FILES['images']['tmp_name'][$key];
                $file_path = "images/" . basename($file_name);

                // Debugging: Check if file is uploaded correctly
                if (is_uploaded_file($file_tmp)) {
                    echo "File {$file_name} uploaded successfully.<br>";  // Debug message
                } else {
                    echo "Error uploading file {$file_name}.<br>";  // Debug message
                }

                if (move_uploaded_file($file_tmp, $file_path)) {
                    echo "File {$file_name} moved to {$file_path}.<br>";  // Debug message
                    $images[] = $file_path;
                } else {
                    echo "Failed to move file {$file_name}.<br>";  // Debug message
                }
            }
        }
    } else {
        echo "No images selected for upload.<br>";  // Debug message
    }

    // Convert images array to JSON
    $images_json = json_encode($images);

    // Prepare SQL and check for errors
    $stmt = $conn->prepare("
        INSERT INTO reviews (attr_id, resto_id, uid, rating, message, images) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        die("Database error: " . $conn->error); // Handle SQL preparation failure
    }

    // Bind parameters
    $stmt->bind_param("iiisss", $attr_id, $resto_id, $uid, $rating, $message, $images_json);

    // Execute the query
    if ($stmt->execute()) {
        header("Location: review_sent.php?attr_id=" . ($attr_id ?? $resto_id));
        exit();
    } else {
        echo "Error submitting review: " . $stmt->error;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave a Review</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }
        .main-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h1 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }
        .star-rating {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }
        .star {
            font-size: 40px;
            color: #ccc;
            cursor: pointer;
            transition: color 0.3s ease, transform 0.2s ease;
        }
        .star.selected {
            color: #FFD700;
            transform: scale(1.1);
        }
        textarea, input[type="file"] {
            width: 100%;
            padding: 10px;
            margin: 20px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        textarea:focus, input[type="file"]:focus {
            outline: none;
            border-color: #666;
        }
        button[type="submit"] {
            padding: 12px 20px;
            font-size: 18px;
            background-color: #333;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        button[type="submit"]:hover {
            background-color: #555;
            transform: translateY(-2px);
        }
        button[type="submit"]:active {
            transform: translateY(0);
        }
        .image-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 10px 0;
            justify-content: center;
            position: relative;
        }
        .image-preview img {
            max-width: 100px;
            max-height: 100px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .image-preview img:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .image-preview .remove-button {
            position: absolute;
            top: 5px;
            right: 5px;
            font-weight: bold;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 14px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="main-container">
    <h1>Leave a Review</h1>
    <form method="POST" enctype="multipart/form-data">
        <div class="star-rating" id="star-rating">
            <span class="star" data-value="1">&#9733;</span>
            <span class="star" data-value="2">&#9733;</span>
            <span class="star" data-value="3">&#9733;</span>
            <span class="star" data-value="4">&#9733;</span>
            <span class="star" data-value="5">&#9733;</span>
        </div>
        <input type="hidden" name="rating" id="rating" required>
        <input type="hidden" name="existing_images" id="existing-images" value="[]">
        
        <label for="message">Message:</label>
        <textarea name="message" rows="5" placeholder="Write your review..." required></textarea>

        <label for="images">Upload Images (max 10):</label>
        <input type="file" id="images" accept="image/*" multiple>
        <div id="image-counter">Uploaded: 0/10</div>
        <div class="image-preview" id="image-preview"></div>

        <button type="submit">Submit Review</button>
    </form>
</div>

<script>
const imagesInput = document.getElementById('images');
const imagePreview = document.getElementById('image-preview');
const imageCounter = document.getElementById('image-counter');
const form = document.querySelector('form');
let selectedFiles = []; // Array to store selected files

imagesInput.addEventListener('change', (event) => {
    const files = Array.from(event.target.files);

    files.forEach((file) => {
        if (selectedFiles.length < 10) { // Limit to 10 images
            selectedFiles.push(file);

            // Create image preview container
            const container = document.createElement('div');
            container.classList.add('image-container');
            container.style.position = 'relative'; // Ensure the button can be positioned correctly

            // Create image element
            // Create image element with URL.createObjectURL
            const img = document.createElement('img');
            img.src = encodeURI(URL.createObjectURL(file));  // This encodes special characters like parentheses
            img.alt = file.name;

            // Create remove button
            const removeButton = document.createElement('button');
            removeButton.className = 'remove-button';
            removeButton.textContent = 'X';
            removeButton.onclick = () => {
                selectedFiles = selectedFiles.filter((f) => f !== file);
                container.remove();
                updateCounter();
            };

            // Append image and remove button to the container
            container.appendChild(img);
            container.appendChild(removeButton);
            imagePreview.appendChild(container);
        }
    });

    updateCounter();
});

// Update the hidden input field with the file names or paths
function updateHiddenInput() {
    const existingImages = selectedFiles.map(file => `images/${file.name}`); // You can use file path if needed
    document.getElementById('existing-images').value = JSON.stringify(existingImages);
}

// When the form is submitted, make sure images are passed correctly
form.addEventListener('submit', (event) => {
    event.preventDefault();  // Prevent default form submission for custom handling

    // Check if images are selected
    if (selectedFiles.length === 0) {
        alert("Please select at least one image to upload.");
    } else {
        // Update the hidden input with the selected image names or paths
        updateHiddenInput();

        // Create FormData to send the files
        const formData = new FormData(form);

        // Optionally, submit the form using AJAX (if you want to avoid a page reload)
        fetch(form.action, {
            method: 'POST',
            body: formData,
        })
        .then(response => response.text())
        .then(data => {
            // Handle the response (redirect, success message, etc.)
            window.location.href = "review_sent.php?attr_id=" + (<?php echo $attr_id ?? $resto_id; ?>);
        })
        .catch(error => {
            console.error('Error:', error);
        });

        // Clear the form after submission
        imagesInput.value = '';  // Reset the file input
        selectedFiles = [];  // Clear selected files
        imagePreview.innerHTML = '';  // Remove image previews
        updateCounter();  // Update image counter display
    }
});

// Update image counter display
function updateCounter() {
    imageCounter.textContent = `Uploaded: ${selectedFiles.length}/10`;
}

// Star rating logic
const stars = document.querySelectorAll('.star');
const ratingInput = document.getElementById('rating');

stars.forEach(star => {
    star.addEventListener('mouseover', () => {
        const value = star.getAttribute('data-value');
        highlightStars(value);
    });

    star.addEventListener('mouseout', () => {
        highlightStars(ratingInput.value);
    });

    star.addEventListener('click', () => {
        ratingInput.value = star.getAttribute('data-value');
    });
});

function highlightStars(value) {
    stars.forEach(star => {
        const starValue = star.getAttribute('data-value');
        if (starValue <= value) {
            star.classList.add('selected');
        } else {
            star.classList.remove('selected');
        }
    });
}
</script>

</body>
</html>
