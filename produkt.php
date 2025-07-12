<?php
session_start();
require_once 'config/dat// Get related products from same category
$related_sql = "SELECT p.*, k.emri as kategoria_emri 
                FROM produktet p 
                LEFT JOIN kategorite k ON p.Id_kategoria = k.Id_kategoria 
                WHERE p.Id_kategoria = ? AND p.id_produkti != ? 
                ORDER BY RAND() LIMIT 4";

$related_stmt = $conn->prepare($related_sql);
$related_stmt->bind_param("ii", $produkti['Id_kategoria'], $product_id);';
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

// Get related products from same category
$related_sql = "SELECT p.*, k.emri as kategoria_emri 
                FROM produktet p 
                LEFT JOIN kategorite k ON p.ld_kategoria = k.Id_kategoria 
                WHERE p.ld_kategoria = ? AND p.id_produkti != ? 
                ORDER BY RAND() LIMIT 4";

$related_stmt = $conn->prepare($related_sql);
$related_stmt->bind_param("ii", $produkti['ld_kategoria'], $product_id);
$related_stmt->execute();
$related_result = $related_stmt->get_result();
$related_products = [];
while ($row = $related_result->fetch_assoc()) {
    $related_products[] = $row;
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
                    <a href="produktet.php?kategoria=<?= $produkti['id_kategoria'] ?>" style="color: #6c757d; text-decoration: none;"><?= htmlspecialchars($produkti['kategoria_emri']) ?></a>
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
                     x
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
        <div class="related-products mt-5">
            <h3 class="mb-4">Produkte të ngjashme</h3>
            <div class="row">
                <?php foreach ($related_products as $related): ?>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card h-100 product-card">
                            <div class="product-image-container">
                                <?php if (!empty($related['foto'])): ?>
                                    <img src="assets/images/<?= htmlspecialchars($related['foto']) ?>" 
                                         class="card-img-top" alt="<?= htmlspecialchars($related['emri']) ?>">
                                <?php else: ?>
                                    <div class="placeholder-image">
                                        <i class="fas fa-image"></i>
                                        <span>Pa imazh</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-body d-flex flex-column">
                                <h6 class="card-title"><?= htmlspecialchars($related['emri']) ?></h6>
                                <p class="card-text text-muted small"><?= htmlspecialchars($related['kategoria_emri']) ?></p>
                                <div class="mt-auto">
                                    <div class="price-section mb-2">
                                        <span class="price">€<?= number_format($related['cmimi'], 2) ?></span>
                                    </div>
                                    <a href="produkt.php?id=<?= $related['id_produkti'] ?>" 
                                       class="btn btn-outline-primary btn-sm w-100">
                                        Shiko detajet
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.product-image-main {
    position: relative;
    text-align: center;
}

.product-image-main img {
    width: 100%;
    max-height: 500px;
    object-fit: cover;
}

.placeholder-image-large {
    width: 100%;
    height: 400px;
    background: #f8f9fa;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
}

.placeholder-image-large i {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.out-of-stock-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    font-weight: bold;
}

.product-title {
    color: #333;
    margin-bottom: 1rem;
}

.product-price .current-price {
    font-size: 2rem;
    font-weight: bold;
    color: #28a745;
}

.quantity-selector .input-group button {
    width: 40px;
}

.product-info .info-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
    color: #6c757d;
}

.product-info .info-item i {
    margin-right: 0.5rem;
    width: 20px;
    color: #28a745;
}

