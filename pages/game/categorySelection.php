<?php
session_start();

// Restore session from cookie 
if (!isset($_SESSION["username"]) && isset($_COOKIE["username"])) {
    $_SESSION["username"] = $_COOKIE["username"];
}

// Define login state
$loggedIn = isset($_SESSION["username"]);
$hasGame = isset($_COOKIE["hasGame"]);


if (!$loggedIn) {
    header("Location: ../login/login.php");
    exit;
}

if (isset($_COOKIE['hasGame'])) {
    unset($_COOKIE['hasGame']); 
    setcookie('hasGame', '', time() - 3600, '/'); 
}


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Computer Science Jeopardy - Select Categories</title>
</head>
<body>
    <div>

    </div>  
    
</body>
</html>