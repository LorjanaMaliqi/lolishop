<?php
// Funksione të përgjithshme për aplikacionin

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function redirectTo($url) {
    header("Location: $url");
    exit();
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function formatPrice($price) {
    return number_format($price, 2) . ' €';
}

function getCartItemCount() {
    if (!isLoggedIn()) return 0;
    
    global $conn;
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT SUM(sasia) as total FROM shporta WHERE Id_perdoruesi = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['total'] ? $row['total'] : 0;
}

function addToCart($user_id, $product_id, $quantity = 1) {
    global $conn;
    
    error_log("addToCart called: user_id=$user_id, product_id=$product_id, quantity=$quantity");
    
    // Kontrolloni nëse produkti ekziston në shportë
    $sql = "SELECT Id_shporta, sasia FROM shporta WHERE Id_perdoruesi = ? AND Id_produkti = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        error_log("Product exists in cart, updating quantity");
        // Përditësoni sasinë
        $row = $result->fetch_assoc();
        $new_quantity = $row['sasia'] + $quantity;
        $sql = "UPDATE shporta SET sasia = ? WHERE Id_shporta = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $new_quantity, $row['Id_shporta']);
        $success = $stmt->execute();
        error_log("Update result: " . ($success ? "success" : "failed"));
        return $success;
    } else {
        error_log("Product not in cart, inserting new");
        // Shtoni produkt të ri
        $sql = "INSERT INTO shporta (Id_perdoruesi, Id_produkti, sasia) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $user_id, $product_id, $quantity);
        $success = $stmt->execute();
        error_log("Insert result: " . ($success ? "success" : "failed"));
        if (!$success) {
            error_log("MySQL error: " . $conn->error);
        }
        return $success;
    }
}

function removeFromCart($user_id, $product_id) {
    global $conn;
    $sql = "DELETE FROM shporta WHERE Id_perdoruesi = ? AND Id_produkti = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    return $stmt->execute();
}

function getCartItems($user_id) {
    global $conn;
    $sql = "SELECT s.*, p.emri, p.cmimi, p.foto 
            FROM shporta s 
            JOIN produktet p ON s.Id_produkti = p.Id_produkti 
            WHERE s.Id_perdoruesi = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result();
}

function calculateCartTotal($user_id) {
    global $conn;
    $sql = "SELECT SUM(s.sasia * p.cmimi) as total 
            FROM shporta s 
            JOIN produktet p ON s.Id_produkti = p.Id_produkti 
            WHERE s.Id_perdoruesi = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['total'] ? $row['total'] : 0;
}
?>
