<?php
session_start();

// go back
if (isset($_GET["goBack"]) && $_GET["goBack"] == 1) {

    unset($_SESSION["currentCategories"]);
    unset($_SESSION["selectedCategories"]);

    $_SESSION["rerollsRemaining"] = 3;

    header("Location: ./playerSelect.php");
    exit;
}

// reset
if (isset($_GET["resetCategories"]) && $_GET["resetCategories"] == 1) {

    unset($_SESSION["currentCategories"]);
    unset($_SESSION["selectedCategories"]);

    $_SESSION["rerollsRemaining"] = 3;

    header("Location: ./categorySelection.php");
    exit;
}

// new game
if (isset($_GET["newGame"]) && $_GET["newGame"] == 1) {

    unset($_SESSION["gameStats"]);
    unset($_SESSION["currentCategories"]);
    unset($_SESSION["selectedCategories"]);
    unset($_SESSION["rerollsRemaining"]);
    unset($_SESSION["currentQuestion"]);
    unset($_SESSION["currentCategory"]);
    unset($_SESSION["answeredQuestions"]);
    unset($_SESSION["gameId"]);

    header("Location: ./categorySelection.php");
    exit;
}

/*
=========================
PLAYER CHECK
=========================
*/
$numPlayers = isset($_SESSION["numPlayers"]) ? $_SESSION["numPlayers"] : 2;

for ($i = 1; $i <= $numPlayers; $i++) {
    if (!isset($_SESSION["player{$i}Name"])) {
        header("Location: ./playerSelect.php");
        exit;
    }
}

// load categories
$file = "../../databases/questions.json";
$categories = json_decode(file_get_contents($file), true);

$categoryNames = array_column($categories["categories"], "name");

// rerolls
if (!isset($_SESSION["rerollsRemaining"])) {
    $_SESSION["rerollsRemaining"] = 3;
}

$rerollsRemaining = $_SESSION["rerollsRemaining"];

// generate categories
if (!isset($_SESSION["currentCategories"]) || empty($_SESSION["currentCategories"])) {
    shuffle($categoryNames);
    $_SESSION["currentCategories"] = array_slice($categoryNames, 0, 5);
}

// =========================
// HANDLE POST
// =========================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST["confirmCategories"])) {

        $file = "../../databases/games.json";
        $games = json_decode(file_get_contents($file), true) ?? [];

        $lastGameId = 0;
        foreach ($games as $g) {
            if ($g["id"] > $lastGameId) {
                $lastGameId = $g["id"];
            }
        }

        unset($_SESSION["gameStats"]);
        unset($_SESSION["currentQuestion"]);
        unset($_SESSION["currentCategory"]);

        $players = [];
        foreach ($players as $i => $p) {} // keep structure safe

        $players = [];
        for ($i = 1; $i <= $numPlayers; $i++) {
            $players[] = $_SESSION["player{$i}Name"];
        }

        $scores = [];
        $difficulties = [];
        $histories = [];

        foreach ($players as $p) {
            $scores[$p] = 0;
            $difficulties[$p] = "EASY";
            $histories[$p] = [];
        }

        $newGame = [
            "id" => $lastGameId + 1,
            "players" => $players,
            "scores" => $scores,
            "difficulties" => $difficulties,
            "histories" => $histories,

            "selectedCategories" => $_SESSION["currentCategories"],

            "category1Remaining" => 5,
            "category2Remaining" => 5,
            "category3Remaining" => 5,
            "category4Remaining" => 5,
            "category5Remaining" => 5,

            "currentPlayersTurn" => 0,
            "completedQuestionIds" => [],

            "isComplete" => false,
            "winner" => null
        ];

        $games[] = $newGame;
        file_put_contents($file, json_encode($games, JSON_PRETTY_PRINT), LOCK_EX);

        $_SESSION["gameStats"] = $newGame;
        $_SESSION["selectedCategories"] = $_SESSION["currentCategories"];

        header("Location: ./jeopardy.php");
        exit;
    }

    if (isset($_POST["reroll"]) && $rerollsRemaining > 0) {

        $_SESSION["rerollsRemaining"]--;
        $rerollsRemaining = $_SESSION["rerollsRemaining"];

        $rerollCategories = $_POST["rerollCategories"] ?? [];
        $current = $_SESSION["currentCategories"];

        $keptCategories = array_diff($current, $rerollCategories);

        $remainingPool = array_diff($categoryNames, $current);
        shuffle($remainingPool);

        $newCategories = array_merge(
            $keptCategories,
            array_slice($remainingPool, 0, 5 - count($keptCategories))
        );

        $_SESSION["currentCategories"] = array_values($newCategories);
    }
}

$currentCategories = $_SESSION["currentCategories"];
$rerollsRemaining = $_SESSION["rerollsRemaining"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Computer Science Jeopardy - Select Categories</title>
    <link rel="stylesheet" href="../../css/common.css">
    <link rel="stylesheet" href="../../css/categorySelection.css">
</head>
<body>

<div id="main-category-selection-container" class="bs">

    <div id="category-selection-header">
        <h1>Choose Your Categories</h1>
    </div>

    <form method="post">

        <div id="category-display-container">

            <?php for ($i = 0; $i < 5; $i++): ?>
                <div class="categoryCard bs">

                    <input type="checkbox"
                        class="hidden"
                        name="rerollCategories[]"
                        value="<?php echo htmlspecialchars($currentCategories[$i]); ?>"
                        id="category<?php echo $i; ?>">

                    <label class="<?php echo ($rerollsRemaining === 0) ? 'muted' : ''; ?>"
                        for="category<?php echo $i; ?>">

                        <h2><?php echo htmlspecialchars($currentCategories[$i]); ?></h2>

                    </label>

                </div>
            <?php endfor; ?>

        </div>

        <div id="reroll-information-container">
            <h3>Select categories to reroll</h3>
        </div>

        <div id="reroll-button-container">
            <h4>Rerolls Remaining: <?php echo $rerollsRemaining; ?></h4>

            <input type="submit" name="reroll" value="Reroll"
                <?php if ($rerollsRemaining <= 0) echo "disabled"; ?>>
        </div>

        <div id="reroll-confirm-container">
            <input class="cb btn" type="submit" name="confirmCategories" value="CONFIRM">
        </div>

        <div id="go-back-button-container">
            <a href="categorySelection.php?goBack=1" class="cb btn">
                GO BACK
            </a>
        </div>

    </form>

</div>

</body>
</html>