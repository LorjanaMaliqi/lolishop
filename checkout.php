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

// Kontrolloni nëse shporta është bosh
if (!$cart_items || $cart_items->num_rows == 0) {
    redirectTo('cart.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $emri = sanitizeInput($_POST['emri']);
    $mbiemri = sanitizeInput($_POST['mbiemri']);
    $email = sanitizeInput($_POST['email']);
    $telefoni = sanitizeInput($_POST['telefoni']);
    $adresa = sanitizeInput($_POST['adresa']);
    $qyteti = sanitizeInput($_POST['qyteti']);
    $kodi_postal = sanitizeInput($_POST['kodi_postal']);
    $metoda_pageses = sanitizeInput($_POST['metoda_pageses']);
    $komente = sanitizeInput($_POST['komente']);
    
    // Validimi
    $errors = [];
    if (empty($emri)) $errors[] = "Emri është i detyrueshëm";
    if (empty($mbiemri)) $errors[] = "Mbiemri është i detyrueshëm";
    if (empty($email)) $errors[] = "Email-i është i detyrueshëm";
    if (empty($telefoni)) $errors[] = "Telefoni është i detyrueshëm";
    if (empty($adresa)) $errors[] = "Adresa është e detyrueshme";
    if (empty($qyteti)) $errors[] = "Qyteti është i detyrueshëm";
    if (empty($metoda_pageses)) $errors[] = "Metoda e pagesës është e detyrueshme";
    
    if (empty($errors)) {
        try {
            // Filloni transaksionin
            $conn->begin_transaction();
            
            // Krijoni porosinë
            $sql = "INSERT INTO porosite (id_perdoruesi, totali, statusi, emri, mbiemri, email, telefoni, adresa, qyteti, kodi_postal, metoda_pageses, komente) 
                    VALUES (?, ?, 'ne_pritje', ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("idsssssssss", $user_id, $total, $emri, $mbiemri, $email, $telefoni, $adresa, $qyteti, $kodi_postal, $metoda_pageses, $komente);
            $stmt->execute();
            
            $porosi_id = $conn->insert_id;
            
            // Shtoni produktet e porosisë
            $cart_items = getCartItems($user_id); // Rifilloni rezultatin
            while ($item = $cart_items->fetch_assoc()) {
                $sql = "INSERT INTO porosi_produktet (id_porosia, id_produkti, sasia, cmimi) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iiid", $porosi_id, $item['Id_produkti'], $item['sasia'], $item['cmimi']);
                $stmt->execute();
            }
            
            // Fshini shportën
            $sql = "DELETE FROM shporta WHERE Id_perdoruesi = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            // Konfirmoni transaksionin
            $conn->commit();
            
            // Ridrejtoni te faqja e suksesit
            redirectTo('order-success.php?order_id=' . $porosi_id);
            
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Ka ndodhur një gabim gjatë procesimit të porosisë. Ju lutemi provoni përsëri.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Loli Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="checkout-main">
        <div class="container">
            <h1><i class="fas fa-credit-card"></i> Përfundoni Porosinë</h1>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="checkout-content">
                <div class="checkout-form">
                    <form method="POST">
                        <div class="form-section">
                            <h3><i class="fas fa-user"></i> Të dhënat personale</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="emri">Emri *</label>
                                    <input type="text" id="emri" name="emri" required value="<?php echo isset($_POST['emri']) ? htmlspecialchars($_POST['emri']) : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="mbiemri">Mbiemri *</label>
                                    <input type="text" id="mbiemri" name="mbiemri" required value="<?php echo isset($_POST['mbiemri']) ? htmlspecialchars($_POST['mbiemri']) : ''; ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email">Email *</label>
                                    <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="telefoni">Telefoni *</label>
                                    <input type="tel" id="telefoni" name="telefoni" required value="<?php echo isset($_POST['telefoni']) ? htmlspecialchars($_POST['telefoni']) : ''; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3><i class="fas fa-map-marker-alt"></i> Adresa e dërgesës</h3>
                            <div class="form-group">
                                <label for="adresa">Adresa *</label>
                                <input type="text" id="adresa" name="adresa" required placeholder="Rruga, numri i shtëpisë" value="<?php echo isset($_POST['adresa']) ? htmlspecialchars($_POST['adresa']) : ''; ?>">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="qyteti">Qyteti *</label>
                                    <input type="text" id="qyteti" name="qyteti" required value="<?php echo isset($_POST['qyteti']) ? htmlspecialchars($_POST['qyteti']) : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="kodi_postal">Kodi Postal</label>
                                    <input type="text" id="kodi_postal" name="kodi_postal" value="<?php echo isset($_POST['kodi_postal']) ? htmlspecialchars($_POST['kodi_postal']) : ''; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3><i class="fas fa-credit-card"></i> Metoda e pagesës</h3>
                            
                            <div class="payment-methods">
                                <div class="payment-option">
                                    <input type="radio" id="cash" name="metoda_pageses" value="para_ne_dore" checked>
                                    <label for="cash">
                                        <i class="fas fa-money-bill-wave"></i>
                                        Para në dorë (Pagesë në dërgesë)
                                    </label>
                                </div>
                                
                                <div class="payment-option">
                                    <input type="radio" id="card" name="metoda_pageses" value="karte_krediti">
                                    <label for="card">
                                        <i class="fas fa-credit-card"></i>
                                        Kartë krediti/debiti
                                    </label>
                                </div>
                                
                                <div class="payment-option">
                                    <input type="radio" id="transfer" name="metoda_pageses" value="transfer_bankar">
                                    <label for="transfer">
                                        <i class="fas fa-university"></i>
                                        Transfer bankar
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Card details (hidden by default) -->
                            <div id="card-details" style="display: none; margin-top: 1rem; padding: 1.5rem; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #007bff;">
                                <h4 style="margin-bottom: 1rem; color: #007bff;">
                                    <i class="fas fa-credit-card"></i> Të dhënat e kartës
                                </h4>
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label>Numri i kartës *</label>
                                        <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" 
                                               maxlength="19" pattern="[0-9\s]{13,19}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Data e skadimit *</label>
                                        <input type="text" id="card_expiry" name="card_expiry" placeholder="MM/YY" 
                                               maxlength="5" pattern="[0-9]{2}/[0-9]{2}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>CVV *</label>
                                        <input type="text" id="card_cvv" name="card_cvv" placeholder="123" 
                                               maxlength="4" pattern="[0-9]{3,4}">
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label>Emri në kartë *</label>
                                        <input type="text" id="card_name" name="card_name" placeholder="EMRI MBIEMRI">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Bank transfer details (hidden by default) -->
                            <div id="transfer-details" style="display: none; margin-top: 1rem; padding: 1.5rem; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #28a745;">
                                <h4 style="margin-bottom: 1rem; color: #28a745;">
                                    <i class="fas fa-university"></i> Të dhënat e transferit bankar
                                </h4>
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label>Numri i llogarisë bankare *</label>
                                        <input type="text" id="bank_account" name="bank_account" placeholder="1234567890123456">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Emri i bankës *</label>
                                        <input type="text" id="bank_name" name="bank_name" placeholder="Banka e Kosovës">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>SWIFT/BIC kodi</label>
                                        <input type="text" id="bank_swift" name="bank_swift" placeholder="BANKKS22">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3><i class="fas fa-comment"></i> Komente (opsionale)</h3>
                            <div class="form-group">
                                <label for="komente">Komente për porosinë</label>
                                <textarea id="komente" name="komente" rows="3" placeholder="Ndonjë informacion shtesë për porosinë tuaj..."><?php echo isset($_POST['komente']) ? htmlspecialchars($_POST['komente']) : ''; ?></textarea>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <a href="cart.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kthehu në Shportë
                            </a>
                            <button type="submit" class="btn btn-primary btn-large">
                                <i class="fas fa-check"></i> Konfirmo Porosinë (€<?php echo number_format($total, 2); ?>)
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="order-summary">
                    <div class="summary-box">
                        <h3>Përmbledhja e Porosisë</h3>
                        
                        <div class="summary-items">
                            <?php 
                            $cart_items = getCartItems($user_id); // Rifilloni për shfaqje
                            while ($item = $cart_items->fetch_assoc()): 
                            ?>
                                <div class="summary-item">
                                    <div class="item-info">
                                        <span class="item-name"><?php echo htmlspecialchars($item['emri']); ?></span>
                                        <span class="item-quantity">x<?php echo $item['sasia']; ?></span>
                                    </div>
                                    <span class="item-price">€<?php echo number_format($item['cmimi'] * $item['sasia'], 2); ?></span>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        
                        <hr>
                        
                        <div class="summary-line">
                            <span>Nëntotali:</span>
                            <span>€<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="summary-line">
                            <span>Dërgesa:</span>
                            <span>Falas</span>
                        </div>
                        <div class="summary-line total">
                            <span>Totali:</span>
                            <span>€<?php echo number_format($total, 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/script.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentRadios = document.querySelectorAll('input[name="metoda_pageses"]');
    const cardDetails = document.getElementById('card-details');
    const transferDetails = document.getElementById('transfer-details');
    
    // Card number formatting
    const cardNumberInput = document.getElementById('card_number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            if (formattedValue.length > 19) formattedValue = formattedValue.substr(0, 19);
            this.value = formattedValue;
        });
    }
    
    // Expiry date formatting
    const expiryInput = document.getElementById('card_expiry');
    if (expiryInput) {
        expiryInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            this.value = value;
        });
    }
    
    // CVV input validation
    const cvvInput = document.getElementById('card_cvv');
    if (cvvInput) {
        cvvInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }
    
    // Show/hide payment details based on selection
    paymentRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            // Hide all details first
            if (cardDetails) cardDetails.style.display = 'none';
            if (transferDetails) transferDetails.style.display = 'none';
            
            // Clear required attributes
            clearRequiredFields();
            
            // Show relevant details
            if (this.value === 'karte_krediti' && cardDetails) {
                cardDetails.style.display = 'block';
                setCardFieldsRequired(true);
            } else if (this.value === 'transfer_bankar' && transferDetails) {
                transferDetails.style.display = 'block';
                setBankFieldsRequired(true);
            }
        });
    });
    
    function clearRequiredFields() {
        // Remove required from all payment fields
        const paymentFields = document.querySelectorAll('#card-details input, #transfer-details input');
        paymentFields.forEach(field => {
            field.removeAttribute('required');
        });
    }
    
    function setCardFieldsRequired(required) {
        const cardFields = ['card_number', 'card_expiry', 'card_cvv', 'card_name'];
        cardFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                if (required) {
                    field.setAttribute('required', 'required');
                } else {
                    field.removeAttribute('required');
                }
            }
        });
    }
    
    function setBankFieldsRequired(required) {
        const bankFields = ['bank_account', 'bank_name'];
        bankFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                if (required) {
                    field.setAttribute('required', 'required');
                } else {
                    field.removeAttribute('required');
                }
            }
        });
    }
});
</script>
</body>
</html>
