<?php
$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    $file = "../../databases/users.json";

    $users = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
    if (!$users) $users = [];

    foreach ($users as $user) {
        if ($user["username"] === $username) {
            $error = "Username already taken";
            break;
        }
    }

    if (!$error) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $users[] = [
            "username" => $username,
            "password" => $hashedPassword
        ];

        file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);

        // Redirect to login page after successful registration
        header("Location: ../login/login.php?registered=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Computer Science Jeopardy - Register</title>
</head>
<body>
    <div id="register-container">
        <div id="register-header">
            <h1>Register for Computer Science Jeopardy!</h1>
        </div>
    
        <div id="register-form">
            <form method="post">
                <label for="username">Username:</label>
                <?php if ($error): ?>
                    <p style="color:red;"><?php echo $error; ?></p>
                <?php endif; ?>

                <?php if ($message): ?>
                    <p style="color:green;"><?php echo $message; ?></p>
                <?php endif; ?>

                <input type="text" id="username" name="username" required><br><br>
                
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required><br><br>
                
                <input type="submit" value="Register">
            </form>
        </div>
    </div>
    
</body>
</html>