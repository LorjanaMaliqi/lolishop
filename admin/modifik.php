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
$product = null;
$category = null;
$edit_type = '';

// Determine what we're editing
if (isset($_GET['edit_product']) && is_numeric($_GET['edit_product'])) {
    $edit_type = 'product';
    $product_id = intval($_GET['edit_product']);
    
    // Get product data
    try {
        $sql = "SELECT * FROM produktet WHERE Id_produkti = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        
        if (!$product) {
            $error_message = "Produkti nuk u gjet!";
        }
    } catch (Exception $e) {
        $error_message = "Gabim në leximin e produktit: " . $e->getMessage();
    }
    
} elseif (isset($_GET['edit_category']) && is_numeric($_GET['edit_category'])) {
    $edit_type = 'category';
    $category_id = intval($_GET['edit_category']);
    
    // Get category data
    try {
        $sql = "SELECT * FROM kategorite WHERE Id_kategoria = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $category = $result->fetch_assoc();
        
        if (!$category) {
            $error_message = "Kategoria nuk u gjet!";
        }
    } catch (Exception $e) {
        $error_message = "Gabim në leximin e kategorisë: " . $e->getMessage();
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // UPDATE PRODUCT
    if (isset($_POST['update_product'])) {
        $product_id = intval($_POST['product_id']);
        
        // Get current product data first
        $sql = "SELECT * FROM produktet WHERE Id_produkti = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $current_product = $result->fetch_assoc();
        
        // Use current values as defaults, then update only if new values provided
        $emri = !empty(trim($_POST['emri'])) ? trim($_POST['emri']) : $current_product['emri'];
        $pershkrimi = !empty(trim($_POST['pershkrimi'])) ? trim($_POST['pershkrimi']) : $current_product['pershkrimi'];
        $cmimi = !empty($_POST['cmimi']) && $_POST['cmimi'] > 0 ? floatval($_POST['cmimi']) : $current_product['cmimi'];
        $kategoria_id = !empty($_POST['kategoria_id']) ? intval($_POST['kategoria_id']) : $current_product['Id_kategoria'];
        
        // Handle image upload - keep current if no new image
        $foto = $current_product['foto']; // Default to current photo
        
        // Check if user selected existing image
        if (isset($_POST['existing_foto']) && !empty($_POST['existing_foto'])) {
            $foto = $_POST['existing_foto'];
            $success_message = "Foto e zgjedhur: " . $foto;
        }
        // Or handle new image upload
        else if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $upload_dir = '../assets/images/';
            
            // Check file type
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['foto']['type'];
            
            if (!in_array($file_type, $allowed_types)) {
                $error_message = "Lloji i fotosë nuk lejohet! Përdorni: JPG, PNG, GIF, WEBP";
            } else {
                // Check file size (max 5MB)
                if ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
                    $error_message = "Foto është shumë e madhe! Maksimumi 5MB";
                } else {
                    $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                    $foto = 'product_' . time() . '_' . rand(1000, 9999) . '.' . strtolower($file_extension);
                    $upload_path = $upload_dir . $foto;
                    
                    if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                        $success_message = "Foto e re u ngarkua me sukses!";
                    } else {
                        $error_message = "Gabim në ngarkimin e fotosë!";
                        $foto = $current_product['foto']; // Keep current photo on error
                    }
                }
            }
        }
        
        if (empty($error_message)) {
            try {
                $sql = "UPDATE produktet SET emri = ?, pershkrimi = ?, cmimi = ?, foto = ?, Id_kategoria = ? WHERE Id_produkti = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssdsii", $emri, $pershkrimi, $cmimi, $foto, $kategoria_id, $product_id);
                
                if ($stmt->execute()) {
                    $success_message = "Produkti u përditësua me sukses!";
                    
                    // Refresh product data
                    $sql = "SELECT * FROM produktet WHERE Id_produkti = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $product_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $product = $result->fetch_assoc();
                    
                } else {
                    $error_message = "Gabim në përditësimin e produktit!";
                }
                
            } catch (Exception $e) {
                $error_message = "Gabim në përditësimin e produktit: " . $e->getMessage();
            }
        }
    }
    
    // UPDATE CATEGORY
    if (isset($_POST['update_category'])) {
        $emri = trim($_POST['emri_kategori']);
        $category_id = intval($_POST['category_id']);
        
        if (empty($emri)) {
            $error_message = "Emri i kategorisë është i detyrueshëm!";
        } else {
            try {
                // Check if category already exists (excluding current one)
                $check_sql = "SELECT COUNT(*) as count FROM kategorite WHERE emri = ? AND Id_kategoria != ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("si", $emri, $category_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                $result = $check_result->fetch_assoc();
                
                if ($result['count'] > 0) {
                    $error_message = "Kategoria '{$emri}' ekziston tashmë!";
                } else {
                    // Update category
                    $sql = "UPDATE kategorite SET emri = ? WHERE Id_kategoria = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $emri, $category_id);
                    
                    if ($stmt->execute()) {
                        $success_message = "Kategoria u përditësua me sukses!";
                        
                        // Refresh category data
                        $sql = "SELECT * FROM kategorite WHERE Id_kategoria = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $category_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $category = $result->fetch_assoc();
                        
                    } else {
                        $error_message = "Gabim në përditësimin e kategorisë!";
                    }
                }
                
            } catch (Exception $e) {
                $error_message = "Gabim në përditësimin e kategorisë: " . $e->getMessage();
            }
        }
    }
}

