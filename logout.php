<?php
session_start();
require_once 'includes/functions.php';

// Destroy session
session_destroy();

// Redirect to home page
redirectTo('index.php');
?>