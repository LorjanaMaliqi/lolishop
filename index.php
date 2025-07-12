<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Merrni produktet nga databaza
$sql = "SELECT p.*, k.emri as kategoria FROM produktet p 
        LEFT JOIN kategorite k ON p.Id_kategoria = k.Id_kategoria 
        ORDER BY p.Id_produkti DESC LIMIT 8";
$result = $conn->query($sql);

// Debug - për të parë nëse ka gabime
if (!$result) {
    die("Error in SQL: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loli Shop - Dyqani Juaj Online</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="container">
                <div class="hero-content">
                    <h1>Mirë se erdhe në Loli Shop</h1>
                    <p>Zbuloni produktet më të mira me çmime të favorshme</p>
                    <a href="#products" class="btn btn-primary">Shikoni Produktet</a>
                </div>
            </div>
        </section>

        <!-- Products Section -->
        <section id="products" class="products-section">
            <div class="container">
                <h2>Produktet e Fundit</h2>
                <div class="products-grid">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($product = $result->fetch_assoc()): ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <?php if ($product['foto']): ?>
                                        <img src="assets/images/<?php echo htmlspecialchars($product['foto']); ?>" alt="<?php echo htmlspecialchars($product['emri']); ?>">
                                    <?php else: ?>
                                        <div class="placeholder-image">
                                            <i class="fas fa-image" style="font-size: 2.5rem; opacity: 0.8; z-index: 2; position: relative; margin-bottom: 0.5rem;"></i>
                                            <span style="font-size: 0.9rem; z-index: 2; position: relative; font-weight: 600; text-shadow: 0 1px 2px rgba(0,0,0,0.1);"><?php echo htmlspecialchars($product['emri']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="product-info">
                                    <h3><?php echo htmlspecialchars($product['emri'] ?? ''); ?></h3>
                                    <?php if (isset($product['kategoria']) && $product['kategoria']): ?>
                                        <p class="category"><?php echo htmlspecialchars($product['kategoria']); ?></p>
                                    <?php endif; ?>
                                    <p class="price">€<?php echo number_format($product['cmimi'] ?? 0, 2); ?></p>
                                    <p class="description"><?php echo htmlspecialchars(substr($product['pershkrimi'] ?? '', 0, 100)); ?><?php if(strlen($product['pershkrimi'] ?? '') > 100) echo '...'; ?></p>
                                    <div class="product-actions">
                                        <a href="produkt.php?id=<?php echo intval($product['Id_produkti']); ?>" class="btn btn-secondary">Detajet</a>
                                        <button class="btn btn-primary add-to-cart" data-id="<?php echo intval($product['Id_produkti']); ?>">
                                            <i class="fas fa-shopping-cart"></i> Shto në Shportë
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-products">
                            <i class="fas fa-box-open" style="font-size: 3rem; color: #6c757d; margin-bottom: 1rem;"></i>
                            <p>Nuk ka produkte të disponueshme momentalisht.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/script.js"></script>
</body>
</html>
