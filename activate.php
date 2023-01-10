<?php
session_start();

// Check if user is logged in already
if (isset($_SESSION['id'])) {
    header('Location: /index.php');
}

if (isset($_GET['code'])) {
    // Check if code is formatted correctly (regex)
    if (preg_match('/^[A-z0-9]{32}$/', $_GET['code'])) {
        // Check for code in database
        include_once 'includes/db_connect.php';

        $res = pg_prepare($db_conn, "check_code_query", 'SELECT id, is_activated::int FROM users WHERE activation_code=$1');
        $res = pg_execute($db_conn, "check_code_query", array($_GET['code']));

        if (pg_num_rows($res) == 1) {
            // Check if account already activated
            $row = pg_fetch_row($res);
            if (!(bool)$row[1]) {
                // Activate account
                $res = pg_prepare($db_conn, "activate_account_query", 'UPDATE users SET is_activated=TRUE WHERE id=$1');
                $res = pg_execute($db_conn, "activate_account_query", array($row[0]));
                
                $alert = "Account activated!";
                $alert_type = "success";
            } else {
                $alert = 'Account already activated.';
            }
        } else {
            $alert = "Invalid activation code.";
        }
    } else {
        $alert = "Invalid activation code.";
    }
} else {
    $alert = "Missing activation code.";
}
?>

<html>
    <head>
        <title>Vitrina : Activate account</title>
        <?php include_once 'includes/header.php'; ?>
    </head>
    <body>
        <?php include_once 'includes/navbar.php'; ?>
        <div class="uk-container uk-container-xsmall">
            <?php
            // Display any alerts
            if (isset($alert)) {
            ?>
                <div uk-alert class="uk-alert-<?php if(isset($alert_type)){echo $alert_type;}else{echo 'danger';} ?>">
                    <a class="uk-alert-close" uk-close></a>
                    <?=$alert?>
                </div>
            <?php
            }
            ?>
        </div>
    </body>
</html>