<?php
session_start();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Computer Science Jeopardy - Start Game</title>
</head>
<body>
    <div id="game-start-container">
        <div id="game-start-header">
            <h1>Computer Science Jeopardy</h1>
        </div>
        <div id="game-start-options">
            <?php if ($hasGame): ?>
                <a href="./jeopardy.php" class="btn btn-primary">Resume</a>
                <a href="./categorySelection.php" class="btn btn-primary">New Game</a>
            <?php else: ?>
                <a href="./categorySelection.php" class="btn btn-primary">New Game</a>
            <?php endif; ?>
        </div>
    </div>
    
</body>
</html>