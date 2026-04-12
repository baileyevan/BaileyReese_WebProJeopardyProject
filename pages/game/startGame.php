<?php
session_start();

// Restore session from cookie 
if (!isset($_SESSION["username"]) && isset($_COOKIE["username"])) {
    $_SESSION["username"] = $_COOKIE["username"];
}

// Define login state
$loggedIn = isset($_SESSION["username"]);

if (!$loggedIn) {
    header("Location: ../login/login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Computer Science Jeopardy - Start Game</title>
</head>
<body>
    
</body>
</html>