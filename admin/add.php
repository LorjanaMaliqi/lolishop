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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_product'])) {
        // Add Product
        $emri = trim($_POST['emri']);
        $pershkrimi = trim($_POST['pershkrimi']);
        $cmimi = floatval($_POST['cmimi']);
        $kategoria_id = intval($_POST['kategoria_id']);
        $sasia = intval($_POST['sasia']);
        
        // Handle image upload
        $foto = 'default.jpg'; // Default value
        
        // Check if user selected existing image
        if (isset($_POST['existing_foto']) && !empty($_POST['existing_foto'])) {
            $foto = $_POST['existing_foto'];
            $success_message = "Foto e zgjedhur: " . $foto;
        }
        // Or handle new image upload
        else if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $upload_dir = '../assets/images/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                if (!mkdir($upload_dir, 0777, true)) {
                    $error_message = "Gabim në krijimin e direktorisë për fotot!";
                }
            }
            
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
                        $foto = 'default.jpg';
                    }
                }
            }
        }
        
        try {
            // Check what columns exist in produktet table
            $check_produktet = "SHOW COLUMNS FROM produktet";
            $columns_result = $conn->query($check_produktet);
            
            $has_kategoria_id = false;
            $has_sasia = false;
            $has_data_krijimit = false;
            
            if ($columns_result) {
                while ($column = $columns_result->fetch_assoc()) {
                    $field = strtolower($column['Field']);
                    if ($field === 'kategoria_id') $has_kategoria_id = true;
                    if ($field === 'sasia') $has_sasia = true;
                    if ($field === 'data_krijimit') $has_data_krijimit = true;
                }
            }
            
            // Build SQL based on available columns
            $sql_fields = "emri, pershkrimi, cmimi, foto";
            $sql_values = "?, ?, ?, ?";
            $params = [$emri, $pershkrimi, $cmimi, $foto];
            
            if ($has_kategoria_id) {
                $sql_fields .= ", kategoria_id";
                $sql_values .= ", ?";
                $params[] = $kategoria_id;
            }
            
            if ($has_sasia) {
                $sql_fields .= ", sasia";
                $sql_values .= ", ?";
                $params[] = $sasia;
            }
            
            if ($has_data_krijimit) {
                $sql_fields .= ", data_krijimit";
                $sql_values .= ", NOW()";
            }
            
            $sql = "INSERT INTO produktet ({$sql_fields}) VALUES ({$sql_values})";
            $stmt = $conn->prepare($sql);
            
            if ($stmt->execute($params)) {
                $success_message = "Produkti u shtua me sukses!";
            } else {
                $error_message = "Gabim në shtimin e produktit!";
            }
            
        } catch (Exception $e) {
            $error_message = "Gabim në shtimin e produktit: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['add_category'])) {
        // Add Category - Simple version
        $emri = trim($_POST['emri_kategori']);
        
        if (empty($emri)) {
            $error_message = "Emri i kategorisë është i detyrueshëm!";
        } else {
            try {
                // Check if category already exists
                $check_sql = "SELECT COUNT(*) as count FROM kategorite WHERE emri = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->execute([$emri]);
                $result = $check_stmt->get_result()->fetch_assoc();
                
                if ($result['count'] > 0) {
                    $error_message = "Kategoria '{$emri}' ekziston tashmë!";
                } else {
                    // Add new category
                    $sql = "INSERT INTO kategorite (emri) VALUES (?)";
                    $stmt = $conn->prepare($sql);
                    
                    if ($stmt->execute([$emri])) {
                        $success_message = "Kategoria '{$emri}' u shtua me sukses!";
                    } else {
                        $error_message = "Gabim në shtimin e kategorisë!";
                    }
                }
                
            } catch (Exception $e) {
                $error_message = "Gabim në shtimin e kategorisë: " . $e->getMessage();
            }
        }
    }
}

