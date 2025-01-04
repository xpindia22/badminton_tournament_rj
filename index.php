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
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100vh;
        }

        .header {
            width: 100%;
            background-color: #333;
            color: white;
            text-align: center;
            padding: 10px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 1200px;
            display: flex;
            overflow: hidden;
            margin-top: 20px;
        }

        .left-section, .right-section {
            width: 50%;
        }

        .left-section {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #007bff;
            padding: 20px;
            position: relative;
        }

        .slideshow-container {
            width: 100%;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
        }

        .slideshow-container img {
            width: 100%;
            display: none;
            border-radius: 8px;
        }

        .slideshow-container img.active {
            display: block;
        }

        .arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 2rem;
            color: white;
            background-color: rgba(0, 0, 0, 0.5);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            text-align: center;
            line-height: 40px;
            cursor: pointer;
        }

        .arrow.left {
            left: 10px;
        }

        .arrow.right {
            right: 10px;
        }

        .right-section {
            padding: 20px;
            text-align: center;
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
    </style>
    <script>
        let currentIndex;

        function showSlides(index) {
            const slides = document.querySelectorAll(".slideshow-container img");
            slides.forEach(slide => slide.classList.remove("active"));
            slides[index].classList.add("active");
        }

        function nextSlide() {
            const slides = document.querySelectorAll(".slideshow-container img");
            currentIndex = (currentIndex + 1) % slides.length;
            showSlides(currentIndex);
        }

        function prevSlide() {
            const slides = document.querySelectorAll(".slideshow-container img");
            currentIndex = (currentIndex - 1 + slides.length) % slides.length;
            showSlides(currentIndex);
        }

        document.addEventListener("DOMContentLoaded", () => {
            const slides = document.querySelectorAll(".slideshow-container img");
            currentIndex = Math.floor(Math.random() * slides.length); // Start with a random image
            showSlides(currentIndex);
            setInterval(nextSlide, 3000); // Change image every 3 seconds
        });
    </script>
</head>
<body>
    <!-- Header Section -->
    <div class="header">
        <?php include 'header.php'; ?>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Left Section with Slideshow -->
        <div class="left-section">
            <div class="slideshow-container">
                <img src="images/tai (1).jpg" alt="Tai Tzu Ying" class="active">
                <img src="images/eric/ej (6).png" alt="Eric James">
                <img src="images/tai (2).jpg" alt="Tai Tzu Ying">
                <img src="images/tai (3).jpg" alt="Tai Tzu Ying">
                <img src="images/eric/ej (1).png" alt="Eric James">
                <img src="images/eric/ej (2).png" alt="Eric James">
                <img src="images/eric/ej (3).png" alt="Eric James">
                <img src="images/eric/ej (4).png" alt="Eric James">
                <img src="images/eric/ej (5).png" alt="Eric James">

                <img src="images/tai (4).jpg" alt="Tai Tzu Ying">
                <img src="images/tai (5).jpg" alt="Tai Tzu Ying">
                <img src="images/tai (6).jpg" alt="Tai Tzu Ying">
                <img src="images/tai (7).jpg" alt="Tai Tzu Ying">
                <img src="images/tai (8).jpg" alt="Tai Tzu Ying">
                <img src="images/eric/ej (7).png" alt="Eric James">
                <img src="images/eric/ej (8).png" alt="Eric James">
                <img src="images/eric/ej (9).png" alt="Eric James">
                <img src="images/eric/ej (10).png" alt="Eric James">
                <img src="images/eric/ej (11).png" alt="Eric James">
                <img src="images/eric/ej (12).png" alt="Eric James">
                <img src="images/eric/ej (13).png" alt="Eric James">
                <img src="images/eric/ej (14).png" alt="Eric James">
                <img src="images/eric/ej (15).png" alt="Eric James">
                <img src="images/eric/ej (16).png" alt="Eric James">
                <img src="images/eric/ej (17).png" alt="Eric James">
                <button class="arrow left" onclick="prevSlide()">&#10094;</button>
                <button class="arrow right" onclick="nextSlide()">&#10095;</button>
            </div>
        </div>

        <!-- Right Section with Login Form -->
        <div class="right-section">
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
    </div>
</body>
</html>
