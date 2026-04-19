<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["numPlayers"])) {
    $_SESSION["numPlayers"] = (int)$_POST["numPlayers"];
    header("Location: ./pages/game/playerSelect.php");
    exit;
}
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

        <form method="post">
            <div id="player-count-container">
                <label for="numPlayers">Number of Players:</label>
                <select name="numPlayers" id="numPlayers">
                    <option value="2">2 Players</option>
                    <option value="3">3 Players</option>
                    <option value="4">4 Players</option>
                    <option value="5">5 Players</option>
                </select>
            </div>

            <div id="index-options">
                <input type="submit" class="btn" value="PLAY" name="play">
            </div>
        </form>
    </div>

</body>
</html>