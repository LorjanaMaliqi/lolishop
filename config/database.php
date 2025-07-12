<?php
$host = 'localhost';
$dbname = 'loli_shop';
$username = 'root';
$password = '';

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    
    // Kontrolloni lidhjen
    if ($conn->connect_error) {
        die("Gabim në lidhje me databazën: " . $conn->connect_error);
    }
    
    // Vendosni charset
    $conn->set_charset("utf8");
    
} catch(Exception $e) {
    die("Gabim: " . $e->getMessage());
}
?>
