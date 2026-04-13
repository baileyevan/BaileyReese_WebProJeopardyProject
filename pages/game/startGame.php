<?php
session_start();

// Make sure both users are logged in
if (!isset($_SESSION["player1Name"]) || !isset($_SESSION["player2Name"])) {
    header("Location: ./playerSelect.php");
    exit;
};

// Check if the 2 users have an active game together
$hasGame = false;

$file = "../../databases/games.json";
$games = json_decode(file_get_contents($file), true);
$gameArr = null;
foreach ($games as $game) {
    if ($game["player1"] === $_SESSION["player1Name"] && $game["player2"] === $_SESSION["player2Name"] || $game["player1"] === $_SESSION["player2Name"] && $game["player2"] === $_SESSION["player1Name"] && !$game["isComplete"]) {
        $hasGame = true;
        $gameArr = $game;
        break;
    }
}

// Debug: Print all game stats
//echo print_r($gameArr);

// If starting new game take the user to the category selection
if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST["newGame"]) && $hasGame) {
    header("Location: ./categorySelection.php");
    exit;
}

// If resuming the game then store the game data in the session
if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST["resume"]) && $hasGame) {
    $_SESSION['game'] = $gameArr;
    header("Location: ./jeopardy.php");
    exit;
}



// If starting a new game then go to the
// category selection step
if (isset($_SERVER['REQUEST_METHOD']) === "POST" && $_POST["newGame"]) {
    header("Location: ./categorySelection.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Computer Science Jeopardy - Start Game</title>
    <link rel="stylesheet" href="../../css/common.css">
    <link rel="stylesheet" href="../../css/startGame.css">
</head>
<body>
    <div id="game-start-container" class="bs">
        <form method="post">

            <div id="game-start-header">
                <h1>Computer Science Jeopardy</h1>
            </div>
            <div id="game-start-options">
                <?php if ($hasGame): ?>
                    <input class="btn bs" type="submit" value="RESUME GAME" name="resume" id="resume-btn" >
                    <input class="btn bs" type="submit" value="NEW GAME" name="newGame" id="new-game-btn" >

                <?php else: ?>
                    <input class="btn bs" type="submit" value="NEW GAME" name="newGame" id="new-game-btn" >

                <?php endif; ?>
            </div>
        </form>
    </div>
    
</body>
</html>