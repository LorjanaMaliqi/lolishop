<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_to_cart') {
    if (!isLoggedIn()) {
        redirectTo('login.php');
    }
    
    $user_id = $_SESSION['user_id'];
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity > 0) {
        addToCart($user_id, $product_id, $quantity);
        $_SESSION['success_message'] = 'Produkti u shtua në shportë!';
        redirectTo('cart.php');
    }
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header('Location: produktet.php');
    exit;
}

// Get product details
$sql = "SELECT p.*, k.emri as kategoria_emri 
        FROM produktet p 
        LEFT JOIN kategorite k ON p.Id_kategoria = k.Id_kategoria 
        WHERE p.id_produkti = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$produkti = $result->fetch_assoc();

if (!$produkti) {
    header('Location: produktet.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($produkti['emri']) ?> - Loli Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <!-- Breadcrumb Section -->
        <section style="background: #f8f9fa; padding: 1rem 0; border-bottom: 1px solid #e9ecef;">
            <div class="container">
                <nav style="font-size: 0.9rem;">
                    <a href="index.php" style="color: #6c757d; text-decoration: none;">Ballina</a>
                    <i class="fas fa-chevron-right" style="margin: 0 0.5rem; color: #6c757d; font-size: 0.7rem;"></i>
                    <a href="produktet.php" style="color: #6c757d; text-decoration: none;">Produktet</a>
                    <i class="fas fa-chevron-right" style="margin: 0 0.5rem; color: #6c757d; font-size: 0.7rem;"></i>
                    <a href="produktet.php?kategoria=<?= $produkti['Id_kategoria'] ?>" style="color: #6c757d; text-decoration: none;"><?= htmlspecialchars($produkti['kategoria_emri']) ?></a>
                    <i class="fas fa-chevron-right" style="margin: 0 0.5rem; color: #6c757d; font-size: 0.7rem;"></i>
                    <span style="color: #c7a76f; font-weight: 600;"><?= htmlspecialchars($produkti['emri']) ?></span>
                </nav>
            </div>
        </section>

        <!-- Product Details Section -->
        <section style="padding: 4rem 0; background: linear-gradient(135deg, #e0d1b6 0%, #f0e8d8 100%); min-height: 100vh;">
            <div class="container">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; max-width: 1200px; margin: 0 auto;">
                    
                    <!-- Product Image -->
                    <div class="product-image-section">
                        <div style="background: white; border-radius: 20px; padding: 2rem; box-shadow: 0 8px 32px rgba(0,0,0,0.1); height: 100%; display: flex; flex-direction: column;">
                            <div style="aspect-ratio: 1; background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border-radius: 16px; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; margin-bottom: 2rem; flex: 1;">
                                <?php if (!empty($produkti['foto'])): ?>
                                    <img src="assets/images/<?= htmlspecialchars($produkti['foto']) ?>" 
                                         alt="<?= htmlspecialchars($produkti['emri']) ?>"
                                         style="width: 90%; height: 90%; object-fit: contain; object-position: center; border-radius: 12px;">
                                <?php else: ?>
                                    <div style="text-align: center; color: #6c757d;">
                                        <i class="fas fa-image" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                                        <p style="margin: 0; font-size: 1.1rem; font-weight: 500;">Pa imazh</p>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Product Badge -->
                                <div style="position: absolute; top: 1rem; right: 1rem; background: linear-gradient(135deg, #c7a76f 0%, #4d4b49 100%); color: white; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                                    <?= htmlspecialchars($produkti['kategoria_emri']) ?>
                                </div>
                            </div>

                            <!-- Quantity and Add to Cart Section -->
                            <div style="border-top: 2px solid #e9ecef; padding-top: 2rem; flex-shrink: 0;">
                                <div style="margin-bottom: 1.5rem;">
                                    <label style="display: block; margin-bottom: 0.5rem; color: #4d4b49; font-weight: 600;">Sasia:</label>
                                    <div style="display: flex; border: 2px solid #c7a76f; border-radius: 8px; overflow: hidden; max-width: 120px;">
                                        <button id="decreaseBtn" style="background: #c7a76f; color: white; border: none; padding: 0.75rem; cursor: pointer; transition: background 0.3s;">-</button>
                                        <input type="number" id="quantity" value="1" min="1" max="10" 
                                               style="border: none; text-align: center; padding: 0.75rem; width: 60px; background: white;">
                                        <button id="increaseBtn" style="background: #c7a76f; color: white; border: none; padding: 0.75rem; cursor: pointer; transition: background 0.3s;">+</button>
                                    </div>
                                </div>

                                <div>
                                    <button class="add-to-cart" data-id="<?= $produkti['Id_produkti'] ?>"
                                            style="width: 100%; background: linear-gradient(135deg, #c7a76f 0%, #4d4b49 100%); color: white; border: none; padding: 1.2rem 2rem; border-radius: 12px; font-size: 1.1rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; cursor: pointer; transition: all 0.3s ease;">
                                        <i class="fas fa-shopping-cart" style="margin-right: 0.5rem;"></i>
                                        Shto në Shportë
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="product-info-section">
                        <div style="background: white; border-radius: 20px; padding: 3rem; box-shadow: 0 8px 32px rgba(0,0,0,0.1); height: 100%; display: flex; flex-direction: column;">
                            <!-- Product Title -->
                            <h1 style="color: #4d4b49; font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem; line-height: 1.2;">
                                <?= htmlspecialchars($produkti['emri']) ?>
                            </h1>

                            <!-- Price -->
                            <div style="margin-bottom: 2rem;">
                                <span style="font-size: 2.5rem; font-weight: 700; color: #c7a76f;">€<?= number_format($produkti['cmimi'], 2) ?></span>
                            </div>

                            <!-- Description -->
                            <div style="flex: 1; padding: 2rem; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 16px; border-left: 4px solid #c7a76f;">
                                <h3 style="color: #4d4b49; margin-bottom: 1rem; font-size: 1.3rem; font-weight: 600;">
                                    <i class="fas fa-info-circle" style="color: #c7a76f; margin-right: 0.5rem;"></i>
                                    Përshkrimi i Produktit
                                </h3>
                                <p style="color: #6c757d; line-height: 1.7; margin: 0; font-size: 1.1rem;">
                                    <?= nl2br(htmlspecialchars($produkti['pershkrimi'])) ?>
                                </p>
                                <!-- Shto info tërheqëse këtu -->
                                <div style="margin-top: 1.5rem;">
                                    <ul style="color: #4d4b49; font-size: 1rem; line-height: 1.7; padding-left: 1.2rem;">
                                        <li><b>Porosit sot</b> dhe përfito transport të shpejtë në të gjithë Kosovën!</li>
                                        <li><b>Cilësi e garantuar</b> – çdo produkt kontrollohet me kujdes para dërgesës.</li>
                                        <li><b>Eksperiencë unike</b> në blerje online, me mbështetje të dedikuar për klientët tanë.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
