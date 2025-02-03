<?php
include 'header.php';
////require_once 'permissions.php';

// This is a simple README page for your PHP application.
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>README</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #007BFF;
        }
        ul {
            margin: 20px 0;
            padding-left: 20px;
        }
        li {
            margin-bottom: 10px;
        }
        .footer {
            margin-top: 30px;
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome To Badminton Tournament Software.</h1> 
        <h2>Software Overview</h2>
        <p>Badminton Tournament Software is designed to help create and manage  Badminton Tournaments efficiently. </p>
        <p>It includes the following features:</p>
        <ul>
            <li><a href="register.php">Register Free</a> as user to Start your own <a href="insert_tournament.php">Championship</a>.</li>
            <li>Comprehensive <a href="register.php">user management</a></li>
            <li>Edit / delete manage your own <a href="insert_tournament.php">tournaments</a> / <a href="insert_category.php">categories</a> for tournament/players/matches</li>
            <li>Dynamic data handling, Real-time updates of results.</li>
        </ul>

         <h2>Usage</h2>
         <ul>
            <li><a href="register.php">Register Free</a>  or <a href="login.php">Login</a> in with your credentials.</li>
            <li>Navigate through the <a href="dashboard.php">Dashboard</a> to access various features.</li>
         </ul>

        <h2>Support</h2>
        <p>If you encounter any issues, please contact the support team at <a href="mailto:xpindia@gmail.com">xpindia@gmail.com</a>. <br>Whatsapp (text only +91-7432001215)</p>

        <div class="footer">
            <p>&copy; 2025 Dr Robert James JHCPL.IN. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
