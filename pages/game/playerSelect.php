<?php

// num of players
$numPlayers = isset($_SESSION["numPlayers"]) ? $_SESSION["numPlayers"] : 2;

session_start();

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
 // fix login logic for multiplayer
if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["confirm"])) {

    $file = "../../databases/users.json";
    $users = json_decode(file_get_contents($file), true);
    if (!$users) $users = [];

    for ($i = 1; $i <= $numPlayers; $i++) {

        $name = $_POST["player{$i}Name"] ?? "";
        $password = $_POST["player{$i}Password"] ?? "";
        $remember = isset($_POST["rememberPlayer{$i}"]);

        $found = false;

        foreach ($users as $user) {
            if ($user["username"] === $name && password_verify($password, $user["password"])) {

                $_SESSION["player{$i}Name"] = $name;
                $found = true;

                if ($remember) {
                    setcookie("player{$i}Name", $name, time() + (86400 * 7), "/");
                }
            }
        }

        if (!$found) {
            ${"errorPlayer{$i}"} = "Player {$i} Invalid Login";
        }
    }
}

// if all players exist in the session then they
// are logged in and ready to play
$readyToPlay = true;

for ($i = 1; $i <= $numPlayers; $i++) {
    if (!isset($_SESSION["player{$i}Name"])) {
        $readyToPlay = false;
        break;
    }
}

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
                <?php
                /* logic for dynamic player generation */
                    for ($i = 1; $i <= $numPlayers; $i++):
                        $sessionKey = "player" . $i . "Name";
                        $errorVar = "errorPlayer" . $i;
                ?>
                    <div class="player-container">
                        
                        <?php if(isset($_SESSION[$sessionKey])): ?>
                            <!-- If player already logged in -->
                            <h2 class="success-welcome">
                                WELCOME: <strong><?php echo htmlspecialchars($_SESSION[$sessionKey]); ?>!</strong>
                            </h2>

                        <?php else: ?>
                            <!-- Login form for each player -->
                            <h2>Player <?php echo $i; ?> Login...</h2>

                            <?php if (!empty($$errorVar)): ?>
                                <p style="color:red; font-weight:800;">
                                    <?php echo $$errorVar; ?>
                                </p>
                            <?php endif; ?>

                            <input type="text" name="player<?php echo $i; ?>Name" placeholder="Player <?php echo $i; ?> Name..." >
                            <input type="password" name="player<?php echo $i; ?>Password" placeholder="Player <?php echo $i; ?> Password..." >

                            <div class="player-remember-container">
                                <label for="rememberPlayer<?php echo $i; ?>">Remember me</label>
                                <input type="checkbox" class="cb" name="rememberPlayer<?php echo $i; ?>">
                            </div>

                        <?php endif; ?>

                    </div>
                <?php endfor; ?>
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