.product-card {
    transition: transform 0.2s, box-shadow 0.2s;
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.product-image-container {
    height: 150px;
    overflow: hidden;
}

.product-image-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.placeholder-image {
    width: 100%;
    height: 100%;
    background: #f8f9fa;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #6c757d;
}

.placeholder-image i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.price {
    font-size: 1rem;
    font-weight: bold;
    color: #28a745;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quantity controls
    const quantityInput = document.getElementById('quantity');
    const decreaseBtn = document.getElementById('decreaseBtn');
    const increaseBtn = document.getElementById('increaseBtn');
    const maxQuantity = <?= $produkti['sasia'] ?>;

    if (decreaseBtn && increaseBtn && quantityInput) {
        decreaseBtn.addEventListener('click', function() {
            const currentValue = parseInt(quantityInput.value);
            if (currentValue > 1) {
                quantityInput.value = currentValue - 1;
            }
        });

        increaseBtn.addEventListener('click', function() {
            const currentValue = parseInt(quantityInput.value);
            if (currentValue < maxQuantity) {
                quantityInput.value = currentValue + 1;
            }
        });

        quantityInput.addEventListener('change', function() {
            const value = parseInt(this.value);
            if (value < 1) this.value = 1;
            if (value > maxQuantity) this.value = maxQuantity;
        });
    }

    // Add to cart functionality
    const addToCartBtn = document.querySelector('.add-to-cart');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productId = this.getAttribute('data-product-id');
            const quantity = quantityInput ? quantityInput.value : 1;
            const originalText = this.innerHTML;
            
            // Disable button and show loading
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Duke shtuar...';
            
            // Send AJAX request
            fetch('api/add-to-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId + '&quantity=' + quantity
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    this.innerHTML = '<i class="fas fa-check"></i> U shtua në shportë!';
                    this.classList.remove('btn-primary');
                    this.classList.add('btn-success');
                    
                    // Update cart count if element exists
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount && data.cart_count) {
                        cartCount.textContent = data.cart_count;
                    }
                    
                    // Reset button after 3 seconds
                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.classList.remove('btn-success');
                        this.classList.add('btn-primary');
                        this.disabled = false;
                    }, 3000);
                } else {
                    // Show error message
                    this.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Gabim!';
                    this.classList.remove('btn-primary');
                    this.classList.add('btn-danger');
                    
                    // Reset button after 3 seconds
                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.classList.remove('btn-danger');
                        this.classList.add('btn-primary');
                        this.disabled = false;
                    }, 3000);
                    
                    // Show alert with error message
                    alert(data.message || 'Ka ndodhur një gabim!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.innerHTML = originalText;
                this.disabled = false;
                alert('Ka ndodhur një gabim në rrjet!');
            });
        });
    }

    // Buy now functionality (redirect to cart)
    const buyNowBtn = document.getElementById('buyNow');
    if (buyNowBtn) {
        buyNowBtn.addEventListener('click', function() {
            // First add to cart, then redirect
            const addToCartBtn = document.querySelector('.add-to-cart');
            if (addToCartBtn) {
                addToCartBtn.click();
                // Redirect after a short delay
                setTimeout(() => {
                    window.location.href = 'cart.php';
                }, 1000);
            }
        });
    }
});
</script>

