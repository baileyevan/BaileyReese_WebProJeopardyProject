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

            <?php if ($loggedIn): ?>
                <h1>
                    Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?> 
                    to Computer Science Jeopardy!
                </h1>
            <?php else: ?>
                <h1>Welcome to Computer Science Jeopardy!</h1>
            <?php endif; ?>
            <p>Test your knowledge of computer science with our fun and interactive Jeopardy game. Sign in to play!</p>

        </div>
        <div id="index-options">
            <?php if (!$loggedIn): ?>
                <a class="btn btn-primary" href="./pages/login/login.php">Sign In</a>
                <a class="btn btn-primary" href="./pages/register/register.php">Register</a>
            <?php else: ?>
                <a class="btn btn-success" href="./pages/game/play.php">Play</a>
            <?php endif; ?>
        </div>
    </div>

    
</body>
</html>