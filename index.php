<?php
session_start();

// Restore session from cookie 
if (!isset($_SESSION["username"]) && isset($_COOKIE["username"])) {
    $_SESSION["username"] = $_COOKIE["username"];
}

// Define login state
$loggedIn = isset($_SESSION["username"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Computer Science Jeopardy - Home</title>
    <link rel="stylesheet" href="./css/index.css">
</head>
<body>

    <div id="index-content"> 
        <div id="index-header">
            <h1>Welcome to Computer Science Jeopardy!</h1>
            <p>Test your knowledge of computer science with our fun and interactive Jeopardy game. Sign in to play!</p>
        </div>
        <div id="index-options">
            <a href="./pages/game/playerSelect.php">Play</a>
        </div>
    </div>

    
</body>
</html>