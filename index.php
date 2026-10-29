<?php

session_start();
require_once 'functions.php';

$errors = [
    'login' => $_SESSION['login_error'] ?? '',
    'register' => $_SESSION['register_error'] ?? ''
];

$activeForm = $_SESSION['active_form'] ?? 'login';

session_unset();

function showError($error) {
    if (!empty($error)) {
        echo "<div class='error-message'>$error</div>";
    }
}

function isActiveForm($formName, $activeForm) {
    return $formName === $activeForm ? 'active' : '';
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Registration Page</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>

<body>
    <div class="container">
        <div class="form-box <?= isActiveForm('login', $activeForm); ?>" id="login-form">
            <form action="login_register.php" method="post">
                <h2>Login, if you dare...</h2>
                <?php showError($errors['login']); ?>
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Proceed</button>
                <p><a href="forgot_password.php">Forgot your password?</a></p>
                <p>Don't have an account? <a href="#" onclick="showForm('register-form')">Join the coven</a></p>
            </form>
        </div>

        <div class="form-box <?= isActiveForm('register', $activeForm); ?>" id="register-form">
            <form action="login_register.php" method="post">
                <h2>Join the Coven</h2>
                <?php showError($errors['register']); ?>
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" id="register-password" placeholder="Password" required>
                <p class="hint">Min 8 characters, with uppercase, lowercase, number, and symbol.</p>
                <select name="security_question" required>
                    <option value="" disabled selected>Choose a security question</option>
                    <?php foreach (getSecurityQuestions() as $q): ?>
                        <option value="<?= htmlspecialchars($q) ?>"><?= htmlspecialchars($q) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="security_answer" placeholder="Your answer" required>
                <button type="submit" name="register">Join</button>
                <p>Already have an account? <a href="#" onclick="showForm('login-form')">Login here</a></p>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>