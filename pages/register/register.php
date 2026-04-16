<?php


$loggedIn = isset($_COOKIE["username"]);
if ($loggedIn) {
    header("Location: ../../index.php");
    exit;
}

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
        header("Location: ../game/playerSelect.php");
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
    <link rel="stylesheet" href="../../css/common.css">
    <link rel="stylesheet" href="../../css/register.css">

</head>
<body>
    <div id="register-container" class="bs">
        <div id="register-header">
            <h1>Register for Computer Science Jeopardy!</h1>
        </div>
    
        <div id="register-form" class="bs">
            <form method="post">
                <div id="credentials-container" class="">
                    <div class="credential-container">
                        <label class="labels-for-input" for="username">Username:</label>
                        <?php if ($error): ?>
                            <p style="color:red;"><?php echo $error; ?></p>
                        <?php endif; ?>

                        <?php if ($message): ?>
                            <p style="color:green;"><?php echo $message; ?></p>
                        <?php endif; ?>

                        <input placeholder="USERNAME" type="text" id="username" name="username" required><br><br>
                    </div>
                    <div class="credential-container bs>
                        <label class="labels-for-input" for="password">Password:</label>
                        <input placeholder="PASSWORD" type="password" id="password" name="password" required><br><br>
                    </div>
                </div>

                

                
                <input type="submit" class="btn bs" value="Register">
            </form>
        </div>
    </div>
    
</body>
</html>