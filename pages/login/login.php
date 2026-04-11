<?php



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Computer Science Jeopardy - Login</title>
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
            <form action="login.php" method="post">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required><br><br>
                
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required><br><br>
                
                <input type="submit" value="Login">
            </form>
        </div>
    </div>

</body>
</html>