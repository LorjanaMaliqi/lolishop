<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Loli Shop</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Header with correct path -->
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
                    <a href="../cart.php" class="cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">0</span>
                    </a>
                    
                    <div class="user-menu">
                        <span>Admin Loli!</span>
                        <div class="dropdown">
                            <a href="../logout.php">
                                <i class="fas fa-sign-out-alt"></i> Dalje
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main style="min-height: 100vh; background: linear-gradient(135deg, #e0d1b6 0%, #f0e8d8 100%); padding: 4rem 0;">
        <div class="container">
            <div style="max-width: 800px; margin: 0 auto;">
                <!-- Page Title -->
                <div style="text-align: center; margin-bottom: 3rem;">
                    <h1 style="color: #4d4b49; font-size: 2.5rem; margin-bottom: 1rem;">
                        <i class="fas fa-cog" style="color: #c7a76f; margin-right: 1rem;"></i>
                        Admin Panel
                    </h1>
                    <p style="color: #6c757d; font-size: 1.1rem;">Menaxho sistemin tuaj</p>
                </div>

                <!-- Action Buttons -->
                <div style="display: flex; justify-content: center; gap: 1.2rem; max-width: 1400px; margin: 0 auto; flex-wrap: nowrap;">
                    
                    <!-- Shtim Button -->
                    <div class="admin-card" style="background: linear-gradient(135deg, #ffffff 0%, #fefcfa 100%); border-radius: 15px; padding: 2.5rem 1.5rem; text-align: center; box-shadow: 0 8px 32px rgba(212,184,150,0.2); transition: transform 0.3s ease; border: 3px solid transparent; cursor: pointer; width: 22%; min-width: 280px; height: 280px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <i class="fas fa-plus-circle" style="font-size: 3.5rem; color: #66bb6a; margin-bottom: 1.2rem;"></i>
                            <h3 style="color: #6b5b47; margin-bottom: 0.8rem; font-size: 1.4rem;">Shtim</h3>
                            <p style="color: #8a7a68; margin-bottom: 1.2rem; font-size: 0.9rem;">Shto produkte dhe kategori</p>
                        </div>
                        <a href="add.php" class="btn-admin" style="display: inline-block; background: linear-gradient(135deg, #66bb6a 0%, #81c784 100%); color: white; padding: 0.8rem 1.5rem; border-radius: 25px; text-decoration: none; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; transition: all 0.3s ease; font-size: 0.85rem;">
                            <i class="fas fa-plus"></i> SHTIM
                        </a>
                    </div>

                    <!-- Modifikim Button -->
                    <div class="admin-card" style="background: linear-gradient(135deg, #ffffff 0%, #fefcfa 100%); border-radius: 15px; padding: 2.5rem 1.5rem; text-align: center; box-shadow: 0 8px 32px rgba(212,184,150,0.2); transition: transform 0.3s ease; border: 3px solid transparent; cursor: pointer; width: 22%; min-width: 280px; height: 280px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <i class="fas fa-edit" style="font-size: 3.5rem; color: #64b5f6; margin-bottom: 1.2rem;"></i>
                            <h3 style="color: #6b5b47; margin-bottom: 0.8rem; font-size: 1.4rem;">Modifikim</h3>
                            <p style="color: #8a7a68; margin-bottom: 1.2rem; font-size: 0.9rem;">Edito përmbajtjen</p>
                        </div>
                        <a href="modifik.php" class="btn-admin" style="display: inline-block; background: linear-gradient(135deg, #64b5f6 0%, #90caf9 100%); color: white; padding: 0.8rem 1.5rem; border-radius: 25px; text-decoration: none; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; transition: all 0.3s ease; font-size: 0.85rem;">
                            <i class="fas fa-edit"></i> MODIFIKIM
                        </a>
                    </div>

                    <!-- Fshirje Button -->
                    <div class="admin-card" style="background: linear-gradient(135deg, #ffffff 0%, #fefcfa 100%); border-radius: 15px; padding: 2.5rem 1.5rem; text-align: center; box-shadow: 0 8px 32px rgba(212,184,150,0.2); transition: transform 0.3s ease; border: 3px solid transparent; cursor: pointer; width: 22%; min-width: 280px; height: 280px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <i class="fas fa-trash-alt" style="font-size: 3.5rem; color: #ef5350; margin-bottom: 1.2rem;"></i>
                            <h3 style="color: #6b5b47; margin-bottom: 0.8rem; font-size: 1.4rem;">Fshirje</h3>
                            <p style="color: #8a7a68; margin-bottom: 1.2rem; font-size: 0.9rem;">Fshij të dhëna</p>
                        </div>
                        <a href="delete.php" class="btn-admin" style="display: inline-block; background: linear-gradient(135deg, #ef5350 0%, #e57373 100%); color: white; padding: 0.8rem 1.5rem; border-radius: 25px; text-decoration: none; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; transition: all 0.3s ease; font-size: 0.85rem;">
                            <i class="fas fa-trash"></i> FSHIRJE
                        </a>
                    </div>

                    <!-- Mesazhet Button -->
                    <div class="admin-card" style="background: linear-gradient(135deg, #ffffff 0%, #fefcfa 100%); border-radius: 15px; padding: 2.5rem 1.5rem; text-align: center; box-shadow: 0 8px 32px rgba(212,184,150,0.2); transition: transform 0.3s ease; border: 3px solid transparent; cursor: pointer; width: 22%; min-width: 280px; height: 280px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <i class="fas fa-envelope" style="font-size: 3.5rem; color: #c7a76f; margin-bottom: 1.2rem;"></i>
                            <h3 style="color: #6b5b47; margin-bottom: 0.8rem; font-size: 1.4rem;">Mesazhet</h3>
                            <p style="color: #8a7a68; margin-bottom: 1.2rem; font-size: 0.9rem;">Menaxho mesazhet</p>
                        </div>
                        <a href="messages.php" class="btn-admin" style="display: inline-block; background: linear-gradient(135deg, #c7a76f 0%, #c7a76f 100%); color: white; padding: 0.8rem 1.5rem; border-radius: 25px; text-decoration: none; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; transition: all 0.3s ease; font-size: 0.85rem;">
                            <i class="fas fa-envelope"></i> MESAZHET
                        </a>
                    </div>
                </div>

                <!-- Back to Home -->
                <div style="text-align: center; margin-top: 3rem;">
                    <a href="../index.php" style="display: inline-block; background:  #8a7a68; color: white; padding: 1rem 2rem; border-radius: 50px; text-decoration: none; font-weight: 600; transition: all 0.3s ease;">
                        <i class="fas fa-arrow-left" style="margin-right: 0.5rem;"></i>
                        Kthehu në faqen kryesore
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer with correct path -->
    <footer style="background:  #4d4b49; color: white; text-align: center; padding: 2rem 0; margin-top: 2rem;">
        <div class="container">
            <p>&copy; 2025 Loli Shop. Të gjitha të drejtat e rezervuara.</p>
        </div>
    </footer>

    <style>
    /* Header Styles */
    .header {
        background: white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
        color: #4d4b49;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s ease;
    }
    
    .nav a:hover {
        color: #c7a76f;
    }
    
    .header-actions {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .cart-icon {
        position: relative;
        color: #4d4b49;
        font-size: 1.2rem;
        text-decoration: none;
    }
    
    .cart-count {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #c7a76f;
        color: white;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        font-size: 0.7rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .user-menu {
        position: relative;
        color: #4d4b49;
        font-weight: 500;
    }
    
    .dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 0.5rem;
        display: none;
        min-width: 120px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    
    .user-menu:hover .dropdown {
        display: block;
    }
    
    .dropdown a {
        display: block;
        color: #4d4b49;
        text-decoration: none;
        padding: 0.5rem;
        border-radius: 4px;
        transition: background 0.3s ease;
    }
    
    .dropdown a:hover {
        background: #f8f9fa;
    }

    /* Hover effects */
    .admin-card:hover {
        transform: translateY(-10px);
        border-color: #c7a76f !important;
        box-shadow: 0 12px 40px rgba(0,0,0,0.15) !important;
    }
    
    .btn-admin:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }
    
    /* Mobile responsive */
    @media (max-width: 768px) {
        .header-content {
            flex-direction: column;
            gap: 1rem;
        }
        
        .nav {
            order: 2;
            width: 100%;
            justify-content: center;
        }
        
        /* Stack vertically on mobile */
        div[style*="flex-wrap: nowrap"] {
            flex-direction: column !important;
            gap: 1rem !important;
            padding: 0 1rem;
        }
        
        .admin-card {
            width: 100% !important;
            max-width: 300px !important;
            margin: 0 auto !important;
        }
        
        h1 {
            font-size: 2rem !important;
        }
    }
    </style>

    <script src="../assets/js/script.js"></script>
</body>
</html>
