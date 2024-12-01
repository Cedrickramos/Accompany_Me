<?php
session_start();

// Set the correct content-type header
// header('Content-Type: text/html; charset=UTF-8');


// Ensure the database connection uses UTF-8
// $conn->set_charset("utf8mb4_general_ci");

// require_once "../config.php";
require_once "sideBar.php";

// Ensure admin is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// $conn = new mysqli('localhost', 'root', '', 'accompanyme');

// kesug
$conn = new mysqli('sql307.infinityfree.com', 'if0_36896748', 'rzQg0dnCh2BT', 'if0_36896748_accompanyme');

// infinity
// $conn = new mysqli('sql202.infinityfree.com', 'if0_37495817', 'TQY8mKoPDq', 'if0_37495817_accompanyme');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// ----------------------------------------------------------------------------to ensure special characters like ñ and ' can be read--------------------------------------------------------------------------------------
if (!$conn->set_charset("utf8mb4")) {
    // Handle error if setting charset fails
    echo "Error loading character set utf8mb4: " . $conn->error;
}

// Get search input from the user
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Start building SQL query
$sql = "
SELECT 
    r.rating, 
    u.uname, 
    COALESCE(ac.city, rc.city) AS city, 
    a.attraction_name, 
    rest.resto_name,
    r.message, 
    r.images, 
    r.created_at 
FROM reviews r
LEFT JOIN users u ON r.uid = u.uid
LEFT JOIN attractions a ON r.attr_id = a.attr_id
LEFT JOIN cities ac ON a.city_id = ac.city_id
LEFT JOIN restaurants rest ON r.resto_id = rest.resto_id
LEFT JOIN cities rc ON rest.city_id = rc.city_id
";

// Add search conditions if search term is provided
if (!empty($search)) {
    $sql .= " WHERE 
        (a.attraction_name LIKE ? 
        OR rest.resto_name LIKE ? 
        OR u.uname LIKE ? 
        OR r.message LIKE ? 
        OR ac.city LIKE ? 
        OR rc.city LIKE ?)";
}

// Prepare the statement
$stmt = $conn->prepare($sql);

// Bind parameters if there's a search term
if (!empty($search)) {
    $searchParam = '%' . $search . '%';
    $stmt->bind_param("ssssss", $searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam);
}

// Execute the query
if ($stmt->execute()) {
    $result = $stmt->get_result();
} else {
    echo "Error executing query: " . $stmt->error;
}
?>

<style>
    .add-button:hover { background-color: #555; }
    .table-container { margin: 20px; }
    .table-container table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    .table-container th, .table-container td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    .table-container th { background-color: #333; color: #fff; }
    .table-container tr:nth-child(even) { background-color: #f2f2f2; }
    .table-container td img { max-width: 100px; height: auto; cursor: pointer; }

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
        max-width: 500px; /* Fixed maximum width */
        max-height: 500px; /* Fixed maximum height */
        width: auto;      /* Maintain aspect ratio */
        height: auto;     /* Maintain aspect ratio */
        border: 2px solid #fff;
        border-radius: 8px;
        object-fit: contain; /* Ensures consistent image fitting */
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

    .modal-nav {
        background: #333;
        color: #fff;
        border: none;
        padding: 10px 20px;
        font-size: 20px;
        cursor: pointer;
        border-radius: 5px;
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
    }

    #prev-image {
        right: calc(50% + 270px); /* Adjust position relative to image size */
    }

    #next-image {
        left: calc(50% + 270px); /* Adjust position relative to image size */
    }

    .modal-nav:hover {
        background: #555;
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

<div class="table-container">
    <h2>Manage Reviews</h2>

    <!-- Search Form -->
    <form class="search-form" method="GET" action="manage_reviews.php">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by ID or Username">
        <button type="submit">Search</button>
        <!-- Add a Refresh button -->
        <button type="button" id="refreshBtn" class="btn btn-outline-secondary">Refresh Search</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Rating</th>
                <th>Username</th>
                <th>City</th>
                <th>Attraction Name</th>
                <th>Restaurant Name</th>
                <th>Message</th>
                <th>Images</th>
                <th>Date/Time</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows === 0): ?>
            <tr>
                <td colspan="9">No reviews found.</td>
            </tr>
        <?php else: ?>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['rating']); ?></td>
                <td><?php echo htmlspecialchars($row['uname']); ?></td>
                <td><?php echo htmlspecialchars($row['city']); ?></td>
                <td><?php echo $row['attraction_name'] ? htmlspecialchars($row['attraction_name']) : 'N/A'; ?></td>
                <td><?php echo $row['resto_name'] ? htmlspecialchars($row['resto_name']) : 'N/A'; ?></td>
                <td><?php echo htmlspecialchars($row['message']); ?></td>
                <td>
                    <?php 
                    $images = json_decode($row['images']);
                    if (!empty($images)) {
                        foreach ($images as $index => $image) {
                            echo '<img src="../' . htmlspecialchars($image) . '" alt="Review Image" class="clickable-image" data-images=\''.json_encode($images).'\' data-index="'.$index.'">';
                        }
                    }
                    ?>
                </td>
                <td><?php echo date('F j, Y, g:i a', strtotime($row['created_at'])); ?></td>
            </tr>
            <?php endwhile; ?>
        <?php endif; ?>

        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal" id="image-modal">
    <button id="prev-image" class="modal-nav">&lt;</button>
    <img id="modal-image" src="" alt="Popup Image">
    <button id="next-image" class="modal-nav">&gt;</button>
    <span class="modal-close" id="modal-close">&times;</span>
</div>

<script>
    const modal = document.getElementById('image-modal');
    const modalImage = document.getElementById('modal-image');
    const modalClose = document.getElementById('modal-close');
    const prevImage = document.getElementById('prev-image');
    const nextImage = document.getElementById('next-image');

    let currentImages = [];
    let currentIndex = 0;

    // Open modal on image click
    document.querySelectorAll('.clickable-image').forEach(img => {
        img.addEventListener('click', () => {
            currentImages = JSON.parse(img.getAttribute('data-images'));
            currentIndex = parseInt(img.getAttribute('data-index'));
            updateModalImage();
            modal.style.display = 'flex';
        });
    });

    // Close modal on close button or background click
    modalClose.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    // Navigate to previous image
    prevImage.addEventListener('click', () => {
        currentIndex = (currentIndex > 0) ? currentIndex - 1 : currentImages.length - 1;
        updateModalImage();
    });

    // Navigate to next image
    nextImage.addEventListener('click', () => {
        currentIndex = (currentIndex < currentImages.length - 1) ? currentIndex + 1 : 0;
        updateModalImage();
    });

    // Update modal image source
    function updateModalImage() {
        modalImage.src = '../' + currentImages[currentIndex];
    }

    // Refresh button functionality
    document.getElementById('refreshBtn').addEventListener('click', function() {
        document.querySelector('input[name="search"]').value = '';  // Clear search input
        document.querySelector('.search-form').submit();  // Submit the form
    });
</script>

<?php $conn->close(); ?>
