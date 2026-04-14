<?php
session_start();

if (!isset($_SESSION["player1Name"]) || !isset($_SESSION["player2Name"])) {
    header("Location: ./playerSelect.php");
    exit;
}

$file = "../../databases/questions.json";
$categories = json_decode(file_get_contents($file), true);

$categoryNames = array_column($categories["categories"], "name");


if (!isset($_SESSION["rerollsRemaining"])) {
    $_SESSION["rerollsRemaining"] = 3;
}

if (!isset($_SESSION["currentCategories"])) {
    shuffle($categoryNames);
    $_SESSION["currentCategories"] = array_slice($categoryNames, 0, 5);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    print_r($_POST);

    if (isset($_POST["confirmCategories"])) {

        $_SESSION["selectedCategories"] = $_SESSION["currentCategories"];

        header("Location: ./jeopardy.php");
        exit;
    }

    // REROLL LOGIC
    if (isset($_POST["reroll"]) && $_SESSION["rerollsRemaining"] > 0) {

        $_SESSION["rerollsRemaining"]--;

        $rerollCategories = $_POST["rerollCategories"] ?? [];

        // Get current categories
        $current = $_SESSION["currentCategories"];

        // Remove categories that should be rerolled
        $keptCategories = array_diff($current, $rerollCategories);

        // Get remaining pool
        $remainingPool = array_diff($categoryNames, $keptCategories);

        shuffle($remainingPool);

        // Fill back up to 5
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
                            id="category<?php echo $i; ?>"
                            <?php 
                                if (isset($_SESSION["selectedCategories"]) && 
                                    in_array($currentCategories[$i], $_SESSION["selectedCategories"])) {
                                    echo "checked";
                                }
                            ?>
                        >

                        <label class="<?php echo ($rerollsRemaining === 0) ? 'muted' : ''; ?>" for="category<?php echo $i; ?>">
                            <h2><?php echo htmlspecialchars($currentCategories[$i]); ?></h2>
                        </label>

                    </div>
                <?php endfor; ?>

            </div>

            <div id="reroll-information-container">
                <h3>Select the categories you want to keep!</h3>
            </div>

            <div id="reroll-button-container">
                <h4>Rerolls Remaining: <?php echo $rerollsRemaining; ?></h4>
                <input type="submit" name="reroll" value="Reroll"
                    <?php if ($rerollsRemaining <= 0) echo "disabled"; ?>>
            </div>
            <div id="reroll-confirm-container">
                <input class="cb btn" type="submit" name="confirmCategories" id="confirm-categories-btn" value="CONFIRM" >
            </div>

        </form>
    </div>

</body>
</html>