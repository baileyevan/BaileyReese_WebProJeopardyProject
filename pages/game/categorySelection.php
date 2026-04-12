<?php
session_start();

// Restore session from cookie 
if (!isset($_SESSION["username"]) && isset($_COOKIE["username"])) {
    $_SESSION["username"] = $_COOKIE["username"];
}

// Define login state
$loggedIn = isset($_SESSION["username"]);

if (!$loggedIn) {
    header("Location: ../login/login.php");
    exit;
}

// Load categories
$jsonString = file_get_contents('../../databases/questions.json');
$data = json_decode($jsonString, true);

$allCategories = [];
foreach ($data['categories'] as $category) {
    $allCategories[] = $category['name'];
}

// Shuffle pool
shuffle($allCategories);

// Default values
$currentCategories = [];
$rerollsRemaining = 3;

// HANDLE FORM SUBMISSION
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $currentCategories = $_POST["currentCategories"];
    $selected = $_POST["selectedCategories"] ?? [];
    $rerollsRemaining = (int)$_POST["rerollsRemaining"];

    // Only reroll if rerolls left
    if ($rerollsRemaining > 0) {

        $newCategories = [];

        foreach ($currentCategories as $cat) {
            if (in_array($cat, $selected)) {
                // Keep selected
                $newCategories[] = $cat;
            } else {
                // Reroll (duplicates allowed)
                $newCategories[] = $allCategories[array_rand($allCategories)];
            }
        }

        $currentCategories = $newCategories;
        $rerollsRemaining--;
    }

} else {
    // First load, pick 5 random categories
    $currentCategories = array_slice($allCategories, 0, 5);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Computer Science Jeopardy - Select Categories</title>
    <link rel="stylesheet" href="../../css/index.css">
</head>
<body>

<div>
    <div id="category-selection-header">
        <h1>Choose Your Categories</h1>
    </div>

    <form method="post">

        <!-- CATEGORY DISPLAY -->
        <div id="category-display-container">
            <?php for ($i = 0; $i < 5; $i++): ?>
                <div class="categoryCard">
                    <input type="checkbox"
                        name="selectedCategories[]"
                        value="<?php echo htmlspecialchars($currentCategories[$i]); ?>"
                        id="category<?php echo $i; ?>">

                    <label for="category<?php echo $i; ?>">
                        <h2><?php echo htmlspecialchars($currentCategories[$i]); ?></h2>
                    </label>
                </div>
            <?php endfor; ?>
        </div>

        <!-- INFO -->
        <div id="reroll-information-container">
            <h3>Select the categories you want to keep!</h3>
            <h4>Rerolls Remaining: <?php echo $rerollsRemaining; ?></h4>
        </div>

        
        <?php foreach ($currentCategories as $cat): ?>
            <input type="hidden" name="currentCategories[]" value="<?php echo htmlspecialchars($cat); ?>">
        <?php endforeach; ?>

        <input type="hidden" name="rerollsRemaining" value="<?php echo $rerollsRemaining; ?>">

        <!-- BUTTON -->
        <div id="reroll-button-container">
            <input type="submit" value="Reroll"
                <?php if ($rerollsRemaining <= 0) echo "disabled"; ?>>
        </div>

    </form>
</div>

</body>
</html>