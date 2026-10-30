<?php

function getSecurityQuestions() {
    return [
        "What was the name of your first pet?",
        "What is your mother's maiden name?",
        "What was the name of your first school?",
        "What city were you born in?",
        "What was your childhood nickname?",
    ];
}

function validatePassword($password) {
    if (strlen($password) < 8) {
        return "Password must be at least 8 characters long.";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return "Password must include at least one uppercase letter.";
    }
    if (!preg_match('/[a-z]/', $password)) {
        return "Password must include at least one lowercase letter.";
    }
    if (!preg_match('/[0-9]/', $password)) {
        return "Password must include at least one number.";
    }
    if (!preg_match('/[\W_]/', $password)) {
        return "Password must include at least one special character.";
    }
    return '';
}

define('MAX_FAILED_ATTEMPTS', 3);
define('LOCKOUT_MINUTES', 15);

// bcrypt: salted, adaptive hash. PHP generates a fresh random 128-bit salt which is embedded in the string.
function hashSecret(string $plain): string {
    return password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]);
}

?>