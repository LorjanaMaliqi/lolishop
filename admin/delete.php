<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$success_message = '';
$error_message = '';

// Handle delete requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // DELETE PRODUCT
    if (isset($_POST['delete_product'])) {
        $product_id = intval($_POST['product_id']);
        
        try {
            // Get product info first (for image deletion)
            $sql = "SELECT foto FROM produktet WHERE Id_produkti = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();
            
            if ($product) {
                // Delete from database
                $delete_sql = "DELETE FROM produktet WHERE Id_produkti = ?";
                $delete_stmt = $conn->prepare($delete_sql);
                $delete_stmt->bind_param("i", $product_id);
                
                if ($delete_stmt->execute()) {
                    // Try to delete image file
                    if (!empty($product['foto'])) {
                        $image_path = '../assets/images/' . $product['foto'];
                        if (file_exists($image_path)) {
                            @unlink($image_path);
                        }
                    }
                    $success_message = "Produkti u fshi me sukses!";
                } else {
                    $error_message = "Gabim në fshirjen e produktit!";
                }
            } else {
                $error_message = "Produkti nuk u gjet!";
            }
            
        } catch (Exception $e) {
            $error_message = "Gabim në fshirjen e produktit: " . $e->getMessage();
        }
    }
    
    // DELETE CATEGORY
    if (isset($_POST['delete_category'])) {
        $category_id = intval($_POST['category_id']);
        
        try {
            // Check if category has products
            $check_sql = "SELECT COUNT(*) as count FROM produktet WHERE Id_kategoria = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $category_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $count = $result->fetch_assoc()['count'];
            
            if ($count > 0) {
                $error_message = "Nuk mund të fshini kategorinë! Ka $count produkte të lidhura me të.";
            } else {
                // Delete category
                $delete_sql = "DELETE FROM kategorite WHERE Id_kategoria = ?";
                $delete_stmt = $conn->prepare($delete_sql);
                $delete_stmt->bind_param("i", $category_id);
                
                if ($delete_stmt->execute()) {
                    $success_message = "Kategoria u fshi me sukses!";
                } else {
                    $error_message = "Gabim në fshirjen e kategorisë!";
                }
            }
            
        } catch (Exception $e) {
            $error_message = "Gabim në fshirjen e kategorisë: " . $e->getMessage();
        }
    }
}

// Get all products
$products_data = [];
try {
    $products_sql = "SELECT p.Id_produkti, p.emri, p.cmimi, p.foto, k.emri as kategoria 
                     FROM produktet p 
                     LEFT JOIN kategorite k ON p.Id_kategoria = k.Id_kategoria 
                     ORDER BY p.emri";
    $products_stmt = $conn->prepare($products_sql);
    $products_stmt->execute();
    $products_result = $products_stmt->get_result();
    $products_data = $products_result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $error_message = "Gabim në leximin e produkteve: " . $e->getMessage();
}

