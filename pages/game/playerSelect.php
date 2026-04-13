<?php

session_start();

if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["confirm"])) {
    $player1Name = $_POST["player1Name"];
    $player1Password = $_POST["player1Password"];
    $rememberPlayer1 = isset($_POST["rememberPlayer1"]);

    $player2Name = $_POST["player2Name"];
    $player2Password = $_POST["player2Password"];
    $rememberPlayer2 = isset($_POST["rememberPlayer2"]);

    $file = "../../databases/users.json";
    $users = json_decode(file_get_contents($file), true);

    if (!$users) $users = [];

    foreach ($users as $user) {
        // Check for player 1
        if ($user["username"] === $player1Name && password_verify($player1Password, $user["password"])) {
            $_SESSION["player1Name"] = $player1Name;

            if ($rememberPlayer1) {
                setcookie("player1Name", $player1Name, time() + (86400 *7), "/"); // 7days
            }
        } else {
            $errorPlayer1 = "Player 1 Invalid Login";
        }

        // Check for player 2
        if ($user["username"] === $player2Name && password_verify($player2Password, $user["password"])) {
            $_SESSION["player2Name"] = $player2Name;

            if ($rememberPlayer2) {
                setcookie("player2Name", $player2Name, time() + (86400 *7), "/"); // 7days
            }
        } else {
            $errorPlayer2 = "Player 2 Invalid Login";
        }
    }  
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["logout"])) {

    // Remove session data
    unset($_SESSION["player1Name"]);
    unset($_SESSION["player2Name"]);

    // Remove cookies 
    setcookie("player1Name", "", time() - 3600, "/");
    setcookie("player2Name", "", time() - 3600, "/");

    header("Location: ./playerSelect.php");
    exit;
}

// if both players exist in the session then they
// are logged in and ready to play
$readyToPlay = isset($_SESSION["player1Name"]) && isset($_SESSION["player2Name"]);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Computer Science Jeopardy - Player Select</title>
    <link rel="stylesheet" href="../../css/playerSelect.css">
    
</head>
<body>
    <div id="player-select-container">
        <form method="post">
            <div id="player-select-header">
                <h1>Player Selection</h1>
            </div>
            <div id="players-container">
                <div class="player-container">
                    <?php if(isset($_SESSION["player1Name"])): ?>
                        <h2>WELCOME: <?php echo "<strong>" . htmlspecialchars($_SESSION["player1Name"]) . "!</strong>"; ?>
                    <?php else: ?>
                        <h2>Player 1 Login...</h2>
                        <?php if (!empty($errorPlayer1)): ?>
                            <p style="color:red;"><?php echo $errorPlayer1; ?></p>
                        <?php endif; ?>
                        <input type="text" id="player1Name" name="player1Name" placeholder="Player 1 Name..." >
                        <input type="password" id="player1Password" name="player1Password" placeholder="Player 1 Password..." >
                        <div class="player-remember-container">
                            <label for="rememberPlayer1">Remember me</label>
                            <input type="checkbox" id="rememberPlayer1" name="rememberPlayer1">
                        </div>

                    <?php endif; ?>
                    
                </div>
                <div class="player-container">
                    <?php if(isset($_SESSION["player2Name"])): ?>
                        <h2>WELCOME: <?php echo "<strong>" . htmlspecialchars($_SESSION["player2Name"]) . "!</strong>"; ?>
                    <?php else: ?>
                        <h2>Player 2 Login...</h2>
                        <?php if (!empty($errorPlayer2)): ?>
                            <p style="color:red;"><?php echo $errorPlayer2; ?></p>
                        <?php endif; ?>
                        <input type="text" id="player2Name" name="player2Name" placeholder="Player 2 Name..." >
                        <input type="password" id="player2Password" name="player2Password" placeholder="Player 2 Password..." >
                        <div class="player-remember-container">
                            <label for="rememberPlayer2">Remember me</label>
                            <input type="checkbox" id="rememberPlayer2" name="rememberPlayer2">
                        </div>
                    <?php endif; ?>
                    
                    
                </div>
            </div>
            <div id="player-select-controls">
                <?php if($readyToPlay): ?>

                    <input class="btn" type="submit" name="logout" value="LOGOUT" >
                    <a href="./startGame.php" class="btn"><input class="btn" type="button" value="PLAY"></a>

                <?php elseif (isset($_SESSION["player1Name"]) || isset($_SESSION["player2Name"])): ?>

                    <input class="btn" type="submit" name="logout" value="LOGOUT" >
                    <a href="../register/register.php" class="btn"><input class="btn" type="button" value="REGISTER A PLAYER"></a>
                    <input class="btn" type="submit" name="confirm" value="CONFIRM">

                <?php else: ?>

                    <a href="../register/register.php" class="btn"><input class="btn" type="button" value="REGISTER A PLAYER"></a>
                    <input class="btn" type="submit" name="confirm" value="CONFIRM">

                <?php endif; ?>
            </div>
            
        </form>
        
    </div>
    
</body>
</html>