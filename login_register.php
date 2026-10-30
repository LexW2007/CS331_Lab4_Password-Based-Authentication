<?php

session_start();
require_once 'config.php';
require_once 'functions.php';

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $rawPassword = $_POST['password'];
    $securityQuestion = $_POST['security_question'] ?? '';
    $securityAnswer = strtolower(trim($_POST['security_answer'] ?? ''));

    $passwordError = validatePassword($rawPassword);

    if ($passwordError) {
        $_SESSION['register_error'] = $passwordError;
        $_SESSION['active_form'] = 'register';
    } elseif ($securityQuestion === '' || $securityAnswer === '') {
        $_SESSION['register_error'] = "Please choose a security question and provide an answer.";
        $_SESSION['active_form'] = 'register';
    } else {
        $checkStmt = $conn->prepare("SELECT username FROM allusers WHERE username = ?");
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $_SESSION['register_error'] = "Username already exists. Please choose a different username.";
            $_SESSION['active_form'] = 'register';
        } else {
            $hashedPassword = hashSecret($rawPassword, PASSWORD_DEFAULT);
            $hashedAnswer = hashSecret($securityAnswer, PASSWORD_DEFAULT);

            $insertStmt = $conn->prepare(
                "INSERT INTO allusers (username, password, security_question, security_answer) VALUES (?, ?, ?, ?)"
            );
            $insertStmt->bind_param("ssss", $username, $hashedPassword, $securityQuestion, $hashedAnswer);
            $insertStmt->execute();
        }
    }

    header("Location: index.php");
    exit();
}

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // One message for every failure: bad user, bad password, or locked.
    $genericError = "Invalid username or password. Accounts are temporarily locked after repeated failed attempts.";

    $stmt = $conn->prepare(
        "SELECT id, password, (locked_until IS NOT NULL AND locked_until > NOW()) AS is_locked
         FROM allusers WHERE username = ?"
    );
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    // Always run one hash operation so response time doesn't reveal
    // whether the username exists.
    $hashToCheck = $user ? $user['password'] : hashSecret($password);
    $passwordOk  = password_verify($password, $hashToCheck);

    if ($user && !$user['is_locked'] && $passwordOk) {
        // Success: clear the counter and lock, upgrade hash if needed
        $reset = $conn->prepare("UPDATE allusers SET failed_attempts = 0, locked_until = NULL WHERE id = ?");
        $reset->bind_param("i", $user['id']);
        $reset->execute();

        if (password_needs_rehash($user['password'], PASSWORD_BCRYPT, ['cost' => 12])) {
            $newHash = hashSecret($password);
            $up = $conn->prepare("UPDATE allusers SET password = ? WHERE id = ?");
            $up->bind_param("si", $newHash, $user['id']);
            $up->execute();
        }

        session_regenerate_id(true);   // prevent session fixation
        $_SESSION['username'] = $username;
        header("Location: user_page.php");
        exit();
    }

    if ($user && !$user['is_locked']) {
        // Failed attempt on a real, unlocked account: count it, lock on the 3rd.
        // (MySQL evaluates SET assignments left to right, so locked_until
        // sees the old counter and failed_attempts is reset after locking.)
        $max = (int) MAX_FAILED_ATTEMPTS;
        $mins = (int) LOCKOUT_MINUTES;
        $fail = $conn->prepare(
            "UPDATE allusers SET
                locked_until    = IF(failed_attempts + 1 >= $max, DATE_ADD(NOW(), INTERVAL $mins MINUTE), locked_until),
                failed_attempts = IF(failed_attempts + 1 >= $max, 0, failed_attempts + 1)
             WHERE id = ?"
        );
        $fail->bind_param("i", $user['id']);
        $fail->execute();
    }

    $_SESSION['login_error'] = $genericError;
    $_SESSION['active_form'] = 'login';
    header("Location: index.php");
    exit();
}

?>