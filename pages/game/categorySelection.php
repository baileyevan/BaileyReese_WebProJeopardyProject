<?php
session_start();

/*
=========================
GO BACK TO PLAYER SELECT
=========================
*/
if (isset($_GET["goBack"]) && $_GET["goBack"] == 1) {

    unset($_SESSION["currentCategories"]);
    unset($_SESSION["selectedCategories"]);

    // Reset rerolls
    $_SESSION["rerollsRemaining"] = 3;

    header("Location: ./playerSelect.php");
    exit;
}

/*
=========================
RESET CATEGORIES ONLY
=========================
*/
if (isset($_GET["resetCategories"]) && $_GET["resetCategories"] == 1) {

    unset($_SESSION["currentCategories"]);
    unset($_SESSION["selectedCategories"]);

    $_SESSION["rerollsRemaining"] = 3;

    header("Location: ./categorySelection.php");
    exit;
}

/*
=========================
HARD RESET FOR NEW GAME
=========================
*/
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
PLAYER CHECK (DYNAMIC)
=========================
Ensure ALL players are logged in
*/
$numPlayers = isset($_SESSION["numPlayers"]) ? $_SESSION["numPlayers"] : 2;

for ($i = 1; $i <= $numPlayers; $i++) {
    if (!isset($_SESSION["player{$i}Name"])) {
        header("Location: ./playerSelect.php");
        exit;
    }
}

/*
=========================
LOAD CATEGORY DATA
=========================
*/
$file = "../../databases/questions.json";
$categories = json_decode(file_get_contents($file), true);

$categoryNames = array_column($categories["categories"], "name");

/*
=========================
INIT REROLLS
=========================
*/
if (!isset($_SESSION["rerollsRemaining"])) {
    $_SESSION["rerollsRemaining"] = 3;
}

$rerollsRemaining = $_SESSION["rerollsRemaining"];

/*
=========================
GENERATE INITIAL CATEGORY SET
=========================
*/
if (!isset($_SESSION["currentCategories"]) || empty($_SESSION["currentCategories"])) {
    shuffle($categoryNames);
    $_SESSION["currentCategories"] = array_slice($categoryNames, 0, 5);
}

/*
=========================
HANDLE POST ACTIONS
=========================
*/
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /*
    =========================
    CONFIRM CATEGORIES
    =========================
    */
    if (isset($_POST["confirmCategories"])) {

        $file = "../../databases/games.json";
        $games = json_decode(file_get_contents($file), true) ?? [];

        // Find last game ID
        $lastGameId = 0;
        foreach ($games as $g) {
            if (isset($g["id"]) && $g["id"] > $lastGameId) {
                $lastGameId = $g["id"];
            }
        }

        unset($_SESSION["gameStats"]);
        unset($_SESSION["currentQuestion"]);
        unset($_SESSION["currentCategory"]);

        /*
        =========================
        BUILD DYNAMIC PLAYER DATA
        =========================
        */
        $players = [];
        $playerScores = [];
        $playerDifficulties = [];
        $playerHistories = [];

        for ($i = 1; $i <= $numPlayers; $i++) {

            $playerName = $_SESSION["player{$i}Name"];

            $players[] = $playerName;

            // Initialize stats for each player
            $playerScores[$playerName] = 0;
            $playerDifficulties[$playerName] = "EASY";
            $playerHistories[$playerName] = [0, 0, 0];
        }

        /*
        =========================
        CREATE NEW GAME OBJECT
        =========================
        */
        $newGame = [
            "id" => $lastGameId + 1,

            // Store players as array
            "players" => $players,

            // Store dynamic player stats (keyed by name)
            "scores" => $playerScores,
            "difficulties" => $playerDifficulties,
            "answerHistories" => $playerHistories,

            // Category data
            "selectedCategories" => $_SESSION["currentCategories"],

            "category1Remaining" => 5,
            "category2Remaining" => 5,
            "category3Remaining" => 5,
            "category4Remaining" => 5,
            "category5Remaining" => 5,

            // Track whose turn it is (index of players array)
            "currentPlayersTurn" => 0,

            "completedQuestionIds" => [],

            "isComplete" => false,
            "winner" => null
        ];

        // Save game
        $games[] = $newGame;
        file_put_contents($file, json_encode($games, JSON_PRETTY_PRINT), LOCK_EX);

        // Store in session
        $_SESSION["gameStats"] = $newGame;
        $_SESSION["selectedCategories"] = $_SESSION["currentCategories"];

        header("Location: ./jeopardy.php");
        exit;
    }

    /*
    =========================
    REROLL LOGIC (UNCHANGED)
    =========================
    */
    if (isset($_POST["reroll"]) && $rerollsRemaining > 0) {

        $_SESSION["rerollsRemaining"]--;
        $rerollsRemaining = $_SESSION["rerollsRemaining"];

        $rerollCategories = $_POST["rerollCategories"] ?? [];
        $current = $_SESSION["currentCategories"];

        // Categories to keep
        $keptCategories = array_diff($current, $rerollCategories);

        // Remove current categories from pool
        $remainingPool = array_diff($categoryNames, $current);
        shuffle($remainingPool);

        // Build new categories
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
                <input class="cb btn"
                    type="submit"
                    name="confirmCategories"
                    value="CONFIRM">
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