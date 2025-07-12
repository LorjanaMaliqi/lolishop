<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirectTo('login.php');
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$user_id = $_SESSION['user_id'];

// Kontrolloni nëse porosia ekziston dhe i përket përdoruesit
$sql = "SELECT * FROM porosite WHERE id_porosia = ? AND id_perdoruesi = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirectTo('index.php');
}

$order = $result->fetch_assoc();

// Merrni produktet e porosisë
$sql = "SELECT pp.*, p.emri, p.foto FROM porosi_produktet pp 
        JOIN produktet p ON pp.id_produkti = p.Id_produkti 
        WHERE pp.id_porosia = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Porosia e Konfirmuar - Loli Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
      <main class="success-main">
        <div class="container">
            <div class="success-content">
                <!-- Success Header -->
                <div class="success-header">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h1>Porosia e Konfirmuar!</h1>
                    <p class="success-message">
                        Faleminderit për besimin! Porosia juaj u regjistrua me sukses.
                    </p>
                </div>

                <!-- Order Details Table -->
                <div class="order-table-container">
                    <h2><i class="fas fa-receipt"></i> Detajet e Porosisë</h2>
                    
                    <table class="order-details-table">
                        <tbody>
                            <tr>
                                <td class="label-cell">
                                    <i class="fas fa-hashtag"></i>
                                    Numri i Porosisë
                                </td>
                                <td class="value-cell">
                                    #<?php echo str_pad($order['id_porosia'], 6, '0', STR_PAD_LEFT); ?>
                                </td>
                                <td class="label-cell">
                                    <i class="fas fa-calendar"></i>
                                    Data e Porosisë
                                </td>
                                <td class="value-cell">
                                    <?php echo date('d/m/Y H:i', strtotime($order['data_krijimit'])); ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-cell">
                                    <i class="fas fa-clock"></i>
                                    Statusi
                                </td>
                                <td class="value-cell">
                                    <span class="status-badge">Në pritje</span>
                                </td>
                                <td class="label-cell">
                                    <i class="fas fa-credit-card"></i>
                                    Metoda e Pagesës
                                </td>
                                <td class="value-cell">
                                    <?php 
                                    switch($order['metoda_pageses']) {
                                        case 'para_ne_dore': echo 'Para në dorë'; break;
                                        case 'karte_krediti': echo 'Kartë krediti'; break;
                                        case 'transfer_bankar': echo 'Transfer bankar'; break;
                                        default: echo ucfirst($order['metoda_pageses']);
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-cell">
                                    <i class="fas fa-user"></i>
                                    Emri i Plotë
                                </td>
                                <td class="value-cell">
                                    <?php echo htmlspecialchars($order['emri'] . ' ' . $order['mbiemri']); ?>
                                </td>
                                <td class="label-cell">
                                    <i class="fas fa-phone"></i>
                                    Telefoni
                                </td>
                                <td class="value-cell">
                                    <?php echo htmlspecialchars($order['telefoni']); ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-cell">
                                    <i class="fas fa-envelope"></i>
                                    Email
                                </td>
                                <td class="value-cell">
                                    <?php echo htmlspecialchars($order['email']); ?>
                                </td>
                                <td class="label-cell">
                                    <i class="fas fa-map-marker-alt"></i>
                                    Qyteti
                                </td>
                                <td class="value-cell">
                                    <?php echo htmlspecialchars($order['qyteti']); ?> <?php echo htmlspecialchars($order['kodi_postal']); ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-cell">
                                    <i class="fas fa-home"></i>
                                    Adresa e Dërgesës
                                </td>
                                <td class="value-cell" colspan="3">
                                    <?php echo htmlspecialchars($order['adresa']); ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Products Table -->
                <div class="products-table-container">
                    <h2><i class="fas fa-shopping-bag"></i> Produktet e Porositura</h2>
                    
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th>Produkti</th>
                                <th>Sasia</th>
                                <th>Çmimi</th>
                                <th>Totali</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($item = $order_items->fetch_assoc()): ?>
                            <tr>
                                <td class="product-cell">
                                    <div class="product-display">
                                        <img src="assets/images/<?php echo htmlspecialchars($item['foto']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['emri']); ?>" 
                                             class="order-product-image">
                                        <span class="product-name"><?php echo htmlspecialchars($item['emri']); ?></span>
                                    </div>
                                </td>
                                <td class="quantity-cell">
                                    <?php echo $item['sasia']; ?>
                                </td>
                                <td class="price-cell">
                                    €<?php echo number_format($item['cmimi'], 2); ?>
                                </td>
                                <td class="total-cell">
                                    €<?php echo number_format($item['cmimi'] * $item['sasia'], 2); ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td colspan="3" class="total-label">
                                    <strong>Totali i Përgjithshëm:</strong>
                                </td>
                                <td class="final-total">
                                    <strong>€<?php echo number_format($order['totali'], 2); ?></strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="produktet.php" class="btn btn-primary">
                        <i class="fas fa-shopping-bag"></i> Vazhdoni Blerjet
                    </a>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-home"></i> Kryefaqja
                    </a>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/script.js"></script>
</body>
</html>
