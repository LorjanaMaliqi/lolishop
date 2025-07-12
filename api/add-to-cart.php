<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Debug logging
error_log("Add to cart request: " . print_r($_POST, true));
error_log("Session data: " . print_r($_SESSION, true));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isLoggedIn()) {
    error_log("User not logged in");
    echo json_encode(['success' => false, 'login_required' => true]);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = (int)$_POST['product_id'];
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

error_log("Processing: user_id=$user_id, product_id=$product_id, quantity=$quantity");

if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
    exit;
}

// Check if product exists
$sql = "SELECT id_produkti FROM produktet WHERE id_produkti = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Produkti nuk ekziston']);
    exit;
}

// Add to cart
if (addToCart($user_id, $product_id, $quantity)) {
    $cart_count = getCartItemCount();
    error_log("Success: Product added to cart. New cart count: $cart_count");
    echo json_encode([
        'success' => true, 
        'message' => 'Produkti u shtua në shportë',
        'cart_count' => $cart_count
    ]);
} else {
    error_log("Failed to add product to cart");
    echo json_encode(['success' => false, 'message' => 'Gabim gjatë shtimit në shportë']);
}
?>
