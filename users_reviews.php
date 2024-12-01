<?php
require_once "config.php";
if (!$conn->set_charset("utf8mb4")) {
    // Handle error if setting charset fails
    echo "Error loading character set utf8mb4: " . $conn->error;
}

require_once "navbar.php";

// Set the correct content-type header
// header('Content-Type: text/html; charset=UTF-8');

// Ensure the database connection uses UTF-8
// $conn->set_charset("utf8mb4");

// Check if the user is logged in
$uid = $_SESSION['uid'] ?? null;
$uname = $_SESSION['uname'] ?? null;

// Get the attr_id from the URL
$attr_id = $_GET['attr_id'] ?? '';

if (empty($attr_id)) {
    echo "Invalid attraction ID.";
    exit;
}

// Fetch attraction details based on attr_id, including the city
$stmt = $conn->prepare("
    SELECT a.*, c.city 
    FROM attractions a 
    JOIN cities c ON a.city_id = c.city_id 
    WHERE a.attr_id = ?
");
$stmt->bind_param("i", $attr_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $attraction = $result->fetch_assoc();
} else {
    echo "Attraction not found.";
    exit;
}

// Existing PHP code remains unchanged

// Fetch reviews
$reviews_stmt = $conn->prepare("
    SELECT r.rating, u.uname, r.message, r.images, r.created_at 
    FROM reviews r 
    JOIN users u ON r.uid = u.uid 
    WHERE r.attr_id = ? 
    ORDER BY r.created_at DESC
");
$reviews_stmt->bind_param("i", $attr_id);
$reviews_stmt->execute();
$reviews = $reviews_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews for <?php echo htmlspecialchars($attraction['attraction_name']); ?></title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Background Blur Effect */
body.popup-active .container {
    filter: blur(10px); /* Blur the container */
    pointer-events: none; /* Disable interactions with the blurred background */
}

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .review-item {
            background-color: white;
            padding: 30px 60px;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: relative; /* To position the date absolutely */
        }
        .review-rating {
            color: #f39c12;
        }
        .review-images img {
            width: 100px;
            border-radius: 4px;
            margin-right: 15px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        .review-images img:hover {
            transform: scale(1.1);
        }
        .review-date {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 0.9em;
            color: #888;
        }
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 900;
            width: 500px;
            height: 500px;
            overflow: hidden;
        }
        .popup-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: calc(100% - 50px);
        }
        .popup img {
            max-width: 100%;
            max-height: 100%;
            display: block;
            margin: 0 auto;
        }
        .popup-message {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            max-height: 150px;
            overflow-y: auto;
            padding: 10px;
            background-color: #f4f4f4;
            text-align: center;
            border-top: 1px solid #ddd;
        }
        .popup-message p {
            margin: 0;
            font-size: 14px;
            color: #333;
        }
        .popup .close {
            position: absolute;
            top: 10px;
            right: 10px;
            height: 46px;
            background-color: transparent;
            color: grey;
            font-size: 45px;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 50%;
            z-index: 900;
        }
        .arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 50%;
            font-size: 18px;
        }
        .arrow-left {
            left: 10px;
        }
        .arrow-right {
            right: 10px;
        }

        .short-text, .full-text {
    display: inline;
}

a.show-more, a.show-less {
    color: black;
    text-decoration: none;
    margin-left: 5px;
    cursor: pointer;
    font-weight: bold;
}

a.show-more:hover, a.show-less:hover {
    text-decoration: underline;
}

    </style>
</head>
<body>
    <div class="container">
        <h1>Reviews for <?php echo htmlspecialchars($attraction['attraction_name']); ?>, <?php echo htmlspecialchars($attraction['city']); ?></h1>
        
        <?php if ($reviews->num_rows > 0): ?>
            <?php while ($row = $reviews->fetch_assoc()): ?>
                <div class="review-item">
                    <div class="review-rating">
                        <?php echo htmlspecialchars($row['rating']); ?> &#11088;
                    </div>
                    <p><strong>User:</strong> <?php echo htmlspecialchars($row['uname']); ?></p>
                    <p>
                        <strong>Review:</strong>
                        <span class="short-text">
                            <?php echo htmlspecialchars(mb_substr($row['message'], 0, 70)); ?>
                            <?php if (mb_strlen($row['message']) > 70): ?>
                                ...
                                <a href="javascript:void(0)" class="show-more" onclick="toggleText(this)">Show More</a>
                            <?php endif; ?>
                        </span>
                        <span class="full-text" style="display: none;">
                            <?php echo htmlspecialchars($row['message']); ?>
                            <a href="javascript:void(0)" class="show-less" onclick="toggleText(this)">Show Less</a>
                        </span>
                    </p>
                    <div class="review-images">
                        <?php 
                            $images = json_decode($row['images'], true);
                            if (!empty($images)) {
                                foreach ($images as $index => $image) {
                                    echo '<img src="' . htmlspecialchars($image) . '" alt="Review Image" data-images="' . htmlspecialchars(json_encode($images)) . '" data-index="' . $index . '" data-message="' . htmlspecialchars($row['message'], ENT_QUOTES) . '" onclick="handlePopup(this)">';
                                }
                            }
                        ?>
                    </div>
                    <div class="review-date">
                        <?php echo date('F j, Y, g:i a', strtotime($row['created_at'])); ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No reviews found for this attraction.</p>
        <?php endif; ?>
    </div>

    <div class="popup" id="imagePopup">
        <button class="close" onclick="hidePopup()">×</button>
        <div class="popup-content">
            <button class="arrow arrow-left" onclick="prevImage()">&#8592;</button>
            <img id="popupImage" src="" alt="Popup Image">
            <button class="arrow arrow-right" onclick="nextImage()">&#8594;</button>
        </div>
        <div class="popup-message">
            <p id="popupMessage"></p>
        </div>
    </div>

    <script>
        let currentImageIndex = 0;
        let imagesArray = [];
        let fullMessageText = '';

        function toggleText(element) {
        const shortText = element.closest('.review-item').querySelector('.short-text');
        const fullText = element.closest('.review-item').querySelector('.full-text');

        if (shortText.style.display === 'none') {
            shortText.style.display = 'inline';
            fullText.style.display = 'none';
        } else {
            shortText.style.display = 'none';
            fullText.style.display = 'inline';
        }
    }

        function handlePopup(element) {
            const images = JSON.parse(element.getAttribute('data-images'));
            const index = parseInt(element.getAttribute('data-index'), 10);
            const message = element.getAttribute('data-message');
            showPopup(images, index, message);
        }

        function showPopup(images, index, message) {
            imagesArray = images;
            currentImageIndex = index;
            fullMessageText = message;
            updatePopup();
            document.getElementById('imagePopup').style.display = 'block';
            document.body.classList.add('popup-active'); // Add blur
        }
        function updatePopup() {
            document.getElementById('popupImage').src = imagesArray[currentImageIndex];
            document.getElementById('popupMessage').innerText = fullMessageText;
        }

        function hidePopup() {
            document.getElementById('imagePopup').style.display = 'none';
            document.body.classList.remove('popup-active'); // Remove blur
        }

        function nextImage() {
            currentImageIndex = (currentImageIndex + 1) % imagesArray.length;
            updatePopup();
        }

        function prevImage() {
            currentImageIndex = (currentImageIndex - 1 + imagesArray.length) % imagesArray.length;
            updatePopup();
        }
    </script>
</body>
</html>
