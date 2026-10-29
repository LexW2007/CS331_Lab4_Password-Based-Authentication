<?php

session_start();
require_once 'config.php';
require_once 'functions.php';

if (isset($_POST['find_user'])) {
    $username = trim($_POST['username']);

    $stmt = $conn->prepare("SELECT security_question FROM allusers WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['reset_username'] = $username;
        $_SESSION['reset_question'] = $user['security_question'];
        $_SESSION['reset_stage'] = 'answer';
    } else {
        
        $_SESSION['reset_error'] = "We couldn't find that account, or the answer didn't match. Please try again.";
        $_SESSION['reset_stage'] = 'username';
    }
    header("Location: forgot_password.php");
    exit();
}

if (isset($_POST['verify_answer'])) {
    $username = $_SESSION['reset_username'] ?? '';
    $answer = strtolower(trim($_POST['security_answer'] ?? ''));

    $stmt = $conn->prepare("SELECT security_answer FROM allusers WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    $verified = false;
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $verified = password_verify($answer, $user['security_answer']);
    }

    if ($verified) {
        $_SESSION['reset_stage'] = 'reset';
    } else {
        $_SESSION['reset_error'] = "We couldn't find that account, or the answer didn't match. Please try again.";
        $_SESSION['reset_stage'] = 'username';
        unset($_SESSION['reset_username'], $_SESSION['reset_question']);
    }
    header("Location: forgot_password.php");
    exit();
}

if (isset($_POST['reset_password'])) {
    $username = $_SESSION['reset_username'] ?? '';
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($username === '') {
        $_SESSION['reset_stage'] = 'username';
        header("Location: forgot_password.php");
        exit();
    }

    $passwordError = validatePassword($newPassword);

    if ($newPassword !== $confirmPassword) {
        $_SESSION['reset_error'] = "Passwords do not match.";
        $_SESSION['reset_stage'] = 'reset';
    } elseif ($passwordError) {
        $_SESSION['reset_error'] = $passwordError;
        $_SESSION['reset_stage'] = 'reset';
    } else {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE allusers SET password = ? WHERE username = ?");
        $stmt->bind_param("ss", $hashed, $username);
        $stmt->execute();

        unset($_SESSION['reset_username'], $_SESSION['reset_question'], $_SESSION['reset_stage']);
        $_SESSION['login_error'] = "Password reset successful. Please log in with your new password.";
        $_SESSION['active_form'] = 'login';
        header("Location: index.php");
        exit();
    }
    header("Location: forgot_password.php");
    exit();
}

$stage = $_SESSION['reset_stage'] ?? 'username';
$error = $_SESSION['reset_error'] ?? '';
$question = $_SESSION['reset_question'] ?? '';
unset($_SESSION['reset_error']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body>
    <div class="container">
        <div class="form-box active" id="reset-form">

            <?php if ($stage === 'username'): ?>
                <h2>Forgot Password</h2>
                <?php if ($error): ?><div class="error-message"><?= htmlspecialchars($error) ?></div><?php endif; ?>
                <form action="forgot_password.php" method="post">
                    <input type="text" name="username" placeholder="Username" required>
                    <button type="submit" name="find_user">Continue</button>
                </form>

            <?php elseif ($stage === 'answer'): ?>
                <h2>Security Question</h2>
                <?php if ($error): ?><div class="error-message"><?= htmlspecialchars($error) ?></div><?php endif; ?>
                <p class="hint"><?= htmlspecialchars($question) ?></p>
                <form action="forgot_password.php" method="post">
                    <input type="text" name="security_answer" placeholder="Your answer" required>
                    <button type="submit" name="verify_answer">Verify</button>
                </form>

            <?php elseif ($stage === 'reset'): ?>
                <h2>Choose a New Password</h2>
                <?php if ($error): ?><div class="error-message"><?= htmlspecialchars($error) ?></div><?php endif; ?>
                <p class="hint">Min 8 characters, with uppercase, lowercase, number, and symbol.</p>
                <form action="forgot_password.php" method="post">
                    <input type="password" name="password" id="reset-password" placeholder="New password" required>
                    <input type="password" name="confirm_password" placeholder="Confirm new password" required>
                    <button type="submit" name="reset_password">Reset Password</button>
                </form>
            <?php endif; ?>

            <p><a href="index.php">Back to login</a></p>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>