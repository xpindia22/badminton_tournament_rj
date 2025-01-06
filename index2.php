<?php
include 'header.php';
?> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 600px;
            padding: 20px;
            text-align: center;
            margin: 10px auto; /* Reduced space below the fixed header */
        }

        h1 {
            color: #007bff;
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        p {
            color: #555;
            line-height: 1.5;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        label {
            font-weight: bold;
            color: #333;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .btn {
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 15px;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .footer {
            margin-top: 20px;
            font-size: 0.8rem;
            color: #666;
        }

        .footer p {
            margin: 5px 0;
        }

        b {
            color: #333;
        }

        .slideshow-container {
            position: relative;
            max-width: 100%;
            margin: auto;
            border-radius: 8px;
            overflow: hidden;
        }

        .slideshow-container img {
            width: 100%;
            display: none;
        }

        .slideshow-container img.active {
            display: block;
        }
    </style>
    <script>
        let currentIndex = 0;

        function showSlides() {
            const slides = document.querySelectorAll(".slideshow-container img");
            slides.forEach((slide, index) => {
                slide.classList.remove("active");
                if (index === currentIndex) {
                    slide.classList.add("active");
                }
            });
            currentIndex = (currentIndex + 1) % slides.length;
        }

        document.addEventListener("DOMContentLoaded", () => {
            showSlides(); // Show the first slide immediately
            setInterval(showSlides, 3000); // Change image every 3 seconds
        });
    </script>
</head>
<body>
    <!-- Header is included and remains fixed at the top -->
    <div class="container">
        <div class="slideshow-container">
            <img src="images/tai (1).jpg" alt="Tai Tzu Ying" class="active">
            <img src="images/tai (2).jpg" alt="Tai Tzu Ying">
            <img src="images/tai (3).jpg" alt="Tai Tzu Ying">
            <img src="images/tai (4).jpg" alt="Tai Tzu Ying">
            <img src="images/tai (5).jpg" alt="Tai Tzu Ying">
        </div>
        <h1>Welcome to Badminton Tournament Login</h1>
        <p><em>Beta release</em></p>
        <?php if (isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        <div class="footer">
            <p>Please click on the links above to start. You can browse as a Guest also.</p>
            <p>As a Visitor, you can view the matches and results only. For increased functionality, please register for free.</p>
            <p>Email or WhatsApp us for any queries. Kindly report any errors or bugs to us at:</p>

            <p>You are free to create tournaments, categories, add new players, and edit matches. Suggest a feature if you want something new!</p>
            <p><b>Dr. Robert James</b>      -----  <b>WhatsApp:</b> 91-7432001215 -----       <b>Email:</b> xpindia@gmail.com</p>
        </div>
    </div>
</body>
</html>
