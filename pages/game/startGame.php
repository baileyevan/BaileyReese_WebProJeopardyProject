<?php
session_start();

// Make sure both users are logged in
$numPlayers = isset($_SESSION["numPlayers"]) ? $_SESSION["numPlayers"] : 2;

for ($i = 1; $i <= $numPlayers; $i++) {
    if (!isset($_SESSION["player{$i}Name"])) {
        header("Location: ./playerSelect.php");
        exit;
    }
}

// Load games
$file = "../../databases/games.json";
$games = json_decode(file_get_contents($file), true);

if (!$games) {
    $games = [];
}

// Get last game ID
$lastGame = end($games);
$lastGameId = $lastGame ? $lastGame["id"] : 0;

$_SESSION["lastGameId"] = $lastGameId;

// Check for active game
$hasGame = false;
$gameArr = null;

foreach (array_reverse($games) as $g) { 

    $players = $g["players"] ?? [];

    $samePlayers = true;
    for ($i = 1; $i <= $numPlayers; $i++) {
        if (!in_array($_SESSION["player{$i}Name"], $players)) {
            $samePlayers = false;
            break;
        }
    }

    $isValidGame =
        !$g["isComplete"] &&
        !empty($g["selectedCategories"]);

    if ($samePlayers && $isValidGame) {
        $hasGame = true;
        $gameArr = $g;
        break;
    }
}

// =========================
// NEW GAME
// =========================
if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST["newGame"])) {

    $_SESSION["numPlayers"] = isset($_POST["numPlayers"]) ? (int)$_POST["numPlayers"] : $numPlayers;

    $games = array_filter($games, function($g) use ($numPlayers) {

        $players = $g["players"] ?? [];

        foreach ($players as $p) {
            if (!in_array($p, $players)) {
                return true;
            }
        }

        return $g["isComplete"];
    });

    $games = array_values($games);

    file_put_contents($file, json_encode($games, JSON_PRETTY_PRINT), LOCK_EX);

    unset($_SESSION["gameStats"]);
    unset($_SESSION["currentQuestion"]);
    unset($_SESSION["currentCategory"]);
    unset($_SESSION["answeredQuestions"]);
    unset($_SESSION["currentCategories"]);
    unset($_SESSION["selectedCategories"]);

    header("Location: ./categorySelection.php?newGame=1");
    exit;
}

// =========================
// RESUME GAME
// =========================
if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST["resume"]) && $hasGame) {

    $_SESSION["gameStats"] = $gameArr;

    $_SESSION["selectedCategories"] = $gameArr["selectedCategories"];

    header("Location: ./jeopardy.php");
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
                    <input class="btn bs" type="submit" value="RESUME GAME" name="resume">
                    <input class="btn bs" type="submit" value="NEW GAME" name="newGame">
                <?php else: ?>
                    <input class="btn bs" type="submit" value="NEW GAME" name="newGame">
                <?php endif; ?>
            </div>

        </form>
    </div>
</body>
</html>