// Get categories for product form - Check structure first
$kategorite = [];
try {
    // First check what columns exist in kategorite table
    $check_columns = "SHOW COLUMNS FROM kategorite";
    $columns_result = $conn->query($check_columns);
    
    $has_id = false;
    $id_column = '';
    $name_column = '';
    
    if ($columns_result) {
        while ($column = $columns_result->fetch_assoc()) {
            $field = strtolower($column['Field']);
            if (in_array($field, ['id', 'kategoria_id', 'kategori_id'])) {
                $has_id = true;
                $id_column = $column['Field'];
            }
            if (in_array($field, ['emri', 'emri_kategori', 'name'])) {
                $name_column = $column['Field'];
            }
        }
    }
    
    if ($has_id && $name_column) {
        // Use existing ID column
        $kategorite_sql = "SELECT {$id_column} as id, {$name_column} as emri FROM kategorite ORDER BY {$name_column}";
        $kategorite_result = $conn->query($kategorite_sql);
        
        if ($kategorite_result) {
            while ($row = $kategorite_result->fetch_assoc()) {
                $kategorite[] = array(
                    'id' => $row['id'],
                    'emri' => $row['emri']
                );
            }
        }
    } else if ($name_column) {
        // No ID column, create artificial ones
        $kategorite_sql = "SELECT {$name_column} as emri FROM kategorite ORDER BY {$name_column}";
        $kategorite_result = $conn->query($kategorite_sql);
        
        if ($kategorite_result) {
            $index = 1;
            while ($row = $kategorite_result->fetch_assoc()) {
                $kategorite[] = array(
                    'id' => $index++,
                    'emri' => $row['emri']
                );
            }
        }
    } else {
        $error_message = "Tabela 'kategorite' nuk ka strukturën e duhur!";
    }
    
} catch (Exception $e) {
    $error_message = "Gabim në leximin e kategorive: " . $e->getMessage();
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
    <title>Shtim - Admin Panel</title>
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
                        <i class="fas fa-store"></i> Loli Shop
                    </a>
                </div>
                
                <nav class="nav">
                    <a href="../index.php">Ballina</a>
                    <a href="../produktet.php">Produktet</a>
                    <a href="../kategorite.php">Kategorite</a>
                    <a href="../contact.php">Kontakti</a>
                </nav>
                
                <div class="header-actions">
                    <a href="index.php" style="color: white; text-decoration: none; font-weight: 500; padding: 0.5rem 1rem; border-radius: 20px; background: linear-gradient(135deg, #8d6e63 0%, #a1887f 100%); transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(141, 110, 99, 0.2);">
                        <i class="fas fa-arrow-left"></i> Admin Panel
                    </a>
                    <a href="../logout.php" style="color: white; text-decoration: none; margin-left: 1rem; padding: 0.5rem 1rem; border-radius: 20px; background: linear-gradient(135deg, #8d6e63 0%, #a1887f 100%); transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(141, 110, 99, 0.2);">
                        <i class="fas fa
                        -sign-out-alt"></i> Dalje
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main style="background: linear-gradient(135deg, #f5f0e8 0%, #faf7f2 100%); padding: 1rem 0; min-height: calc(100vh - 200px);">
        <div class="container">
            <div style="max-width: 1200px; margin: 0 auto;">
                <!-- Page Title -->
                <div style="text-align: center; margin-bottom: 1rem;">
                    <h1 style="color: #6b5b47; font-size: 2rem; margin-bottom: 0.5rem;">
                        <i class="fas fa-plus-circle" style="color: #66bb6a; margin-right: 0.5rem;"></i>
                        Shtim të Dhënash
                    </h1>
                   
                </div>

                <!-- Success/Error Messages -->
                <?php if (!empty($success_message)): ?>
                    <div style="background: linear-gradient(135deg, #66bb6a 0%, #81c784 100%); color: white; padding: 0.8rem 1.5rem; border-radius: 8px; margin-bottom: 1rem; text-align: center;">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                    <div style="background: linear-gradient(135deg, #ef5350 0%, #e57373 100%); color: white; padding: 0.8rem 1.5rem; border-radius: 8px; margin-bottom: 1rem; text-align: center;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <!-- Forms Container -->
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                    
                    <!-- Add Product Form -->
                    <div class="form-container" style="background: linear-gradient(135deg, #ffffff 0%, #fefcfa 100%); border-radius: 15px; padding: 2rem; box-shadow: 0 6px 24px rgba(212,184,150,0.2); border: 3px solid transparent;">
                        <h2 style="color: #6b5b47; margin-bottom: 1.5rem; text-align: center;">
                            <i class="" style="color: #66bb6a; margin-right: 0.5rem;"></i>
                            Shto Produkt të Ri
                        </h2>
                        
                        <form method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 1.2rem;">
                            <div class="form-group">
                                <label style="display: block; color: #6b5b47; font-weight: 600; margin-bottom: 0.5rem;">
                                    <i class="fas fa-tag"></i> Emri i Produktit
                                </label>
                                <input type="text" name="emri" required 
                                       style="width: 100%; padding: 1rem; border: 2px solid #e6ccaa; border-radius: 12px; font-size: 1rem; background: #fefcfa; color: #6b5b47; transition: border-color 0.3s ease; box-sizing: border-box;"
                                       placeholder="Shkruaj emrin e produktit...">
                            </div>
                            
                            <div class="form-group">
                                <label style="display: block; color: #6b5b47; font-weight: 600; margin-bottom: 0.5rem;">
                                    <i class="fas fa-align-left"></i> Përshkrimi
                                </label>
                                <textarea name="pershkrimi" rows="4" required
                                          style="width: 100%; padding: 1rem; border: 2px solid #e6ccaa; border-radius: 12px; font-size: 1rem; background: #fefcfa; color: #6b5b47; resize: vertical; transition: border-color 0.3s ease; box-sizing: border-box;"
                                          placeholder="Shkruaj përshkrimin e produktit..."></textarea>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label style="display: block; color: #6b5b47; font-weight: 600; margin-bottom: 0.5rem;">
                                        <i class="fas fa-euro-sign"></i> Çmimi
                                    </label>
                                    <input type="number" name="cmimi" step="0.01" min="0" required
                                           style="width: 100%; padding: 1rem; border: 2px solid #e6ccaa; border-radius: 12px; font-size: 1rem; background: #fefcfa; color: #6b5b47; transition: border-color 0.3s ease; box-sizing: border-box;"
                                           placeholder="0.00">
                                </div>
                                
                                <div class="form-group">
                                    <label style="display: block; color: #6b5b47; font-weight: 600; margin-bottom: 0.5rem;">
                                        <i class="fas fa-warehouse"></i> Sasia
                                    </label>
                                    <input type="number" name="sasia" min="0" required
                                           style="width: 100%; padding: 1rem; border: 2px solid #e6ccaa; border-radius: 12px; font-size: 1rem; background: #fefcfa; color: #6b5b47; transition: border-color 0.3s ease; box-sizing: border-box;"
                                           placeholder="0">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label style="display: block; color: #6b5b47; font-weight: 600; margin-bottom: 0.5rem;">
                                    <i class="fas fa-tags"></i> Kategoria
                                </label>
                                <select name="kategoria_id" required
                                        style="width: 100%; padding: 1rem; border: 2px solid #e6ccaa; border-radius: 12px; font-size: 1rem; background: #fefcfa; color: #6b5b47; transition: border-color 0.3s ease; box-sizing: border-box;">
                                    <option value="">Zgjidh kategorinë...</option>
                                    <?php if (count($kategorite) > 0): ?>
                                        <?php foreach ($kategorite as $kategoria): ?>
                                            <option value="<?= $kategoria['id'] ?>"><?= htmlspecialchars($kategoria['emri']) ?></option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>Nuk ka kategori të disponueshme</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label style="display: block; color: #6b5b47; font-weight: 600; margin-bottom: 0.5rem;">
                                    <i class="fas fa-image"></i> Foto e Produktit
                                </label>
                                
                                <!-- Tabs for photo selection -->
                                <div style="margin-bottom: 1rem;">
                                    <button type="button" onclick="showExistingPhotos()" style="background: #8d6e63; color: white; padding: 0.5rem 1rem; border: none; border-radius: 10px; margin-right: 0.5rem; cursor: pointer;">
                                        <i class="fas fa-folder-open"></i> Përdor Foto Ekzistuese
                                    </button>
                                    <button type="button" onclick="showUploadNew()" style="background: #66bb6a; color: white; padding: 0.5rem 1rem; border: none; border-radius: 10px; cursor: pointer;">
                                        <i class="fas fa-upload"></i> Ngarko Foto të Re
                                    </button>
                                </div>
                                
                                <!-- Existing Photos Selection -->
                                <div id="existing-photos" style="display: block; margin-bottom: 1rem;">
                                    <select name="existing_foto" style="width: 100%; padding: 1rem; border: 2px solid #e6ccaa; border-radius: 12px; font-size: 1rem; background: #fefcfa; color: #6b5b47; box-sizing: border-box;">
                                        <option value="">Zgjidh foto nga galeria...</option>
                                        <?php foreach ($existing_images as $image): ?>
                                            <option value="<?= htmlspecialchars($image) ?>"><?= htmlspecialchars($image) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    
                                    <!-- Preview existing photos -->
                                    <div style="margin-top: 1rem; max-height: 200px; overflow-y: auto; border: 1px solid #e6ccaa; border-radius: 8px; padding: 0.5rem;">
                                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 0.5rem;">
                                            <?php foreach ($existing_images as $image): ?>
                                                <div style="text-align: center; cursor: pointer;" onclick="selectExistingPhoto('<?= htmlspecialchars($image) ?>')">
                                                    <img src="../assets/images/<?= htmlspecialchars($image) ?>" 
                                                         style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 2px solid transparent; transition: border-color 0.3s ease;"
                                                         onmouseover="this.style.borderColor='#8d6e63'"
                                                         onmouseout="this.style.borderColor='transparent'">
                                                    <p style="font-size: 0.7rem; margin: 0.2rem 0; color: #6b5b47; word-break: break-all;"><?= htmlspecialchars(substr($image, 0, 12)) ?>...</p>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Upload New Photo -->
                                <div id="upload-new" style="display: none;">
                                    <input type="file" name="foto" accept="image/*"
                                           style="width: 100%; padding: 1rem; border: 2px solid #e6ccaa; border-radius: 12px; font-size: 1rem; background: #fefcfa; color: #6b5b47; transition: border-color 0.3s ease; box-sizing: border-box;">
                                </div>
                            </div>
                            
                            <button type="submit" name="add_product" 
                                    style="background: linear-gradient(135deg, #66bb6a 0%, #81c784 100%); color: white; padding: 1rem 2rem; border: none; border-radius: 50px; font-size: 1rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; cursor: pointer; transition: all 0.3s ease; margin-top: 1rem;">
                                <i class="fas fa-plus"></i> Shto Produktin
                            </button>
                        </form>
                    </div>

                    <!-- Add Category Form - Ultra Compact Height -->
                    <div class="form-container" style="background: linear-gradient(135deg, #ffffff 0%, #fefcfa 100%); border-radius: 15px; padding: 1.2rem; box-shadow: 0 6px 24px rgba(212,184,150,0.2); border: 3px solid transparent; height: fit-content;">
                        <h2 style="color: #6b5b47; margin-bottom: 0.8rem; text-align: center; font-size: 1.1rem;">
                            Shto Kategori
                        </h2>
                        
                        <form method="POST" style="display: flex; flex-direction: column; gap: 0.6rem;">
                            <div class="form-group">
                                <label style="display: block; color: #6b5b47; font-weight: 600; margin-bottom: 0.3rem; font-size: 0.85rem;">
                                    <i class="fas fa-tag"></i> Emri i Kategorisë
                                </label>
                                <input type="text" name="emri_kategori" required 
                                       style="width: 100%; padding: 0.7rem; border: 2px solid #e6ccaa; border-radius: 8px; font-size: 0.9rem; background: #fefcfa; color: #6b5b47; transition: border-color 0.3s ease; box-sizing: border-box;"
                                       placeholder="p.sh: Teknologji">
                            </div>
                            
                            <button type="submit" name="add_category" 
                                    style="background: linear-gradient(135deg, #66bb6a 0%, #81c784 100%); color: white; padding: 0.7rem 1.2rem; border: none; border-radius: 20px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; cursor: pointer; transition: all 0.3s ease; margin-top: 0.3rem;">
                                <i class="fas fa-plus"></i> Shto
                            </button>
                        </form>

                        <!-- Compact Info -->
                       
                           
                        </div>
                    </div>
                </div>

                <!-- Back Button -->
                <div style="text-align: center; margin-top: 1.5rem; margin-bottom: 0;">
                    <a href="../index.php" style="display: inline-block; background: linear-gradient(135deg, #8a7a68 , #8a7a68); color: white; padding: 0.8rem 1.5rem; border-radius: 30px; text-decoration: none; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(141, 110, 99, 0.3);">
                        <i class="fas fa-arrow-left" style="margin-right: 0.3rem;"></i>
                        Kthehu në faqen kryesore
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
    }
    
    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
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
        gap: 1rem;
    }

    /* Form Styles */
    .form-container:hover {
        transform: translateY(-5px);
        border-color: #d4b896 !important;
        box-shadow: 0 12px 40px rgba(212,184,150,0.3) !important;
    }
    
    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: #d4b896 !important;
        box-shadow: 0 0 0 3px rgba(212,184,150,0.2);
    }
    
    button:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }

    /* Force Back Button Style */
    div[style*="text-align: center; margin-top: 1.5rem"] a {
        background: linear-gradient(135deg, #8d6e63 0%, #a1887f 100%) !important;
        color: white !important;
    }
    
    /* Back Button Hover Effect */
    div[style*="text-align: center; margin-top: 1.5rem"] a:hover {
        background: linear-gradient(135deg, #6d4c41 0%, #8d6e63 100%) !important;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(141, 110, 99, 0.4) !important;
    }

    /* Ultra specific back button fix */
    div[style*="text-align: center"] a[href="../index.php"] {
        background: linear-gradient(135deg, #8d6e63 0%, #a1887f 100%) !important;
        color: white !important;
    }

    div[style*="text-align: center"] a[href="../index.php"]:hover {
        background: linear-gradient(135deg, #6d4c41 0%, #8d6e63 100%) !important;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(141, 110, 99, 0.4) !important;
    }

    /* Header actions hover */
    .header-actions a:hover {
        background: linear-gradient(135deg, #6d4c41 0%, #8d6e63 100%) !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(141, 110, 99, 0.4) !important;
    }

    /* Mobile responsive */
    @media (max-width: 968px) {
        div[style*="grid-template-columns: 2fr 1fr"] {
            grid-template-columns: 1fr !important;
            gap: 2rem !important;
        }
        
        .header-content {
            flex-direction: column;
            gap: 1rem;
        }
        
        .nav {
            order: 2;
            width: 100%;
            justify-content: center;
        }
        
        h1 {
            font-size: 2rem !important;
        }
        
        .form-container {
            padding: 2rem 1.5rem !important;
        }
        
        div[style*="grid-template-columns: 1fr 1fr; gap: 1rem"] {
            grid-template-columns: 1fr !important;
        }
    }
    </style>

    <script src="../assets/js/script.js"></script>
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
            const allImages = document.querySelectorAll('[onclick*="selectExistingPhoto"]');
            allImages.forEach(img => {
                img.querySelector('img').style.borderColor = 'transparent';
            });
            
            const selectedImg = document.querySelector(`[onclick*="${filename}"] img`);
            if (selectedImg) {
                selectedImg.style.borderColor = '#8d6e63';
                selectedImg.style.borderWidth = '3px';
            }
        }
    }
    </script>
</body>
</html>