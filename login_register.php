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
            $hashedPassword = password_hash($rawPassword, PASSWORD_DEFAULT);
            $hashedAnswer = password_hash($securityAnswer, PASSWORD_DEFAULT);

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

    $stmt = $conn->prepare("SELECT * FROM allusers WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username;
            header("Location: user_page.php");
            exit();
        } else {
            $_SESSION['login_error'] = "Incorrect password. Please try again.";
            $_SESSION['active_form'] = 'login';
        }
    } else {
        $_SESSION['login_error'] = "Username not found. Please register first.";
        $_SESSION['active_form'] = 'login';
    }

    header("Location: index.php");
    exit();
}

?>