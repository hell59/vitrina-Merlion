<?php
session_start();

// Check if user is not logged in
if (!isset($_SESSION['id'])) {
    header('Location: /login.php');
    echo "Not logged in";
    die();
}

// Check that all parameters are filled out
if (!isset($_POST['id']) || !isset($_POST['username']) || !isset($_POST['email']) || !isset($_POST['password'])) {
    header('Location: /index.php');
    echo "Missing parameters";
    die();
}

// Check that parameters are not empty
if (empty($_POST['id'])) {
    header('Location: /index.php');
    echo "Empty parameters";
    die();
}

// Check that we have permissions
include_once 'includes/db_connect.php';

$res = pg_prepare($db_conn, "is_admin_query", 'SELECT is_admin::int FROM users WHERE id = $1');
$res = pg_execute($db_conn, "is_admin_query", array($_SESSION['id']));
$row = pg_fetch_row($res);
$_SESSION['is_admin'] = $row[0]; // Update value since we can

if ($_SESSION['id'] !== $_POST['id'] && !(bool)$row[0]) {
    header("Location: /user.php?id={$_POST['id']}");
    echo "Missing permissions";
    die();
}

// Update the user
if (!empty($_POST['username'])) {
    if (strlen($_POST['username']) <= 100) {
        // Ensure username is unique
        $res = pg_prepare($db_conn, "check_username_query", 'SELECT id FROM users WHERE username = $1');
        $res = pg_execute($db_conn, "check_username_query", array($_POST['username']));
        if (pg_num_rows($res) == 0) {
            $res = pg_prepare($db_conn, "update_user_username_query", 'UPDATE users SET username = $1 WHERE id = $2');
            $res = pg_execute($db_conn, "update_user_username_query", array($_POST['username'], $_POST['id']));

            // Update session username if we are logged in as this user
            if ($_SESSION['id'] === $_POST['id']) {
                $_SESSION['username'] = $_POST['username'];
            }
        } else {
            echo "Skipping username which already exists\n";
        }
    } else {
        echo "Skipped invalid username\n";
    }
}

if (!empty($_POST['email'])) {
    if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        // Ensure email is unique
        $res = pg_prepare($db_conn, "check_email_query", 'SELECT id FROM users WHERE email = $1');
        $res = pg_execute($db_conn, "check_email_query", array($_POST['email']));
        if (pg_num_rows($res) == 0) {
            $res = pg_prepare($db_conn, "update_user_email_query", 'UPDATE users SET email = $1 WHERE id = $2');
            $res = pg_execute($db_conn, "update_user_email_query", array($_POST['email'], $_POST['id']));
        } else {
            echo "Skipping email which already exists\n";
        }
    } else {
        echo "Skipped invalid email\n";
    }
}

if (!empty($_POST['password'])) {
    $res = pg_prepare($db_conn, "update_user_password_query", 'UPDATE users SET password = $1 WHERE id = $2');
    $res = pg_execute($db_conn, "update_user_password_query", array(md5($db_salt . $_POST['password']), $_POST['id']));
}

header("Location: /user.php?id={$_POST['id']}");
echo "User updated";
?>