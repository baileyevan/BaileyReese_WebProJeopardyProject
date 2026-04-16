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

    $game = $_SESSION["gameStats"];
    $question = $_SESSION["currentQuestion"];
    $questionId = $question["id"];

    if (!isset($_SESSION["answeredQuestions"])) {
        $_SESSION["answeredQuestions"] = [];
    }

    // prevent double scoring
    if (isset($_SESSION["answeredQuestions"][$questionId])) {
        header("Location: ./jeopardy.php");
        exit;
    }

    $_SESSION["answeredQuestions"][$questionId] = true;

    $currentPlayer = $game["currentPlayersTurn"];

    if (isset($_POST["correct"])) {
        $wasCorrect = true;
    } elseif (isset($_POST["wrong"])) {
        $wasCorrect = false;
    } else {
        header("Location: ./jeopardy.php");
        exit;
    }

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

    header("Location: ./jeopardy.php");
    exit;
}

/*
=========================
SAFETY CHECKS
=========================
*/
if (!isset($_SESSION["gameStats"]) || !isset($_SESSION["currentCategory"])) {
    header("Location: ./jeopardy.php");
    exit;
}

$game = $_SESSION["gameStats"];
$category = $_SESSION["currentCategory"];

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
PICK QUESTION 
=========================
*/
if (!isset($_SESSION["currentQuestion"])) {

    $currentPlayer = $game["currentPlayersTurn"];

    // Get player difficulty
    $difficulty = $currentPlayer === 0
        ? $game["player1Difficulty"]
        : $game["player2Difficulty"];

    // Filter questions based on difficulty
    $filtered = [];

    foreach ($availableQuestions as $q) {

        if ($difficulty === "EASY" && $q["value"] <= 200) {
            $filtered[] = $q;
        }

        if ($difficulty === "MED" && $q["value"] == 200) {
            $filtered[] = $q;
        }

        if ($difficulty === "HARD" && $q["value"] >= 300) {
            $filtered[] = $q;
        }
    }

    // fallback if empty
    if (empty($filtered)) {
        $filtered = $availableQuestions;
    }

    // pick question
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
    <title>Computer Science Jeopardy - Question</title>
    <link rel="stylesheet" href="../../css/common.css">
    <link rel="stylesheet" href="../../css/question.css">
</head>
<body>

    <div id="question-category-name-container">
        <h1>Category: <?php echo $category; ?></h1>
    </div>
    <div id="main-question-container" class="bs">
        <div id="question-turn-contianer">
            <h3>
                Turn:
                <?php echo $game["currentPlayersTurn"] === 0
                    ? $game["player1"]
                    : $game["player2"]; ?>
            </h3>
        </div>

        <div id="question-container">
            <h2><?php echo $question["question"]; ?></h2>
        </div>
        <div id="form-container">
            <form method="post">

                <div id="reveal-button-container">
                    <button class="bs" id="reveal-button" type="button" onclick="document.getElementById('answer').style.display='block'">
                        Reveal Answer
                    </button>
                    <p id="answer" style="display:none;">
                        <strong><?php echo $question["answer"]; ?></strong>
                    </p>
                </div>

                <div id="wrong-right-container">
                    <input class="btn bs" type="submit" name="correct" value="Correct">
                    <input class="btn bs" type="submit" name="wrong" value="Wrong">
                </div>


                


            </form>
        </div>


    </div>

    


    

</body>
</html>