<!-- Related Products Section -->
<?php if (!empty($related_products)): ?>
    <section style="padding: 4rem 0; background: white;">
        <div class="container">
            <div style="text-align: center; margin-bottom: 3rem;">
                <h2 style="color: #4d4b49; font-size: 2.2rem; font-weight: 700; margin-bottom: 1rem;">Produkte të Ngjashme</h2>
                <p style="color: #6c757d; font-size: 1.1rem;">Zbuloni produkte të tjera nga kategoria <?= htmlspecialchars($produkti['kategoria_emri']) ?></p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem;">
                <?php foreach ($related_products as $related): ?>
                    <div style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: all 0.3s ease; border: 1px solid rgba(199, 167, 111, 0.1);"
                         onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 12px 40px rgba(0,0,0,0.15)'"
                         onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 20px rgba(0,0,0,0.08)'">
                        
                        <div style="height: 220px; background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); display: flex; align-items: center; justify-content: center; overflow: hidden;">
                            <?php if (!empty($related['foto'])): ?>
                                <img src="assets/images/<?= htmlspecialchars($related['foto']) ?>" 
                                     alt="<?= htmlspecialchars($related['emri']) ?>"
                                     style="width: 90%; height: 90%; object-fit: contain; object-position: center;">
                            <?php else: ?>
                                <div style="text-align: center; color: #6c757d;">
                                    <i class="fas fa-image" style="font-size: 2rem; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                                    <p style="margin: 0; font-size: 0.9rem;">Pa imazh</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div style="padding: 1.5rem;">
                            <h3 style="color: #4d4b49; font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem; line-height: 1.3;">
                                <?= htmlspecialchars($related['emri']) ?>
                            </h3>
                            <p style="color: #6c757d; font-size: 0.9rem; margin-bottom: 1rem;">
                                <?= htmlspecialchars($related['kategoria_emri']) ?>
                            </p>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                <span style="font-size: 1.3rem; font-weight: 700; color: #c7a76f;">€<?= number_format($related['cmimi'], 2) ?></span>
                            </div>
                            <a href="produkt.php?id=<?= $related['id_produkti'] ?>" 
                               style="display: block; width: 100%; padding: 0.75rem; background: linear-gradient(135deg, #c7a76f 0%, #4d4b49 100%); color: white; text-decoration: none; border-radius: 8px; text-align: center; font-weight: 600; transition: all 0.3s ease;"
                               onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 15px rgba(199, 167, 111, 0.4)'"
                               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                                <i class="fas fa-eye"></i> Shiko Detajet
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quantity controls
    const quantityInput = document.getElementById('quantity');
    const decreaseBtn = document.getElementById('decreaseBtn');
    const increaseBtn = document.getElementById('increaseBtn');

    if (decreaseBtn && increaseBtn && quantityInput) {
        decreaseBtn.addEventListener('click', function() {
            const currentValue = parseInt(quantityInput.value);
            if (currentValue > 1) {
                quantityInput.value = currentValue - 1;
            }
        });

        increaseBtn.addEventListener('click', function() {
            const currentValue = parseInt(quantityInput.value);
            if (currentValue < 10) {
                quantityInput.value = currentValue + 1;
            }
        });

        quantityInput.addEventListener('change', function() {
            const value = parseInt(this.value);
            if (value < 1) this.value = 1;
            if (value > 10) this.value = 10;
        });
    }

    // Add to cart functionality
    const addToCartBtn = document.querySelector('.add-to-cart');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productId = this.getAttribute('data-id');
            const quantity = quantityInput ? quantityInput.value : 1;
            const originalText = this.innerHTML;
            
            // Disable button and show loading
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Duke shtuar...';
            
            // Send AJAX request
            fetch('api/add-to-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId + '&quantity=' + quantity
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    this.innerHTML = '<i class="fas fa-check"></i> U shtua në shportë!';
                    this.style.background = 'linear-gradient(135deg, #28a745 0%, #20c997 100%)';
                    
                    // Update cart count if element exists
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount && data.cart_count) {
                        cartCount.textContent = data.cart_count;
                    }
                    
                    // Reset button after 3 seconds
                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.style.background = 'linear-gradient(135deg, #c7a76f 0%, #4d4b49 100%)';
                        this.disabled = false;
                    }, 3000);
                } else {
                    // Show error message
                    this.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Gabim!';
                    this.style.background = 'linear-gradient(135deg, #dc3545 0%, #c82333 100%)';
                    
                    // Reset button after 3 seconds
                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.style.background = 'linear-gradient(135deg, #c7a76f 0%, #4d4b49 100%)';
                        this.disabled = false;
                    }, 3000);
                    
                    // Show alert with error message
                    alert(data.message || 'Ka ndodhur një gabim!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.innerHTML = originalText;
                this.style.background = 'linear-gradient(135deg, #c7a76f 0%, #4d4b49 100%)';
                this.disabled = false;
                alert('Ka ndodhur një gabim në rrjet!');
            });
        });
    }

    // Hover effects for buttons
    const buttons = document.querySelectorAll('button[style*="background: linear-gradient"]');
    buttons.forEach(button => {
        const originalBg = button.style.background;
        
        button.addEventListener('mouseenter', function() {
            if (!this.disabled) {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 8px 25px rgba(199, 167, 111, 0.4)';
            }
        });
        
        button.addEventListener('mouseleave', function() {
            if (!this.disabled) {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            }
        });
    });
});
</script>

<style>
/* Mobile responsive styles */
@media (max-width: 768px) {
    .container {
        padding: 0 15px;
    }
    
    /* Product details mobile */
    div[style*="grid-template-columns: 1fr 1fr"] {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 2rem !important;
    }
    
    /* Hero section mobile */
    h1[style*="font-size: 2.5rem"] {
        font-size: 2rem !important;
    }
    
    /* Product image mobile */
    div[style*="position: sticky"] {
        position: static !important;
    }
    
    /* Product actions mobile */
    div[style*="display: flex; gap: 1rem"] {
        flex-direction: column !important;
    }
    
    button[style*="flex: 2"] {
        flex: 1 !important;
    }
    
    /* Related products mobile */
    div[style*="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr))"] {
        grid-template-columns: 1fr !important;
        gap: 1.5rem !important;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
</body>
</html>
