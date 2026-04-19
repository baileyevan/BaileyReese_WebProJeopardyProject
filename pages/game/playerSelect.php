<?php
session_start();

// num of players
$numPlayers = isset($_SESSION["numPlayers"]) ? $_SESSION["numPlayers"] : 2;

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

    for ($i = 1; $i <= $numPlayers; $i++) {
        unset($_SESSION["player{$i}Name"]);
        setcookie("player{$i}Name", "", time() - 3600, "/");
    }

    header("Location: ./playerSelect.php");
    exit;
}

// login logic
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

// check readiness
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

            <?php for ($i = 1; $i <= $numPlayers; $i++): ?>
                <div class="player-container">

                    <?php if(isset($_SESSION["player{$i}Name"])): ?>

                        <h2 class="success-welcome">
                            WELCOME: <strong><?php echo htmlspecialchars($_SESSION["player{$i}Name"]); ?>!</strong>
                        </h2>

                    <?php else: ?>

                        <h2>Player <?php echo $i; ?> Login...</h2>

                        <?php if (!empty(${"errorPlayer{$i}"})): ?>
                            <p style="color:red; font-weight:800;">
                                <?php echo ${"errorPlayer{$i}"}; ?>
                            </p>
                        <?php endif; ?>

                        <input type="text" name="player<?php echo $i; ?>Name" placeholder="Player <?php echo $i; ?> Name...">
                        <input type="password" name="player<?php echo $i; ?>Password" placeholder="Player <?php echo $i; ?> Password...">

                        <div class="player-remember-container">
                            <label>Remember me</label>
                            <input type="checkbox" class="cb" name="rememberPlayer<?php echo $i; ?>">
                        </div>

                    <?php endif; ?>

                </div>
            <?php endfor; ?>

        </div>

        <div id="player-select-controls">

            <?php if($readyToPlay): ?>

                <input class="btn inv" type="submit" name="logout" value="LOGOUT">
                <input class="btn inv" type="submit" name="play" value="PLAY">

            <?php else: ?>

                <input class="btn inv" type="submit" name="logout" value="LOGOUT">
                <input class="btn inv" type="submit" name="confirm" value="CONFIRM">

            <?php endif; ?>

        </div>

    </form>
</div>

</body>
</html>