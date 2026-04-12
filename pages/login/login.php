<?php

session_start();
$loggedIn = isset($_SESSION["username"]);
if ($loggedIn) {
    header("Location: ../../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $remember = isset($_POST["remember-me"]);

    $file = "../../databases/users.json";
    $users = json_decode(file_get_contents($file), true);

    if (!$users) $users = [];

    foreach ($users as $user) {
        if ($user["username"] === $username && password_verify($password, $user["password"])) {
            $_SESSION["username"] = $username;

            if ($remember) {
                setcookie("username", $username, time() + (86400 * 7), "/"); // 7 days
            }

            header("Location: ../../index.php");
            exit;
        }
    }

    $error = "Invalid username or password";
}




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Computer Science Jeopardy - Login</title>
    <link rel="stylesheet" href="../../css/index.css">
    
</head>
<body>
    <div id="login-container">
        <div id="login-header">
            <h1>Login to Computer Science Jeopardy!</h1>
        </div>
        <div id="login-confirm-container">
            <?php
                    if (isset($_GET["registered"])) {
                    echo "<p style='color:green;'>Registration successful! Please log in.</p>";
                }
            ?>
        </div>
        <div id="login-form-container">
            <form method="post">
                <div>
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required><br><br>
                    
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required><br><br>

                    <input type="checkbox" id="remember" name="remember-me">
                    <label for="remember">Remember me</label><br><br>

                    <?php if (!empty($error)): ?>
                        <p style="color:red;"><?php echo $error; ?></p>
                    <?php endif; ?>
                </div>
                <div id="login-form-buttons">
                    <input type="submit" value="Login">
                    <a href="../register/register.php">Register</a>
                </div>
                
            </form>
        </div>
    </div>

</body>
</html>