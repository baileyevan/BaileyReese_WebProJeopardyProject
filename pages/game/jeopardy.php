<?php 
session_start();

if (empty($_SESSION["selectedCategories"])) {
    header("Location: ./categorySelection.php");
    exit;
}

$selectedCategories = $_SESSION["selectedCategories"] ?? [];

print_r($selectedCategories);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Computer Science Jeopard - Game</title>
</head>
<body>
    <div>
        <form method="post">
            <div>
                <h1>Jeopardy In Game Page </h1>
            </div>
        </form>
    </div>
</body>
</html>