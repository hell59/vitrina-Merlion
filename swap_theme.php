<?php
session_start();

// Check if user is logged in already
if (!isset($_SESSION['id'])) {
    header('Location: /index.php');
}

// Swap the theme
include_once "includes/utils.php";
if (strcmp(get_theme(), "light") === 0) {
    set_theme("dark");
} else {
    set_theme("light");
}

// Redirect
if (!empty($_SERVER['HTTP_REFERER'])) {
    header("Location: {$_SERVER['HTTP_REFERER']}");
} else {
    header("Location: /index.php");
}