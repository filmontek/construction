<?php
require_once 'includes/functions.php';

// Redirect to appropriate page based on login status
if (isLoggedIn()) {
    header('Location: pages/dashboard.php');
} else {
    header('Location: pages/login.php');
}
exit();
?>