<?php

session_start();

// Debugging: View POST data
//var_dump($_POST);

//echo time();


// Handle play button
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["play"])) {
    header("Location: ./startGame.php");
    exit;
}

// Handle register button
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["register"])) {
    header("Location: ../register/register.php");
    exit;
}

// Handle logout
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

if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["confirm"])) {
    $player1Name = $_POST["player1Name"];
    $player1Password = $_POST["player1Password"];
    $rememberPlayer1 = isset($_POST["rememberPlayer1"]);
    
    $player2Name = $_POST["player2Name"];
    $player2Password = $_POST["player2Password"];
    $rememberPlayer2 = isset($_POST["rememberPlayer2"]);

    $samePlayers = $player1Name === $player2Name;
    if($samePlayers) {
        $errorPlayer1 = "Player 1 cannot be the same as Player 2";
        $errorPlayer2 = "Player 2 cannot be the same as Player 1";
    } else {
        $file = "../../databases/users.json";
        $users = json_decode(file_get_contents($file), true);

        if (!$users) $users = [];

        $foundPlayer1 = false;
        $foundPlayer2 = false;


        foreach ($users as $user) {
            if ($user["username"] === $player1Name && password_verify($player1Password, $user["password"])) {
                $_SESSION["player1Name"] = $player1Name;
                $foundPlayer1 = true;

                if ($rememberPlayer1) {
                    setcookie("player1Name", $player1Name, time() + (86400 *7), "/");
                }
            }

            if ($user["username"] === $player2Name && password_verify($player2Password, $user["password"])) {
                $_SESSION["player2Name"] = $player2Name;
                $foundPlayer2 = true;

                if ($rememberPlayer2) {
                    setcookie("player2Name", $player2Name, time() + (86400 *7), "/");
                }
            }
        }

        if (!$foundPlayer1) $errorPlayer1 = "Player 1 Invalid Login";
        if (!$foundPlayer2) $errorPlayer2 = "Player 2 Invalid Login";  

    }



    
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
    <link rel="stylesheet" href="../../css/common.css">
    <link rel="stylesheet" href="../../css/playerSelect.css">
    
</head>
<body>
    <div id="player-select-container">
        <form method="post">
            <div id="player-select-header">
                <h1>Player Selection</h1>
            </div>
            <div id="players-container">
                <div class="player-container p1">
                    <?php if(isset($_SESSION["player1Name"])): ?>
                        <h2 class="success-welcome">WELCOME: <?php echo "<strong>" . htmlspecialchars($_SESSION["player1Name"]) . "!</strong>"; ?>
                    <?php else: ?>
                        <h2>Player 1 Login...</h2>
                        <?php if (!empty($errorPlayer1)): ?>
                            <p style="color:rgb(196, 46, 46);"><?php echo $errorPlayer1; ?></p>
                        <?php endif; ?>
                        <input type="text" id="player1Name" name="player1Name" placeholder="Player 1 Name..." >
                        <input type="password" id="player1Password" name="player1Password" placeholder="Player 1 Password..." >
                        <div class="player-remember-container">
                            <pre><label class="remember-label" for="rememberPlayer1">Remember me</label></pre>
                            <input type="checkbox" class="cb" style="accent-color: rgb(196, 46, 46);" id="rememberPlayer1" name="rememberPlayer1">
                        </div>

                    <?php endif; ?>
                    
                </div>
                <div class="player-container p2">
                    <?php if(isset($_SESSION["player2Name"])): ?>
                        <h2 class="success-welcome">WELCOME: <?php echo "<strong>" . htmlspecialchars($_SESSION["player2Name"]) . "!</strong>"; ?>
                    <?php else: ?>
                        <h2>Player 2 Login...</h2>
                        <?php if (!empty($errorPlayer2)): ?>
                            <p style="color:rgb(98, 29, 235);"><?php echo $errorPlayer2; ?></p>
                        <?php endif; ?>
                        <input type="text" id="player2Name" name="player2Name" placeholder="Player 2 Name..." >
                        <input type="password" id="player2Password" name="player2Password" placeholder="Player 2 Password..." >
                        <div class="player-remember-container">
                            <pre><label for="rememberPlayer2">Remember me</label></pre>
                            <input type="checkbox" class="cb" style="accent-color: rgb(98, 29, 235);" id="rememberPlayer2" name="rememberPlayer2" >
                        </div>
                    <?php endif; ?>
                    
                    
                </div>
            </div>
            <div id="player-select-controls">
                <?php if($readyToPlay): ?>

                    <input class="btn inv" type="submit" name="logout" value="LOGOUT" >
                    <input class="btn inv" type="submit" name="play" value="PLAY" >
                    

                <?php elseif (isset($_SESSION["player1Name"]) || isset($_SESSION["player2Name"])): ?>

                    <input class="btn inv" type="submit" name="logout" value="LOGOUT" >
                    <div class="btn inv">
                        <a href="../register/register.php" class="inv">REGISTER A USER</a>
                    </div>
                    <input class="btn inv" type="submit" name="confirm" value="CONFIRM">

                <?php else: ?>

                    <input class="btn inv" type="submit" name="register" value="REGISTER A USER">
                    <input class="btn inv" type="submit" name="confirm" value="CONFIRM">

                <?php endif; ?>
            </div>
            
        </form>
        
    </div>
    
</body>
</html>