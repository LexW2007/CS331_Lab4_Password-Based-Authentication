# CS331 Project 1 Password System

## Author: Lex Watts

### Software Used

VSCode - IDE

XAMPP - Apache and MySQL manager

Claude - AI Assistant

### Setup

1. Start Apache and MySQL in XAMPP.

2. Create the users_db database.

3. Run the init_table.sql code in phpMyAdmin to create the users table with the security_question and security_answer columns.

4. Paste this project's files into your XAMPP htdocs folder.

5. Visit http://localhost/"project-folder"/index.php

### Features

Registration — unique usernames are required, password hashed with password_hash(), password rules, and a security question/answer chosen at signup (answer is hashed too).

Login — verifies against the stored hash with password_verify().

Password storage — all passwords and security-question answers are stored as hashes.

Password reset — forgot_password.php implements a 3-step flow:

1. Enter your username.

2. Answer your security question.

3. Choose and confirm a new password.

Password complexity — minimum 8 characters, at least one uppercase letter, one lowercase letter, one digit, and one special character.

### Files

index.php — main login/register page

login_register.php — handles registration and login POST requests

forgot_password.php — handles the password reset flow

user_page.php — page shown after a successful login

functions.php — shared password validation + security question list

config.php — database connection

init_table.sql — SQL to set up/update the users table

script.js — client-side behavior

style.css - styling

### Notes for the security writeup

Both the "user not found" and "wrong security answer" cases in forgot_password.php return the same error message, so the reset flow can't be used to enumerate valid usernames.

All secrets (passwords and security answers) are hashed, never compared or stored in plaintext.
