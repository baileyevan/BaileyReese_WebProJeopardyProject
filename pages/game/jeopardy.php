<?php 
session_start();

// Redirect if no categories selected
if (empty($_SESSION["selectedCategories"]) && isset($_SESSION["gameStats"])) {
    $_SESSION["selectedCategories"] = $_SESSION["gameStats"]["selectedCategories"];
}

// Load games
$file = "../../databases/games.json";
$games = json_decode(file_get_contents($file), true);

// Prevent null crash
if (!$games) {
    $games = [];
}

// =========================
// CREATE NEW GAME IF NONE EXISTS
// =========================
if (!isset($_SESSION["gameStats"])) {

    $newId = !empty($games) ? end($games)["id"] + 1 : 1;

    $newGame = [
        "id" => $newId,
        "player1" => $_SESSION["player1Name"],
        "player2" => $_SESSION["player2Name"],
        "player1Score" => 0,
        "player1Difficulty" => "EASY",
        "player1AnswerHistory" => [0,0,0],
        "player2Score" => 0,
        "player2Difficulty" => "EASY",
        "player2AnswerHistory" => [0,0,0],
        "selectedCategories" => $_SESSION["selectedCategories"],
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

    $_SESSION["gameStats"] = $newGame;

    $games[] = $newGame;
    file_put_contents($file, json_encode($games, JSON_PRETTY_PRINT), LOCK_EX);
}

// =========================
// LOAD GAME
// =========================
if (!isset($_SESSION["gameStats"])) {
    echo "No active game.";
    exit;
}

$game = $_SESSION["gameStats"];
$selectedCategories = $game["selectedCategories"];

// =========================
// GAME OVER CHECK
// =========================
$isGameOver = true;

for ($i = 1; $i <= 5; $i++) {
    if ($game["category{$i}Remaining"] > 0) {
        $isGameOver = false;
        break;
    }
}

// =========================
// FINALIZE GAME
// =========================
if ($isGameOver && !$game["isComplete"]) {

    if ($game["player1Score"] > $game["player2Score"]) {
        $winner = $game["player1"];
    } elseif ($game["player2Score"] > $game["player1Score"]) {
        $winner = $game["player2"];
    } else {
        $winner = "Tie";
    }

    $game["isComplete"] = true;
    $game["winner"] = $winner;

    $_SESSION["gameStats"] = $game;

    // update JSON
    $fileGames = "../../databases/games.json";
    $games = json_decode(file_get_contents($fileGames), true);

    foreach ($games as &$g) {
        if ($g["id"] === $game["id"]) {
            $g = $game;
            break;
        }
    }
    unset($g);

    file_put_contents($fileGames, json_encode($games, JSON_PRETTY_PRINT), LOCK_EX);
}

// =========================
// HANDLE CATEGORY CLICK
// =========================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["category"])) {

    $selectedCategory = $_POST["category"];

    // store category for question.php
    $_SESSION["currentCategory"] = $selectedCategory;

    unset($_SESSION["currentQuestion"]);

    // redirect to question page
    header("Location: ./question.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["exitGame"])) {

    // =========================
    // SAVE LATEST SESSION STATE TO JSON
    // =========================
    $game = $_SESSION["gameStats"];

    $file = "../../databases/games.json";
    $games = json_decode(file_get_contents($file), true) ?? [];

    foreach ($games as &$g) {
        if ($g["id"] === $game["id"]) {
            $g = $game;
            break;
        }
    }
    unset($g);

    file_put_contents($file, json_encode($games, JSON_PRETTY_PRINT), LOCK_EX);

    // =========================
    // CLEAN SESSION
    // =========================
    unset($_SESSION["gameStats"]);
    unset($_SESSION["currentQuestion"]);
    unset($_SESSION["currentCategory"]);
    unset($_SESSION["answeredQuestions"]);
    unset($_SESSION["currentCategories"]);
    unset($_SESSION["selectedCategories"]);

    header("Location: ./playerSelect.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Computer Science Jeopard - Game</title>
    <link rel="stylesheet" href="../../css/common.css">
    <link rel="stylesheet" href="../../css/jeopardy.css">
</head>
<body>

<?php if ($game["isComplete"]): ?>

    <div class="bs" id="main-winner-container" style="text-align:center; padding:40px;">
        <div id="winner-header">
            <h1>Game Over!</h1>
        </div>

        <div id="winner-display-container">
            <h2>Winner: <?php echo $game["winner"]; ?></h2>
        </div>

        <div id="score-display-container">
            <p><?php echo $game["player1"]; ?>: <?php echo $game["player1Score"]; ?></p>
            <p><?php echo $game["player2"]; ?>: <?php echo $game["player2Score"]; ?></p>
        </div>
 
        <div id="exit-game-btn-container-winner" class="bs">
            <form method="post">
                <input type="submit" name="exitGame" value="EXIT GAME" class="btn bs">
            </form>
        </div>
    </div>

<?php else: ?>

    <div id="jeopardy-main-container" class="bs">
        <form method="post" style="width: 100%; height: 100%;">

            <div id="jeopardy-game-header">
                <h1>Computer Science Jeopardy</h1>
            </div>

            <!-- BOARD -->
            <div id="jeopardy-board-container">

                <?php foreach ($selectedCategories as $index => $category): ?>
                    <?php
                    $remaining = $game["category" . ($index + 1) . "Remaining"];
                    ?>

                    <div class="category-card bs">

                        <input 
                            type="submit" 
                            name="category" 
                            value="<?php echo $category; ?>" 
                            class="btn"
                            <?php echo ($remaining <= 0) ? "disabled style='opacity:0.4; cursor:not-allowed;'" : ""; ?>
                        >

                        <p>REMAINING: <?php echo $remaining; ?> / 5</p>

                    </div>

                <?php endforeach; ?>

            </div>

            <!-- PLAYERS -->
            <div id="players-container">

                <div class="players-stats-container bs">
                    <h2><?php echo $game["player1"] ?></h2>
                    <h3>SCORE: <?php echo $game["player1Score"] ?></h3>
                    <p>DIFFICULTY: <strong><?php echo $game["player1Difficulty"]; ?></strong></p>
                </div>

                <div class="players-stats-container bs">
                    <h2><?php echo $game["player2"] ?></h2>
                    <h3>SCORE: <?php echo $game["player2Score"] ?></h3>
                    <p>DIFFICULTY: <strong><?php echo $game["player2Difficulty"]; ?></strong></p>
                </div>

            </div>

            <!-- EXIT -->
            <div id="exit-game-btn-container" class="bs">
                <input id="exit-game-btn" type="submit" name="exitGame" value="EXIT GAME" class="btn">
            </div>

        </form>
    </div>

<?php endif; ?>

</body>
</html>