<?php
session_start(); // Start the session
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Basic Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /*------------------------ Navbar----------- */
        .navbar {
            background: #2C5F2D; 
            height: 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 20px;
            position: sticky;
            top: 0;
            overflow: hidden;
            z-index: 1000;
        }

        .navbar .logo {
            font-size: 1.5rem;
            font-weight: bold;
            display: flex;
            align-items: center;
        }

        .navbar img {
            height: 40px; /* to adjust logo height */
            margin-right: 10px; 
        }

        .navbar .logo a {
            color: white; 
            text-decoration: none; 
        }

        .nav-links {
            list-style: none;
            display: flex;
            align-items: center;
            margin: 0;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 15px;
            font-size: 1rem;
            transition: background-color 0.3s, color 0.3s;
        }

        .nav-links a:hover,
        .nav-links a:focus {
            color: #FFD700; 
        }


        .nav-links a.active {
            background-color: #214A22; 
            color: #e6cc00;
        }

        .menu-button {
            display: none;
            cursor: pointer;
            flex-direction: column;
        }

        .menu-button div {
            width: 30px;
            height: 3px;
            background-color: white;
            margin: 5px;
            transition: all 0.3s ease;
        }

        /* Responsive for screen sizing */
        @media screen and (max-width: 850px) {
            .nav-links {
                display: flex; 
                position: fixed;
                top: 60px; 
                right: 0;
                width: 250px;
                height: auto;
                background: #2C5F2D;
                z-index: 999; 
                transform: translateX(100%); 
                transition: transform 0.3s ease-in-out;
                padding: 20px;
                flex-direction: column;
                align-items: center;
                overflow-y: auto; 
            }

            .nav-links.show {
                transform: translateX(0); 
            }

            .menu-button {
                display: flex; 
            }

            .nav-links li {
                margin: 20px 0; 
                opacity: 0;
                transition: opacity 0.3s ease-in-out;
            }

            .nav-links.show li {
                opacity: 1; 

            }
        }

        .menu-button.open div {
            background: #ccc; 
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="logo">
            <img src="images/logo.jpg" alt="AccompanyMe Logo" style="border-radius: 100px;">
            <a href="index.php">AccompanyMe</a>
        </div>
        <ul class="nav-links">
            <b><li><a href="index.php">HOME</a></li></b>
            <b><li><a href="attractions.php">ATTRACTIONS</a></li></b>
            <b><li><a href="lagunamap.php">MAP</a></li></b>
            <b><li><a href="about.php">ABOUT</a></li></b>
            <b><li><a href="contact.php">CONTACT</a></li></b>
            <?php if (isset($_SESSION['uid'])): ?>
                <b><li><a href="confirm_logout.php">LOGOUT</a></li></b>
            <?php else: ?>
                <b><li><a href="signin.php">SIGN IN</a></li></b>
            <?php endif; ?>

        </ul>


        <div class="menu-button">
            <div class="line1"></div>
            <div class="line2"></div>
            <div class="line3"></div>
        </div>
    </nav>

    <!-- for navbar pag lumiit screen -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // screen responsiveness
            const menuButton = document.querySelector('.menu-button');
            const navMenu = document.querySelector('.nav-links');

            menuButton.addEventListener('click', function() {
                navMenu.classList.toggle('show');
                menuButton.classList.toggle('open');
            });

            // highlight the current page
            const currentPage = window.location.pathname.split('/').pop(); // Get the current page from the URL
            const navLinks = document.querySelectorAll('.nav-links a');

            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPage) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
