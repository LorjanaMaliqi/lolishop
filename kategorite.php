<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get all categories with product count
$sql = "SELECT k.*, COUNT(p.Id_produkti) as total_produktet 
        FROM kategorite k 
        LEFT JOIN produktet p ON k.Id_kategoria = p.Id_kategoria 
        GROUP BY k.Id_kategoria 
        ORDER BY k.emri";
$result = $conn->query($sql);

$kategorite = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $kategorite[] = $row;
    }
}

// Get featured products for each category (3 products per category)
$featured_products = [];
foreach ($kategorite as $kategoria) {
    $featured_sql = "SELECT * FROM produktet 
                     WHERE Id_kategoria = ? 
                     ORDER BY Id_produkti DESC 
                     LIMIT 3";
    $stmt = $conn->prepare($featured_sql);
    $stmt->bind_param("i", $kategoria['Id_kategoria']);
    $stmt->execute();
    $featured_result = $stmt->get_result();
    
    $featured_products[$kategoria['Id_kategoria']] = [];
    while ($product = $featured_result->fetch_assoc()) {
        $featured_products[$kategoria['Id_kategoria']][] = $product;
    }
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategorite - Loli Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <!-- Hero Section for Categories -->
        <section style="background: linear-gradient(135deg, #c7a76f 0%, #4d4b49 100%); color: white; padding: 4rem 0; text-align: center;">
            <div class="container">
                <h1 style="font-size: 3rem; margin-bottom: 1rem; font-weight: 700;">Kategorite</h1>
                <p style="font-size: 1.2rem; opacity: 0.9;">Zbuloni produktet sipas kategorive</p>
            </div>
        </section>

        <!-- Categories Section -->
        <section style="padding: 4rem 0; background: linear-gradient(135deg, #e0d1b6 0%, #f0e8d8 100%); min-height: 100vh;">
            <div class="container">
                <?php if (empty($kategorite)): ?>
                    <div class="no-categories" style="text-align: center; padding: 4rem 2rem;">
                        <div style="background: white; padding: 3rem; border-radius: 20px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); max-width: 500px; margin: 0 auto;">
                            <i class="fas fa-folder-open" style="font-size: 4rem; color: #4d4b49; margin-bottom: 1.5rem;"></i>
                            <h3 style="color: #4d4b49; margin-bottom: 1rem; font-size: 1.5rem;">Nuk ka kategori</h3>
                            <p style="color: #4d4b49; font-size: 1.1rem;">Aktualisht nuk ka kategori të disponueshme.</p>
                        </div>
                    </div>
                <?php else: ?>                    <div class="categories-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2.5rem;">
                        <?php foreach ($kategorite as $kategoria): ?>
                            <div class="category-card" style="background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 8px 32px rgba(0,0,0,0.1); transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); border: 1px solid rgba(199, 167, 111, 0.1); position: relative;">
                                <!-- Category Header -->
                                <div class="category-header" style="background: linear-gradient(135deg, #c7a76f 0%, #4d4b49 100%); color: white; padding: 2rem; text-align: center; position: relative; overflow: hidden;">
                                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(45deg, rgba(255,255,255,0.1) 25%, transparent 25%, transparent 75%, rgba(255,255,255,0.1) 75%), linear-gradient(45deg, rgba(255,255,255,0.1) 25%, transparent 25%, transparent 75%, rgba(255,255,255,0.1) 75%); background-size: 30px 30px; background-position: 0 0, 15px 15px; opacity: 0.3;"></div>
                                    <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem; z-index: 2; position: relative;">
                                        <?= htmlspecialchars($kategoria['emri']) ?>
                                    </h3>
                                    <p style="opacity: 0.9; z-index: 2; position: relative; font-size: 1rem; margin-bottom: 0;"><?= $kategoria['total_produktet'] ?> produkte</p>
                                </div>

                                <!-- Category Content -->
                                <div class="category-content" style="padding: 2rem;">
                                    <?php if (!empty($kategoria['pershkrimi'])): ?>
                                        <p style="color: #4d4b49; margin-bottom: 1.5rem; line-height: 1.6;"><?= htmlspecialchars($kategoria['pershkrimi']) ?></p>
                                    <?php endif; ?>

                                    <!-- Featured Products Preview -->
                                    <?php if (!empty($featured_products[$kategoria['Id_kategoria']])): ?>
                                        <div class="featured-products" style="margin-bottom: 1.5rem;">
                                            <h4 style="color: #4d4b49; margin-bottom: 1rem; font-size: 1.1rem; font-weight: 600;">Produktet e Fundit:</h4>
                                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem;">
                                                <?php foreach ($featured_products[$kategoria['Id_kategoria']] as $product): ?>
                                                    <div style="background: #f8f9fa; border-radius: 8px; padding: 0.8rem; text-align: center; border: 2px solid transparent; transition: all 0.3s ease;">
                                                        <div style="background: linear-gradient(135deg, #c7a76f 0%, #4d4b49 100%); height: 60px; border-radius: 6px; display: flex; align-items: center; justify-content: center; margin-bottom: 0.5rem; color: white;">
                                                            <i class="fas fa-box" style="font-size: 1.2rem;"></i>
                                                        </div>
                                                        <p style="font-size: 0.8rem; color: #4d4b49; font-weight: 600; margin-bottom: 0.3rem; line-height: 1.2;"><?= htmlspecialchars(substr($product['emri'], 0, 15)) ?>...</p>
                                                        <p style="font-size: 0.8rem; color: #c7a76f; font-weight: 700;">€<?= number_format($product['cmimi'], 2) ?></p>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Action Buttons -->
                                    <div class="category-actions" style="display: flex; gap: 0.8rem;">
                                        <a href="produktet.php?kategoria=<?= $kategoria['Id_kategoria'] ?>" 
                                           class="btn btn-primary" 
                                           style="flex: 1; text-decoration: none; background: linear-gradient(135deg, #c7a76f 0%, #4d4b49 100%); color: white; padding: 0.9rem 1.2rem; border-radius: 10px; text-align: center; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.3s ease; border: none;">
                                            <i class="fas fa-eye"></i> Shiko Produktet
                                        </a>
                                        <?php if ($kategoria['total_produktet'] > 0): ?>
                                            <a href="produktet.php?kategoria=<?= $kategoria['Id_kategoria'] ?>&sort=price_low" 
                                               class="btn btn-secondary" 
                                               style="background: linear-gradient(135deg, #c7a76f 0%, #4d4b49 100%); color: white; padding: 0.9rem; border-radius: 10px; text-decoration: none; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; border: none;">
                                                <i class="fas fa-shopping-cart"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Stats Card si pjesë e grid-it -->
                        <div class="stats-card" style="background: rgba(255, 255, 255, 0.95); border-radius: 15px; overflow: hidden; box-shadow: 0 8px 32px rgba(0,0,0,0.1); transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); border: 1px solid rgba(199, 167, 111, 0.2); position: relative; grid-column: span 3; min-width: 700px; max-width: 1200px; min-height: 120px; max-height: 160px;">
                            <!-- Stats Content -->
                            <div style="padding: 2rem; height: 100%; display: flex; flex-direction: row; justify-content: space-around; align-items: center;">
                                <div style="display: flex; flex-direction: row; gap: 3rem; align-items: center; justify-content: space-around; width: 100%;">
                                    <div style="text-align: center;">
                                        <h3 style="color: #c7a76f; font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem; line-height: 1;"><?= count($kategorite) ?></h3>
                                        <p style="color: #4d4b49; font-weight: 600; font-size: 1rem; margin: 0; line-height: 1.2;">Kategori Aktive</p>
                                    </div>
                                    <div style="width: 3px; height: 60px; background: linear-gradient(180deg, #c7a76f 0%, #4d4b49 100%); border-radius: 2px;"></div>
                                    <div style="text-align: center;">
                                        <h3 style="color: #c7a76f; font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem; line-height: 1;"><?= array_sum(array_column($kategorite, 'total_produktet')) ?></h3>
                                        <p style="color: #4d4b49; font-weight: 600; font-size: 1rem; margin: 0; line-height: 1.2;">Produkte Gjithsej</p>
                                    </div>
                                    <div style="width: 3px; height: 60px; background: linear-gradient(180deg, #c7a76f 0%, #4d4b49 100%); border-radius: 2px;"></div>
                                    <div style="text-align: center;">
                                        <h3 style="color: #c7a76f; font-size: 2.2rem; font-weight: 700; margin-bottom: 0.5rem; line-height: 1;"><?= count($kategorite) > 0 ? round(array_sum(array_column($kategorite, 'total_produktet')) / count($kategorite)) : 0 ?></h3>
                                        <p style="color: #4d4b49; font-weight: 600; font-size: 0.9rem; margin: 0; line-height: 1.2;">Mesatarisht për Kategori</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add hover effects to category cards
        const categoryCards = document.querySelectorAll('.category-card');
        
        categoryCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-12px) scale(1.02)';
                this.style.boxShadow = '0 20px 60px rgba(0,0,0,0.15)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
                this.style.boxShadow = '0 8px 32px rgba(0,0,0,0.1)';
            });
        });

        // Add hover effects to featured product previews
        const productPreviews = document.querySelectorAll('.featured-products > div > div');
        
        productPreviews.forEach(preview => {
            preview.addEventListener('mouseenter', function() {
                this.style.borderColor = '#e74c3c';
                this.style.transform = 'scale(1.05)';
                this.style.background = 'white';
            });
            
            preview.addEventListener('mouseleave', function() {
                this.style.borderColor = 'transparent';
                this.style.transform = 'scale(1)';
                this.style.background = '#f8f9fa';
            });
        });

        // Add button hover effects
        const buttons = document.querySelectorAll('.btn');
        
        buttons.forEach(button => {
            button.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                if (this.classList.contains('btn-primary') || this.classList.contains('btn-secondary')) {
                    this.style.boxShadow = '0 8px 25px rgba(199, 167, 111, 0.4)';
                }
            });
            
            button.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        });
    });
    </script>

    <style>
    /* Additional styles for categories page */
    @media (max-width: 768px) {
        .container {
            padding: 0 15px;
        }
        
        .categories-grid {
            grid-template-columns: 1fr !important;
            gap: 2rem !important;
        }
        
        .category-card {
            max-width: 400px;
            margin: 0 auto;
        }
        
        .category-actions {
            flex-direction: column !important;
        }
        
        .featured-products > div {
            grid-template-columns: 1fr !important;
            gap: 1rem !important;
        }
        
        section[style*="padding: 4rem 0"] h1 {
            font-size: 2.2rem !important;
        }
        
        div[style*="display: flex; gap: 3rem"] {
            flex-direction: column !important;
            gap: 1.5rem !important;
        }
        
        div[style*="width: 2px; height: 40px"] {
            width: 40px !important;
            height: 2px !important;
        }
    }
    
    @media (max-width: 480px) {
        .category-header {
            padding: 1.5rem !important;
        }
        
        .category-content {
            padding: 1.5rem !important;
        }
    }
    </style>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
