<?php
session_start();


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
    
    // Redirect to remove the newGame parameter from URL
    header("Location: ./categorySelection.php");
    exit;
}

/*
=========================
PLAYER CHECK
=========================
*/
if (!isset($_SESSION["player1Name"]) || !isset($_SESSION["player2Name"])) {
    header("Location: ./playerSelect.php");
    exit;
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
REROLLS INIT
=========================
*/
if (!isset($_SESSION["rerollsRemaining"])) {
    
    $_SESSION["rerollsRemaining"] = 3;
    $rerollsRemaining = $_SESSION["rerollsRemaining"];
}

/*
=========================
GENERATE NEW CATEGORY SET
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

        // =========================
        // LOAD GAMES
        // =========================
        $file = "../../databases/games.json";
        $games = json_decode(file_get_contents($file), true) ?? [];

        $lastGameId = 0;

        foreach ($games as $g) {
            if (isset($g["id"]) && $g["id"] > $lastGameId) {
                $lastGameId = $g["id"];
            }
        }

        // =========================
        // HARD SESSION CLEAN
        // =========================
        unset($_SESSION["gameStats"]);
        unset($_SESSION["currentQuestion"]);
        unset($_SESSION["currentCategory"]);

        // =========================
        // CREATE NEW GAME
        // =========================
        $newGame = [
            "id" => $lastGameId + 1,

            "player1" => $_SESSION["player1Name"],
            "player2" => $_SESSION["player2Name"],

            "player1Score" => 0,
            "player1Difficulty" => "EASY",
            "player1AnswerHistory" => [0,0,0],

            "player2Score" => 0,
            "player2Difficulty" => "EASY",
            "player2AnswerHistory" => [0,0,0],

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

        // =========================
        // SESSION SYNC
        // =========================
        $_SESSION["gameStats"] = $newGame;
        $_SESSION["selectedCategories"] = $_SESSION["currentCategories"];

        header("Location: ./jeopardy.php");
        exit;
    }

    /*
    =========================
    REROLL LOGIC
    =========================
    */
    if (isset($_POST["reroll"]) && $_SESSION["rerollsRemaining"] > 0) {

        $_SESSION["rerollsRemaining"] = $_SESSION["rerollsRemaining"] - 1;
        $rerollsRemaining = $_SESSION["rerollsRemaining"];
        unset($_POST["reroll"]);

        $rerollCategories = $_POST["rerollCategories"] ?? [];

        $current = $_SESSION["currentCategories"];

        // keep non-selected
        $keptCategories = array_diff($current, $rerollCategories);

        $remainingPool = array_diff($categoryNames, $keptCategories);
        shuffle($remainingPool);

        $newCategories = array_merge(
            $keptCategories,
            array_slice($remainingPool, 0, 5 - count($keptCategories))
        );

        $_SESSION["currentCategories"] = $newCategories;
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

    </form>
</div>

</body>
</html>