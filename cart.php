<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirectTo('login.php');
}

$user_id = $_SESSION['user_id'];
$cart_items = getCartItems($user_id);
$total = calculateCartTotal($user_id);

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update':
                $product_id = (int)$_POST['product_id'];
                $quantity = (int)$_POST['quantity'];
                
                if ($quantity > 0) {
                    $sql = "UPDATE shporta SET sasia = ? WHERE Id_perdoruesi = ? AND Id_produkti = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iii", $quantity, $user_id, $product_id);
                    $stmt->execute();
                } else {
                    removeFromCart($user_id, $product_id);
                }
                break;
                
            case 'remove':
                $product_id = (int)$_POST['product_id'];
                removeFromCart($user_id, $product_id);
                break;
        }
        
        // Refresh page to show updated cart
        redirectTo('cart.php');
    }
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shporta - Loli Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="cart-main">
        <div class="container">
            <h1><i class="fas fa-shopping-cart"></i> Shporta Juaj</h1>
            
            <?php if ($cart_items && $cart_items->num_rows > 0): ?>
                <div class="cart-content">
                    <div class="cart-items">
                        <?php while ($item = $cart_items->fetch_assoc()): ?>
                            <div class="cart-item">
                                <div class="item-image">
                                    <img src="assets/images/<?php echo htmlspecialchars($item['foto']); ?>" alt="<?php echo htmlspecialchars($item['emri']); ?>">
                                </div>
                                
                                <div class="item-details">
                                    <h3><?php echo htmlspecialchars($item['emri']); ?></h3>
                                    <p class="item-price">€<?php echo number_format($item['cmimi'], 2); ?></p>
                                </div>
                                
                                <div class="item-quantity">
                                    <form method="POST" class="quantity-form">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?php echo $item['Id_produkti']; ?>">
                                        <div class="quantity-controls">
                                            <button type="button" class="qty-btn minus">-</button>
                                            <input type="number" name="quantity" value="<?php echo $item['sasia']; ?>" min="0" max="99">
                                            <button type="button" class="qty-btn plus">+</button>
                                        </div>
                                    </form>
                                </div>
                                
                                <div class="item-total">
                                    €<?php echo number_format($item['cmimi'] * $item['sasia'], 2); ?>
                                </div>
                                
                                <div class="item-actions">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="product_id" value="<?php echo $item['Id_produkti']; ?>">
                                        <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Jeni i sigurt që doni ta hiqni këtë produkt?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <div class="cart-summary">
                        <div class="summary-box">
                            <h3>Përmbledhja e Porosisë</h3>
                            <div class="summary-line">
                                <span>Nëntotali:</span>
                                <span>€<?php echo number_format($total, 2); ?></span>
                            </div>
                            <div class="summary-line">
                                <span>Dërgesa:</span>
                                <span>Falas</span>
                            </div>
                            <hr>
                            <div class="summary-line total">
                                <span>Totali:</span>
                                <span>€<?php echo number_format($total, 2); ?></span>
                            </div>
                            
                            <div class="checkout-actions">
                                <a href="checkout.php" class="btn btn-primary btn-full">
                                    <i class="fas fa-credit-card"></i> Vazhdoni te Pagesa
                                </a>
                                <a href="produktet.php" class="btn btn-secondary btn-full">
                                    <i class="fas fa-arrow-left"></i> Vazhdoni Blerjet
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Empty cart message -->
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h2>Shporta juaj është bosh</h2>
                    <p>Nuk keni shtuar ende asnjë produkt në shportë.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/script.js"></script>
</body>
</html>
