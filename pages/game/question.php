<?php
session_start();

/*
=========================
DIFFICULTY FUNCTION
=========================
*/
function calculateDifficulty($history) {

    if (count($history) < 3) {
        return "MED";
    }

    $correct = array_sum($history);
    $ratio = $correct / 3;

    if ($ratio >= 0.66) return "HARD";
    if ($ratio <= 0.33) return "EASY";

    return "MED";
}

/*
=========================
HANDLE ANSWER 
=========================
*/
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST["correct"]) || isset($_POST["wrong"])) {

        $game = $_SESSION["gameStats"];
        $question = $_SESSION["currentQuestion"];
        $questionId = $question["id"];

        if (!isset($_SESSION["answeredQuestions"])) {
            $_SESSION["answeredQuestions"] = [];
        }

        // prevent double scoring
        if (!isset($_SESSION["answeredQuestions"][$questionId])) {

            $_SESSION["answeredQuestions"][$questionId] = true;

            $currentPlayer = $game["currentPlayersTurn"];
            $wasCorrect = isset($_POST["correct"]);

            // -------------------------
            // PLAYER 1
            // -------------------------
            if ($currentPlayer === 0) {

                $game["player1AnswerHistory"][] = $wasCorrect;
                $game["player1AnswerHistory"] = array_slice($game["player1AnswerHistory"], -3);

                if ($wasCorrect) {
                    $game["player1Score"] += $question["value"];
                }

                $game["player1Difficulty"] =
                    calculateDifficulty($game["player1AnswerHistory"]);
            }

            // -------------------------
            // PLAYER 2
            // -------------------------
            else {

                $game["player2AnswerHistory"][] = $wasCorrect;
                $game["player2AnswerHistory"] = array_slice($game["player2AnswerHistory"], -3);

                if ($wasCorrect) {
                    $game["player2Score"] += $question["value"];
                }

                $game["player2Difficulty"] =
                    calculateDifficulty($game["player2AnswerHistory"]);
            }

            // switch turn
            $game["currentPlayersTurn"] = ($currentPlayer === 0) ? 1 : 0;

            $_SESSION["gameStats"] = $game;
        }

        header("Location: ./jeopardy.php");
        exit;
    }
}

/*
=========================
SAFETY CHECKS
=========================
*/
if (!isset($_SESSION["gameStats"]) || !isset($_SESSION["currentCategory"]) || !isset($_SESSION["currentValue"])) {
    header("Location: ./jeopardy.php");
    exit;
}

$game = $_SESSION["gameStats"];
$category = $_SESSION["currentCategory"];
$currentValue = (int)$_SESSION["currentValue"];

/*
=========================
LOAD QUESTIONS
=========================
*/
$file = "../../databases/questions.json";
$data = json_decode(file_get_contents($file), true);

$questions = [];

foreach ($data["categories"] as $cat) {
    if ($cat["name"] === $category) {
        $questions = $cat["questions"];
        break;
    }
}

/*
=========================
FILTER USED QUESTIONS
=========================
*/
$availableQuestions = [];

foreach ($questions as $q) {
    if (!in_array($q["id"], $game["completedQuestionIds"])) {
        $availableQuestions[] = $q;
    }
}

/*
=========================
NO QUESTIONS LEFT
=========================
*/
if (empty($availableQuestions)) {
    header("Location: ./jeopardy.php");
    exit;
}

/*
=========================
PICK QUESTION (BY VALUE)
=========================
*/
if (!isset($_SESSION["currentQuestion"])) {

    $filtered = [];

    foreach ($availableQuestions as $q) {
        if ((int)$q["value"] === $currentValue) {
            $filtered[] = $q;
        }
    }

    if (empty($filtered)) {
        $filtered = $availableQuestions;
    }

    $question = $filtered[array_rand($filtered)];
    $_SESSION["currentQuestion"] = $question;

    // update game state
    $game["completedQuestionIds"][] = $question["id"];

    $categoryIndex = array_search($category, $game["selectedCategories"]);
    $key = "category" . ($categoryIndex + 1) . "Remaining";

    $game[$key]--;

    $_SESSION["gameStats"] = $game;

    // save to JSON 
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

$question = $_SESSION["currentQuestion"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question - Flip Card</title>
    <link rel="stylesheet" href="../../css/common.css">
    <link rel="stylesheet" href="../../css/question.css">
</head>
<body>

<div id="question-category-name-container">
    <h1><?php echo htmlspecialchars($category); ?> — $<?php echo $question["value"]; ?></h1>
</div>

<div id="flip-card-container">

    <div class="flip-card" onclick="this.classList.toggle('flipped')">

        <!-- FRONT: QUESTION -->
        <div class="flip-card-front">
            <h2><?php echo htmlspecialchars($question["question"]); ?></h2>
            <p class="turn-label">
                Turn: <?php echo $game["currentPlayersTurn"] === 0
                    ? htmlspecialchars($game["player1"])
                    : htmlspecialchars($game["player2"]); ?>
            </p>
            <p class="flip-hint">Click to flip</p>
        </div>

        <!-- BACK: ANSWER + BUTTONS -->
        <div class="flip-card-back">
            <h2><?php echo htmlspecialchars($question["answer"]); ?></h2>

            <form method="post" class="answer-buttons">
                <button class="btn bs" name="correct">Correct</button>
                <button class="btn bs" name="wrong">Wrong</button>
            </form>
        </div>

    </div>

</div>

</body>
</html>
