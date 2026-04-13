<?php
session_start();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Computer Science Jeopardy - Home</title>
    <link rel="stylesheet" href="./css/common.css">
    <link rel="stylesheet" href="./css/index.css">
</head>
<body>

    <div id="index-content"> 
        <div id="index-header">
            <h1>Welcome to Computer Science Jeopardy!</h1>
            <p>Test your knowledge of computer science with our fun and interactive Jeopardy game. Sign in to play!</p>
        </div>
        <div id="index-options">
            <a class="" id="player-select-link" href="./pages/game/playerSelect.php">
                <input type="button" class="btn" value="PLAY" name="play">    
            </a>
        </div>
    </div>

    
</body>
</html>