// Get categories for product form
$kategorite = [];
try {
    $kategorite_sql = "SELECT Id_kategoria, emri FROM kategorite ORDER BY emri";
    
    // Kontrolloni nëse përdorni PDO apo MySQLi
    if ($conn instanceof PDO) {
        // PDO version
        $kategorite_stmt = $conn->prepare($kategorite_sql);
        $kategorite_stmt->execute();
        $kategorite = $kategorite_stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // MySQLi version
        $kategorite_stmt = $conn->prepare($kategorite_sql);
        $kategorite_stmt->execute();
        $result = $kategorite_stmt->get_result();
        $kategorite = $result->fetch_all(MYSQLI_ASSOC);
    }
} catch (Exception $e) {
    error_log("Gabim në leximin e kategorive: " . $e->getMessage());
}

// Get existing images from assets/images folder
$existing_images = [];
$image_dir = '../assets/images/';
if (is_dir($image_dir)) {
    $files = scandir($image_dir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $existing_images[] = $file;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifikim - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                    <a href="index.php" class="admin-btn">
                        <i class="fas fa-arrow-left"></i> Admin Panel
                    </a>
                    <a href="../logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Dalje
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
                        <i class="fas fa-edit" style="color: #5DADE2; margin-right: 0.5rem;"></i>
                        Modifikim
                    </h1>
                    <p style="color: #8a7a68; font-size: 1.1rem;"></p>
                </div>

                <!-- Success/Error Messages -->
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($edit_type == 'product' && $product): ?>
                <!-- EDIT PRODUCT FORM -->
                <div class="form-container">
                    <h2 style="color: #6b5b47; margin-bottom: 1.5rem; text-align: center;">
                        <i class="" style="color:  #5DADE2; margin-right: 0.5rem;"></i>
                        Modifikim Produkti: <?= htmlspecialchars($product['emri']) ?>
                    </h2>
                    
                    <form method="POST" enctype="multipart/form-data" class="edit-form">
                        <input type="hidden" name="product_id" value="<?= $product['Id_produkti'] ?>">
                        <input type="hidden" name="current_foto" value="<?= htmlspecialchars($product['foto']) ?>">
                        
                        <div class="form-group">
                            <label><i class="fas fa-tag"></i> Emri i Produktit</label>
                            <input type="text" name="emri" value="<?= htmlspecialchars($product['emri']) ?>" placeholder="Lër bosh për të mbajtur: <?= htmlspecialchars($product['emri']) ?>">
   
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-align-left"></i> Përshkrimi</label>
                            <textarea name="pershkrimi" rows="4" placeholder="Lër bosh për të mbajtur përshkrimin aktual..."><?= htmlspecialchars($product['pershkrimi']) ?></textarea>
                            <small style="color: #8a7a68; font-style: italic;"></small>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-euro-sign"></i> Çmimi</label>
                                <input type="number" name="cmimi" step="0.01" min="0" value="<?= $product['cmimi'] ?>" placeholder="Aktual: €<?= $product['cmimi'] ?>">
                                <small style="color: #8a7a68; font-style: italic;"></small>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-tags"></i> Kategoria</label>
                            <select name="kategoria_id">
                                <option value="">-- Mbaj kategorinë aktuale --</option>
                                <?php foreach ($kategorite as $kategoria): ?>
                                    <option value="<?= $kategoria['Id_kategoria'] ?>" <?= (isset($product['Id_kategoria']) && $product['Id_kategoria'] == $kategoria['Id_kategoria']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($kategoria['emri']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small style="color: #8a7a68; font-style: italic;">Zgjidh vetëm nëse doni të ndryshoni kategorinë</small>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-image"></i> Foto e Produktit</label>
                            
                            <!-- Current Photo Display -->
                            <div class="current-photo">
                                <p>Foto aktuale:</p>
                                <img src="../assets/images/<?= htmlspecialchars($product['foto']) ?>" alt="Foto aktuale">
                                <p class="filename"><?= htmlspecialchars($product['foto']) ?></p>
                            </div>
                            
                            <!-- Photo Selection Tabs -->
                            <div class="photo-tabs">
                                <button type="button" onclick="showExistingPhotos()" class="tab-btn existing-btn">
                                    <i class="fas fa-folder-open"></i> Përdor Foto Ekzistuese
                                </button>
                                <button type="button" onclick="showUploadNew()" class="tab-btn upload-btn">
                                    <i class="fas fa-upload"></i> Ngarko Foto të Re
                                </button>
                            </div>
                            
                            <!-- Existing Photos Selection -->
                            <div id="existing-photos" style="display: none;">
                                <select name="existing_foto">
                                    <option value="">Zgjidh foto nga galeria...</option>
                                    <?php foreach ($existing_images as $image): ?>
                                        <option value="<?= htmlspecialchars($image) ?>"><?= htmlspecialchars($image) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                
                                <!-- Preview existing photos -->
                                <div class="photo-gallery">
                                    <?php foreach ($existing_images as $image): ?>
                                        <div class="photo-item" onclick="selectExistingPhoto('<?= htmlspecialchars($image) ?>')">
                                            <img src="../assets/images/<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($image) ?>">
                                            <p><?= htmlspecialchars(substr($image, 0, 12)) ?>...</p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- Upload New Photo -->
                            <div id="upload-new" style="display: none;">
                                <input type="file" name="foto" accept="image/*">
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="update_product" class="btn btn-success">
                                <i class="fas fa-save"></i> Ruaj Ndryshimet
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Anulo
                            </a>
                        </div>
                    </form>
                </div>

                <?php elseif ($edit_type == 'category' && $category): ?>
                <!-- EDIT CATEGORY FORM -->
                <div class="form-container category-form">
                    <h2 style="color: #6b5b47; margin-bottom: 1.5rem; text-align: center;">
                        <i class="fas fa-tag" style="color:  #5DADE2; margin-right: 0.5rem;"></i>
                        Modifikim Kategorie: <?= htmlspecialchars($category['emri']) ?>
                    </h2>
                    
                    <form method="POST" class="edit-form">
                        <input type="hidden" name="category_id" value="<?= $category['Id_kategoria'] ?>">
                        
                        <div class="form-group">
                            <label><i class="fas fa-tag"></i> Emri i Kategorisë</label>
                            <input type="text" name="emri_kategori" required value="<?= htmlspecialchars($category['emri']) ?>" placeholder="Shkruaj emrin e kategorisë...">
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="update_category" class="btn btn-success">
                                <i class="fas fa-save"></i> Ruaj Ndryshimet
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Anulo
                            </a>
                        </div>
                    </form>
                </div>

                <?php else: ?>
                <!-- NO ITEM SELECTED -->
                <div class="no-selection">
                    <!-- Search Section -->
                    <div class="search-section">
                        <h2>Kërko dhe Modifiko</h2>
                   
                        <!-- Search Bar -->
                        <div class="search-bar">
                            <input type="text" id="searchInput" placeholder="Kërkoni produkt ose kategori..." onkeyup="searchItems()">
                        </div>
                    </div>
                    
                    <!-- PRODUCT LIST FOR EDITING -->
                    <div class="section">
                        <h3>Produktet për Modifikim</h3>
                        
                        <div class="items-grid" id="products-grid">
                        <?php
                        // Get products for editing
                        try {
                            $products_sql = "SELECT Id_produkti, emri, cmimi, foto FROM produktet ORDER BY emri";
                            $products_stmt = $conn->prepare($products_sql);
                            $products_stmt->execute();
                            $products_result = $products_stmt->get_result();
                            $products_data = $products_result->fetch_all(MYSQLI_ASSOC);

                            if (!empty($products_data)) {
                                foreach ($products_data as $produkt) {
                                    echo '<div class="item-card product-item" data-name="' . strtolower(htmlspecialchars($produkt['emri'])) . '">';
                                    echo '<div class="item-content">';
                                    
                                    // Product image
                                    if (!empty($produkt['foto'])) {
                                        echo '<img src="../assets/images/' . htmlspecialchars($produkt['foto']) . '" alt="' . htmlspecialchars($produkt['emri']) . '">';
                                    } else {
                                        echo '<div class="no-image"><i class="fas fa-image"></i></div>';
                                    }
                                    
                                    // Product info
                                    echo '<div class="item-info">';
                                    echo '<h4>' . htmlspecialchars($produkt['emri']) . '</h4>';
                                    echo '<p>€' . number_format($produkt['cmimi'], 2) . '</p>';
                                    echo '</div>';
                                    
                                    // Edit button
                                    echo '<a href="modifik.php?edit_product=' . $produkt['Id_produkti'] . '" class="edit-btn product-edit">';
                                    echo '<i class="fas fa-edit"></i> Modifiko';
                                    echo '</a>';
                                    
                                    echo '</div>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<p class="no-items">Nuk ka produkte të disponueshme.</p>';
                            }
                        } catch (Exception $e) {
                            echo '<p class="error-text">Gabim në leximin e produkteve: ' . htmlspecialchars($e->getMessage()) . '</p>';
                        }
                        ?>
                        </div>
                    </div>
                    
                    <!-- CATEGORY LIST FOR EDITING -->
                    <div class="section">
                        <h3>Kategorite për Modifikim</h3>
                        
                        <div class="items-grid categories-grid" id="categories-grid">
                        <?php
                        // Get categories for editing
                        try {
                            $categories_sql = "SELECT Id_kategoria, emri FROM kategorite ORDER BY emri";
                            $categories_stmt = $conn->prepare($categories_sql);
                            $categories_stmt->execute();
                            $categories_result = $categories_stmt->get_result();
                            $categories_data = $categories_result->fetch_all(MYSQLI_ASSOC);

                            if (!empty($categories_data)) {
                                foreach ($categories_data as $kategoria) {
                                    echo '<div class="item-card category-card category-item" data-name="' . strtolower(htmlspecialchars($kategoria['emri'])) . '">';
                                    echo '<div class="item-content">';
                                    
                                    // Category info
                                    echo '<div class="item-info">';
                                    echo '<h4>' . htmlspecialchars($kategoria['emri']) . '</h4>';
                                    echo '</div>';
                                    
                                    // Edit button
                                    echo '<a href="modifik.php?edit_category=' . $kategoria['Id_kategoria'] . '" class="edit-btn category-edit">';
                                    echo '<i class="fas fa-edit"></i> Modifiko';
                                    echo '</a>';
                                    
                                    echo '</div>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<p class="no-items">Nuk ka kategori të disponueshme.</p>';
                            }
                        } catch (Exception $e) {
                            echo '<p class="error-text">Gabim në leximin e kategorive: ' . htmlspecialchars($e->getMessage()) . '</p>';
                        }
                        ?>
                        </div>
                    </div>
                    
                    <!-- No Results Message -->
                    <div id="no-results" style="display: none; text-align: center; padding: 2rem; color: #8a7a68;">
                        <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <p>Nuk u gjetën rezultate për kërkimin tuaj.</p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Back Button -->
                <div class="back-section">
                    <a href="index.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i>
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

    .admin-btn, .logout-btn {
        color: white !important;
        text-decoration: none;
        font-weight: 500;
        padding: 0.6rem 1.2rem;
        border-radius: 25px;
        background: linear-gradient(135deg, #8d6e63 0%, #a1887f 100%);
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(141, 110, 99, 0.2);
        font-size: 0.9rem;
    }

    .admin-btn:hover, .logout-btn:hover {
        background: linear-gradient(135deg, #6d4c41 0%, #8d6e63 100%) !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(141, 110, 99, 0.4) !important;
    }

    /* Alert Styles */
    .alert {
        padding: 1rem 1.5rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        text-align: center;
        font-weight: 500;
    }

    .alert-success {
        background: linear-gradient(135deg,  #5DADE2 0%, #5DADE2 100%);
        color: white;
    }

    .alert-error {
        background: linear-gradient(135deg, #ef5350 0%, #e57373 100%);
        color: white;
    }

    /* Form Styles */
    .form-container {
        background: linear-gradient(135deg, #ffffff 0%, #fefcfa 100%);
        border-radius: 15px;
        padding: 2.5rem;
        box-shadow: 0 6px 24px rgba(212,184,150,0.2);
        border: 3px solid transparent;
        max-width: 800px;
        margin: 0 auto;
        transition: all 0.3s ease;
    }

    .form-container.category-form {
        max-width: 600px;
    }

    .form-container:hover {
        transform: translateY(-5px);
        border-color: #d4b896;
        box-shadow: 0 12px 40px rgba(212,184,150,0.3);
    }

    .edit-form {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .form-group label {
        color: #6b5b47;
        font-weight: 600;
        font-size: 1rem;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 1rem;
        border: 2px solid #e6ccaa;
        border-radius: 12px;
        font-size: 1rem;
        background: #fefcfa;
        color: #6b5b47;
        transition: border-color 0.3s ease;
        box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: #d4b896;
        box-shadow: 0 0 0 3px rgba(212,184,150,0.2);
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    /* Photo Upload Styles */
    .current-photo {
        text-align: center;
        margin-bottom: 1rem;
    }

    .current-photo img {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 12px;
        border: 3px solid #8d6e63;
        box-shadow: 0 4px 12px rgba(141, 110, 99, 0.3);
    }

    .current-photo .filename {
        font-size: 0.8rem;
        color: #8a7a68;
        margin-top: 0.5rem;
    }

    .photo-tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .tab-btn {
        background: #8d6e63;
        color: white;
        padding: 0.6rem 1.2rem;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .tab-btn.upload-btn {
        background:  #5DADE2;
    }

    .tab-btn:hover {
        transform: scale(1.05);
    }

    .photo-gallery {
        margin-top: 1rem;
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #e6ccaa;
        border-radius: 8px;
        padding: 0.5rem;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
        gap: 0.5rem;
    }

    .photo-item {
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .photo-item:hover {
        transform: scale(1.05);
    }

    .photo-item img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid transparent;
        transition: border-color 0.3s ease;
    }

    .photo-item:hover img {
        border-color: #8d6e63;
    }

    .photo-item p {
        font-size: 0.7rem;
        margin: 0.2rem 0;
        color: #6b5b47;
        word-break: break-all;
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 1rem;
    }

    .btn {
        padding: 1rem 2rem;
        border: none;
        border-radius: 50px;
        font-size: 1rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-success {
        flex: 1;
        background: linear-gradient(135deg,  #5DADE2 0%, #5DADE2 100%);
        color: white;
    }

    .btn-secondary {
        flex: 0 0 auto;
        background: linear-gradient(135deg, #8d6e63 0%, #a1887f 100%);
        color: white;
    }

    .btn-primary {
        background: linear-gradient(135deg, #5DADE2 0%, #85C1E9 100%);
        color: white;
    }

    .btn:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }

    /* No Selection Styles */
    .no-selection {
        text-align: center;
        padding: 3rem;
        background: linear-gradient(135deg, #ffffff 0%, #fefcfa 100%);
        border-radius: 15px;
        box-shadow: 0 6px 24px rgba(212,184,150,0.2);
    }

    .no-selection i {
        font-size: 4rem;
        color: #8a7a68;
        margin-bottom: 1rem;
    }

    .no-selection h2 {
        color: #6b5b47;
        margin-bottom: 1rem;
    }

    .no-selection p {
        color: #8a7a68;
        margin-bottom: 2rem;
    }

    .section {
        margin-bottom: 2rem;
    }

    .section h3 {
        color: #6b5b47;
        margin-bottom: 1rem;
        text-align: left;
    }

    .items-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .categories-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    }

    .item-card {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 2px solid #e6ccaa;
        transition: all 0.3s ease;
    }

    .item-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    }

    .item-content {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .item-card img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #8d6e63;
    }

    .no-image {
        width: 50px;
        height: 50px;
        background: #e6ccaa;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #8d6e63;
    }

    .item-info {
        flex: 1;
    }

    .item-info h4 {
        margin: 0;
        color: #6b5b47;
        font-size: 0.9rem;
    }

    .item-info p {
        margin: 0.2rem 0;
        color: #8a7a68;
        font-size: 0.8rem;
    }

    .edit-btn {
        color: white;
        padding: 0.3rem 0.6rem; /* U zvogëlua edhe më shumë */
        border-radius: 8px; /* U zvogëlua nga 15px */
        text-decoration: none;
        font-size: 0.65rem; /* U zvogëlua nga 0.75rem */
        font-weight: 600;
        transition: all 0.3s ease;
        white-space: nowrap;
        min-width: auto; /* Hequr min-width */
        height: auto; /* Hequr height të fikse */
        line-height: 1.2; /* Më pak lartësi */
    }

    .product-edit {
        background: linear-gradient(135deg, #5DADE2 0%, #85C1E9 100%);
    }

    .category-edit {
        background: linear-gradient(135deg, #8d6e63 0%, #a1887f 100%);
    }

    .edit-btn:hover {
        transform: scale(1.03); /* U zvogëlua nga 1.05 */
    }

    .edit-btn i {
        font-size: 0.6rem; /* Ikonë më e vogël */
        margin-right: 0.3rem;
    }

    /* Back Button */
    .back-section {
        text-align: center;
        margin-top: 2rem;
    }

    .back-btn {
        display: inline-block;
        background: linear-gradient(135deg, #8d6e63 0%, #a1887f 100%);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 30px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(141, 110, 99, 0.3);
    }

    .back-btn:hover {
        background: linear-gradient(135deg, #6d4c41 0%, #8d6e63 100%);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(141, 110, 99, 0.4);
    }

    /* Search Styles */
    .search-section {
        text-align: center;
        margin-bottom: 3rem;
    }

    .search-section h2 {
        color: #6b5b47;
        margin-bottom: 0.8rem;
        font-size: 2rem;
        font-weight: 600;
    }

    .search-section p {
        color: #8a7a68;
        margin-bottom: 2rem;
        font-size: 1.1rem;
    }

    .search-bar {
        max-width: 500px; /* U zvogëlua nga 700px */
        margin: 0 auto 2.5rem; /* U zvogëlua margin-bottom */
        position: relative;
        display: flex;
        align-items: center;
        background: linear-gradient(135deg, #f8f8f8 0%, #ffffff 100%);
        border-radius: 40px; /* U zvogëlua nga 50px */
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        border: 2px solid #e8e8e8; /* U zvogëlua nga 3px */
        transition: all 0.4s ease;
        overflow: hidden;
        padding: 0.3rem; /* U zvogëlua nga 0.5rem */
    }

    .search-bar:focus-within {
        border-color: #5DADE2;
        box-shadow: 0 12px 35px rgba(93, 173, 226, 0.2);
        transform: translateY(-3px); /* U zvogëlua nga -5px */
        background: linear-gradient(135deg, #ffffff 0%, #f8faff 100%);
    }

    .search-bar input {
        width: 100%;
        padding: 1rem 1.5rem; /* U zvogëlua nga 1.5rem 2rem */
        border: none;
        border-radius: 35px; /* U zvogëlua nga 45px */
        font-size: 1rem; /* U zvogëlua nga 1.2rem */
        background: transparent;
        color: #4a4a4a;
        font-weight: 400;
        letter-spacing: 0.3px; /* U zvogëlua nga 0.5px */
    }

    .search-bar input::placeholder {
        color: #b8b8b8;
        font-style: italic;
        transition: color 0.3s ease;
        font-weight: 300;
    }

    /* Hover effect për search bar */
    .search-bar:hover {
        border-color: #d4d4d4;
        box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        transform: translateY(-2px);
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
        }
        
        h1 {
            font-size: 2rem !important;
        }
        
        .form-container {
            padding: 2rem 1.5rem !important;
        }
        
        .form-row {
            grid-template-columns: 1fr !important;
        }
        
        .form-actions {
            flex-direction: column !important;
        }
        
        .items-grid {
            grid-template-columns: 1fr !important;
        }
        
        .photo-tabs {
            flex-direction: column;
            gap: 0.2rem;
        }
        
        .tab-btn {
            width: 100%;
            padding: 0.8rem;
            font-size: 0.9rem;
        }
    }
    </style>

    <script>
    function showExistingPhotos() {
        document.getElementById('existing-photos').style.display = 'block';
        document.getElementById('upload-new').style.display = 'none';
        
        // Clear file input
        const fileInput = document.querySelector('input[type="file"]');
        if (fileInput) fileInput.value = '';
    }
    
    function showUploadNew() {
        document.getElementById('existing-photos').style.display = 'none';
        document.getElementById('upload-new').style.display = 'block';
        
        // Clear select
        const select = document.querySelector('select[name="existing_foto"]');
        if (select) select.value = '';
    }
    
    function selectExistingPhoto(filename) {
        const select = document.querySelector('select[name="existing_foto"]');
        if (select) {
            select.value = filename;
            
            // Visual feedback
            const allImages = document.querySelectorAll('.photo-item img');
            allImages.forEach(img => {
                img.style.borderColor = 'transparent';
                img.style.borderWidth = '2px';
            });
            
            const selectedImg = document.querySelector(`[onclick*="${filename}"] img`);
            if (selectedImg) {
                selectedImg.style.borderColor = '#8d6e63';
                selectedImg.style.borderWidth = '3px';
            }
        }
    }

    function searchItems() {
        const query = document.getElementById('searchInput').value.toLowerCase();
        const products = document.querySelectorAll('.product-item');
        const categories = document.querySelectorAll('.category-item');
        let hasResults = false;

        products.forEach(item => {
            const name = item.getAttribute('data-name');
            if (name && name.includes(query)) {
                item.style.display = 'block';
                hasResults = true;
            } else {
                item.style.display = 'none';
            }
        });

        categories.forEach(item => {
            const name = item.getAttribute('data-name');
            if (name && name.includes(query)) {
                item.style.display = 'block';
                hasResults = true;
            } else {
                item.style.display = 'none';
            }
        });

        document.getElementById('no-results').style.display = hasResults ? 'none' : 'block';
    }

    function clearSearch() {
        document.getElementById('searchInput').value = '';
        searchItems();
    }
    </script>
</body>
</html>