<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role_id'] == 2) {
        header('Location: hr/index.php');
    } else if ($_SESSION['role_id'] == 1) {
        header('Location: director/index.php');
    } else {
        header('Location: login.php');
    }
} else {
    header('Location: login.php');
}
exit;