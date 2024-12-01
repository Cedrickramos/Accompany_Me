<?php
// Get the current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>

.admin-page {
    display: flex;
    height: 650vh;
    /* overflow: hidden; */
}

.sidebar {
    width: 150px;
    background-color: #333;
    color: #fff;
    display: flex;
    flex-direction: column;
    padding: 10px;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    z-index: 1000; /* Ensure it's above other content */
    pointer-events: 500px; /* Ensure pointer events are enabled */
}

/* Sidebar visible on hover */
.sidebar.visible {
    transform: translateX(0);
}

/* Remove bullets from sidebar list */
.sidebar ul {
    list-style-type: none; /* Remove bullets */
    padding: 0; /* Remove padding */
    margin: 0; /* Remove margin */
}

/* Sidebar link styling */
.sidebar a.active {
    background-color: #4CAF50; /* Change to your preferred active color */
    color: #fff;
    font-weight: bold;
}

/* Sidebar link styling (unchanged) */
.sidebar a {
    color: #fff;
    text-decoration: none;
    padding: 15px;
    display: block;
    border-radius: 4px;
}

/* Sidebar link hover effect */
.sidebar a:hover {
    background-color: #555;
}

/* Main content styling */
.main-content {
    flex: 3;
    padding: 20px;
    background-color: #f9f9f9;
    margin-left: 0; /* Full width of the page when sidebar is hidden */
    z-index: 1; /* Ensure it's below the sidebar */
    transition: margin-left 0.10s ease;
}
</style>

<script>
document.addEventListener('mousemove', function(event) {
    const sidebar = document.querySelector('.sidebar');
    if (event.clientX < 105) {
        sidebar.classList.add('visible');
    } else {
        sidebar.classList.remove('visible');
    }
});

</script>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <ul>
        <li><a href="manage_users.php" class="<?php echo ($current_page == 'manage_users.php') ? 'active' : ''; ?>">Users</a></li>
        <li><a href="manage_attractions.php" class="<?php echo ($current_page == 'manage_attractions.php') ? 'active' : ''; ?>">Attractions</a></li>
        <li><a href="manage_destinations.php" class="<?php echo ($current_page == 'manage_destinations.php') ? 'active' : ''; ?>">Destinations</a></li>
        <li><a href="manage_restaurants.php" class="<?php echo ($current_page == 'manage_restaurants.php') ? 'active' : ''; ?>">Restaurants</a></li>
        <li><a href="manage_reviews.php" class="<?php echo ($current_page == 'manage_reviews.php') ? 'active' : ''; ?>">Reviews</a></li>
        <li><a href="manage_messages.php" class="<?php echo ($current_page == 'manage_messages.php') ? 'active' : ''; ?>">Messages</a></li>
        <li><a href="manage_admin.php" class="<?php echo ($current_page == 'manage_admin.php') ? 'active' : ''; ?>">Admin</a></li>
        <br>
        <hr></hr>
        </br>
        <li><a href="logout_admin.php" class="<?php echo ($current_page == 'logout_admin.php') ? 'active' : ''; ?>">Logout</a></li>
    </ul>
</div>