// Get all categories
$categories_data = [];
try {
    $categories_sql = "SELECT k.Id_kategoria, k.emri, COUNT(p.Id_produkti) as product_count 
                       FROM kategorite k 
                       LEFT JOIN produktet p ON k.Id_kategoria = p.Id_kategoria 
                       GROUP BY k.Id_kategoria, k.emri 
                       ORDER BY k.emri";
    $categories_stmt = $conn->prepare($categories_sql);
    $categories_stmt->execute();
    $categories_result = $categories_stmt->get_result();
    $categories_data = $categories_result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $error_message = "Gabim në leximin e kategorive: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fshirje - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
    /* Header Styles */
    .header {
        background: linear-gradient(135deg, #ffffff 0%, #fefcfa 100%);
        box-shadow: 0 2px 10px rgba(212,184,150,0.3);
        position: sticky;
        top: 0;
        z-index: 1000;
        padding: 0.5rem 0;
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
    }

    .nav {
        display: flex;
        gap: 2rem;
    }

    .nav a {
        color: #6b5b47;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    .nav a:hover {
        color: #d4b896;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .header-actions a:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(141, 110, 99, 0.4);
        background: linear-gradient(135deg, #6d4c41 0%, #8d6e63 100%) !important;
    }

    .header-actions a:nth-child(2):hover {
        box-shadow: 0 6px 20px rgba(141, 110, 99, 0.4);
        background: linear-gradient(135deg, #6d4c41 0%, #8d6e63 100%) !important;
    }

    .item-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 15px 45px rgba(212,184,150,0.35);
        border-color: #d4b896;
    }

    /* Mobile Responsive */
    @media (max-width: 968px) {
        .header-content {
            flex-direction: column;
            gap: 1rem;
        }
        
        .nav {
            order: 2;
            width: 100%;
            justify-content: center;
        }
        
        .header-actions {
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .header-actions a {
            padding: 0.6rem 1.2rem !important;
            font-size: 0.85rem !important;
        }
        
        h1 {
            font-size: 2rem !important;
        }
        
        div[style*="grid-template-columns"] {
            grid-template-columns: 1fr !important;
        }
        
        .item-card {
            flex-direction: column !important;
            text-align: center !important;
            padding: 1.5rem !important;
        }
        
        div[style*="padding-right: 1rem"] {
            padding-right: 0 !important;
            width: 100% !important;
        }
        
        div[style*="min-width: 100px"] {
            width: 100% !important;
            margin-top: 1rem !important;
        }
        
        button[style*="text-transform: uppercase"] {
            width: 100% !important;
        }
    }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="../index.php" style="color: #c7a76f; text-decoration: none; font-size: 1.5rem; font-weight: bold;">
                        <i class="fas fa-store" style="color: #c7a76f; margin-right: 0.3rem;"></i> Loli Shop
                    </a>
                </div>
                
                <nav class="nav">
                    <a href="../index.php">Ballina</a>
                    <a href="../produktet.php">Produktet</a>
                    <a href="../kategorite.php">Kategorite</a>
                    <a href="../contact.php">Kontakti</a>
                </nav>
                
                <div class="header-actions">
                    <a href="index.php" style="color: white !important; text-decoration: none; font-weight: 600; padding: 0.8rem 1.5rem; border-radius: 25px; background: linear-gradient(135deg, #8d6e63 0%, #a1887f 100%); transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(141, 110, 99, 0.3); margin-right: 0.5rem; font-size: 0.95rem;">
                        <i class="fas fa-arrow-left" style="margin-right: 0.5rem;"></i> Admin Panel
                    </a>
                    <a href="../logout.php" style="color: white !important; text-decoration: none; font-weight: 600; padding: 0.8rem 1.5rem; border-radius: 25px; background: linear-gradient(135deg, #8d6e63 0%, #a1887f 100%); transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(141, 110, 99, 0.3); font-size: 0.95rem;">
                        <i class="fas fa-sign-out-alt" style="margin-right: 0.5rem;"></i> Dalje
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main style="background: linear-gradient(135deg, #f5f0e8 0%, #faf7f2 100%); padding: 1rem 0; min-height: calc(100vh - 200px);">
        <div class="container">
            <div style="max-width: 1200px; margin: 0 auto;">    
                <!-- Page Title -->
                <div style="text-align: center; margin-bottom: 2rem;">
                    <h1 style="color: #6b5b47; font-size: 2.5rem; margin-bottom: 0.5rem;">
                        <i class="fas fa-trash-alt" style="color: #ef5350; margin-right: 0.5rem;"></i>
                        Fshirje
                    </h1>
                
                </div>

                <!-- Success/Error Messages -->
                <?php if ($success_message): ?>
                    <div class="alert alert-success" role="alert" style="margin-bottom: 1.5rem;">
                        <i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i>
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                <?php if ($error_message): ?>
                    <div class="alert alert-danger" role="alert" style="margin-bottom: 1.5rem;">
                        <i class="fas fa-exclamation-circle" style="margin-right: 0.5rem;"></i>
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Search Section -->
                <div style="text-align: center; margin-bottom: 3rem;">

                    
                    <!-- Search Bar -->
                    <div style="max-width: 500px; margin: 0 auto 2.5rem; position: relative; display: flex; align-items: center; background: linear-gradient(135deg, #f8f8f8 0%, #ffffff 100%); border-radius: 40px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); border: 2px solid #e8e8e8; transition: all 0.4s ease; overflow: hidden; padding: 0.3rem;">
                        <input type="text" id="searchInput" placeholder="Kërkoni produkt ose kategori..." onkeyup="searchItems()" style="width: 100%; padding: 1rem 1.5rem; border: none; border-radius: 35px; font-size: 1rem; background: transparent; color: #4a4a4a; font-weight: 400; letter-spacing: 0.3px;">
                    </div>
                </div>

                <!-- Products Section -->
                <div class="section" style="margin-bottom: 3rem;" id="products-section">
                    <h2 style="color: #6b5b47; font-size: 2rem; margin-bottom: 1rem;">
                        <i class="" style="color: #ef5350; margin-right: 0.5rem;"></i>
                        Produktet
                    </h2>
                    
                    <div class="table-responsive">
                        <table class="table" style="width: 100%; border-collapse: collapse; margin-bottom: 2rem;">
                            <thead>
                                <tr style="background-color: #f7f7f7; color: #333;">
                                    <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #ddd;">Emri</th>
                                    <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #ddd;">Cmimi</th>
                                    <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #ddd;">Kategoria</th>
                                    <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #ddd;">Foto</th>
                                    <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #ddd;">Veprime</th>
                                </tr>
                            </thead>
                            <tbody id="products-tbody">
                                <?php foreach ($products_data as $product): ?>
                                    <tr class="product-row" data-name="<?php echo strtolower(htmlspecialchars($product['emri'])); ?>" style="background-color: #fff; border-bottom: 1px solid #ddd;">
                                        <td style="padding: 1rem;"><?php echo htmlspecialchars($product['emri']); ?></td>
                                        <td style="padding: 1rem;">€<?php echo number_format($product['cmimi'], 2); ?></td>
                                        <td style="padding: 1rem;"><?php echo htmlspecialchars($product['kategoria'] ?? 'Pa kategori'); ?></td>
                                        <td style="padding: 1rem;">
                                            <?php if (!empty($product['foto'])): ?>
                                                <img src="../assets/images/<?php echo htmlspecialchars($product['foto']); ?>" alt="<?php echo htmlspecialchars($product['emri']); ?>" style="max-width: 100px; max-height: 100px; object-fit: cover; border-radius: 8px;">
                                            <?php else: ?>
                                                <span style="color: #888; font-style: italic;">Nuk ka foto</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 1rem;">
                                            <form method="POST" style="display: inline-block;">
                                                <input type="hidden" name="product_id" value="<?php echo $product['Id_produkti']; ?>">
                                                <button type="submit" name="delete_product" onclick="return confirm('A jeni të sigurt se doni ta fshini këtë produkt: <?php echo htmlspecialchars($product['emri']); ?>?');" style="background: linear-gradient(135deg, #ef5350 0%, #e57373 100%); border: none; color: white; cursor: pointer; padding: 0.5rem 1rem; border-radius: 8px; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(239, 83, 80, 0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(239, 83, 80, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(239, 83, 80, 0.3)';">
                                                    <i class="fas fa-trash-alt" style="margin-right: 0.3rem;"></i> Fshij
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <!-- No products message -->
                        <div id="no-products" style="display: none; text-align: center; padding: 2rem; color: #8a7a68; background: #f8f8f8; border-radius: 10px; margin: 1rem 0;">
                            <i class="fas fa-box-open" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>Nuk u gjetën produkte për kërkimin tuaj.</p>
                        </div>
                    </div>
                </div>

                <!-- Categories Section -->
                <div class="section" id="categories-section">
                    <h2 style="color: #6b5b47; font-size: 2rem; margin-bottom: 1rem;">
                        <i class="" style="color: rgba(248, 247, 244, 1); margin-right: 0.5rem;"></i>
                        Kategorite
                    </h2>
                    
                    <div class="table-responsive">
                        <table class="table" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background-color: #f7f7f7; color: #333;">
                                    <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #ddd;">Emri</th>
                                    <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #ddd;">Produkte</th>
                                    <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #ddd;">Veprime</th>
                                </tr>
                            </thead>
                            <tbody id="categories-tbody">
                                <?php foreach ($categories_data as $category): ?>
                                    <tr class="category-row" data-name="<?php echo strtolower(htmlspecialchars($category['emri'])); ?>" style="background-color: #fff; border-bottom: 1px solid #ddd;">
                                        <td style="padding: 1rem;"><?php echo htmlspecialchars($category['emri']); ?></td>
                                        <td style="padding: 1rem;">
                                            <span style="color: #333; font-size: 0.9rem; font-weight: 600;"><?php echo $category['product_count']; ?></span>
                                        </td>
                                        <td style="padding: 1rem;">
                                            <form method="POST" style="display: inline-block;">
                                                <input type="hidden" name="category_id" value="<?php echo $category['Id_kategoria']; ?>">
                                                <button type="submit" name="delete_category" onclick="return confirm('A jeni te sigurt se doni ta fshini kete kategori?');" style="background: linear-gradient(135deg, #ef5350 0%, #e57373 100%); border: none; color: white; cursor: pointer; padding: 0.5rem 1rem; border-radius: 8px; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(239, 83, 80, 0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(239, 83, 80, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(239, 83, 80, 0.3)';">
                                                    <i class="fas fa-trash-alt" style="margin-right: 0.3rem;"></i> Fshij
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Back Button -->
                <div style="text-align: center; margin-top: 3rem;">
                    <a href="index.php" style="display: inline-block; background: linear-gradient(135deg, #8d6e63 0%, #a1887f 100%); color: white; padding: 1rem 2rem; border-radius: 30px; text-decoration: none; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(141, 110, 99, 0.3);">
                        <i class="fas fa-arrow-left" style="margin-right: 0.5rem;"></i>
                        Kthehu në Admin Panel
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer style="background: #4d4b49; color: white; text-align: center; padding: 2rem 0; margin-top: 0;">
        <div class="container">
            <p>&copy; 2025 Loli Shop. Të gjitha të drejtat e rezervuara.</p>
        </div>
    </footer>
    <script src="../assets/js/script.js"></script>
    <script>
    function searchItems() {
        const query = document.getElementById('searchInput').value.toLowerCase();
        
        // Search products
        const productRows = document.querySelectorAll('.product-row');
        let visibleProducts = 0;
        
        productRows.forEach(row => {
            const name = row.getAttribute('data-name');
            if (name && name.includes(query)) {
                row.style.display = 'table-row';
                visibleProducts++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Show/hide no products message
        const noProductsMsg = document.getElementById('no-products');
        if (visibleProducts === 0 && query !== '') {
            noProductsMsg.style.display = 'block';
        } else {
            noProductsMsg.style.display = 'none';
        }
        
        // Search categories
        const categoryRows = document.querySelectorAll('.category-row');
        let visibleCategories = 0;
        
        categoryRows.forEach(row => {
            const name = row.getAttribute('data-name');
            if (name && name.includes(query)) {
                row.style.display = 'table-row';
                visibleCategories++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Show/hide sections based on results
        const productsSection = document.getElementById('products-section');
        const categoriesSection = document.getElementById('categories-section');
        
        if (query !== '') {
            // Hide sections with no results
            if (visibleProducts === 0) {
                productsSection.style.display = 'none';
            } else {
                productsSection.style.display = 'block';
            }
            
            if (visibleCategories === 0) {
                categoriesSection.style.display = 'none';
            } else {
                categoriesSection.style.display = 'block';
            }
        } else {
            // Show all sections when search is empty
            productsSection.style.display = 'block';
            categoriesSection.style.display = 'block';
        }
    }

    // Add search bar focus/blur effects
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        
        searchInput.addEventListener('focus', function() {
            this.parentElement.style.borderColor = '#ef5350';
            this.parentElement.style.boxShadow = '0 12px 35px rgba(93, 173, 226, 0.2)';
            this.parentElement.style.transform = 'translateY(-3px)';
            this.parentElement.style.background = 'linear-gradient(135deg, #ffffff 0%, #f8faff 100%)';
        });
        
        searchInput.addEventListener('blur', function() {
            this.parentElement.style.borderColor = '#e8e8e8';
            this.parentElement.style.boxShadow = '0 8px 25px rgba(0,0,0,0.08)';
            this.parentElement.style.transform = 'translateY(0)';
            this.parentElement.style.background = 'linear-gradient(135deg, #f8f8f8 0%, #ffffff 100%)';
        });
    });
    </script>
</body>
</html>