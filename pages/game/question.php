<?php
session_start();

/*
=========================
DIFFICULTY FUNCTION
=========================
*/
function calculateDifficulty($currentDifficulty, $history) {

    if (empty($history)) return $currentDifficulty;

    $lastThree = array_slice($history, -3);
    $lastTwo = array_slice($history, -2);

    if (count($lastTwo) === 2 && $lastTwo[0] == 0 && $lastTwo[1] == 0) {
        if ($currentDifficulty === "HARD") return "MED";
        if ($currentDifficulty === "MED") return "EASY";
        return "EASY";
    }

    if (count($lastThree) === 3 && array_sum($lastThree) === 3) {
        if ($currentDifficulty === "EASY") return "MED";
        if ($currentDifficulty === "MED") return "HARD";
        return "HARD";
    }

    return $currentDifficulty;
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

        if (!isset($_SESSION["answeredQuestions"][$questionId])) {

            $_SESSION["answeredQuestions"][$questionId] = true;

            $currentPlayer = $game["currentPlayersTurn"];
            $wasCorrect = isset($_POST["correct"]) ? 1 : 0;

            if ($currentPlayer === 0) {

                $game["player1AnswerHistory"][] = $wasCorrect;
                $game["player1AnswerHistory"] = array_slice($game["player1AnswerHistory"], -3);

                if ($wasCorrect) {
                    $game["player1Score"] += $question["value"];
                }

                $game["player1Difficulty"] =
                    calculateDifficulty($game["player1Difficulty"], $game["player1AnswerHistory"]);
            } else {

                $game["player2AnswerHistory"][] = $wasCorrect;
                $game["player2AnswerHistory"] = array_slice($game["player2AnswerHistory"], -3);

                if ($wasCorrect) {
                    $game["player2Score"] += $question["value"];
                }

                $game["player2Difficulty"] =
                    calculateDifficulty($game["player2Difficulty"], $game["player2AnswerHistory"]);
            }

            $game["currentPlayersTurn"] = ($currentPlayer === 0) ? 1 : 0;

            $_SESSION["gameStats"] = $game;

            // FIX: persist answer updates to JSON immediately
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
if (!isset($game["completedQuestionIds"])) {
    $game["completedQuestionIds"] = [];
}

$availableQuestions = [];

foreach ($questions as $q) {
    if (!in_array($q["id"], $game["completedQuestionIds"])) {
        $availableQuestions[] = $q;
    }
}

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

    $game["completedQuestionIds"][] = $question["id"];

    $categoryIndex = array_search($category, $game["selectedCategories"]);
    $key = "category" . ($categoryIndex + 1) . "Remaining";

    $game[$key]--;

    $_SESSION["gameStats"] = $game;

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

$currentPlayerName = ($game["currentPlayersTurn"] === 0)
    ? $game["player1"]
    : $game["player2"];
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

<div id="timer">10</div>

<div id="flip-card-container">

    <div class="flip-card" onclick="this.classList.toggle('flipped')">

        <div class="flip-card-front">
            <h2><?php echo htmlspecialchars($question["question"]); ?></h2>
            <p class="turn-label">
                Turn: <?php echo htmlspecialchars($currentPlayerName); ?>
            </p>
            <p class="flip-hint">Click to flip</p>
        </div>

        <div class="flip-card-back">
            <h2><?php echo htmlspecialchars($question["answer"]); ?></h2>

            <form method="post" class="answer-buttons">
                <button class="btn bs" name="correct" type="submit">Correct</button>
                <button class="btn bs" name="wrong" type="submit">Wrong</button>
            </form>
        </div>

    </div>

</div>

</body>
</html>