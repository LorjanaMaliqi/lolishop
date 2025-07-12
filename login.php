<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Ju lutemi plotësoni të gjitha fushat!';
    } else {
        // Kontrolloni përdoruesin në databazë
        $sql = "SELECT id_perdoruesi, emri, fjalekalimi, roli FROM perdoruesit WHERE emaili = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // TESTIM - krahasim VETEM fjalëkalime të thjeshta (pa hash)
            if ($password == $user['fjalekalimi']) {
                // Login i suksesshëm
                $_SESSION['user_id'] = $user['id_perdoruesi'];
                $_SESSION['user_name'] = $user['emri'];
                $_SESSION['user_role'] = $user['roli'];
                
                redirectTo('index.php');
            } else {
                $error = 'Fjalëkalimi është i pasaktë! Testoni: admin123, test123, ose loli123';
            }
        } else {
            $error = 'Emaili nuk ekziston!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hyrje - Loli Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="auth-main">
        <div class="container">
            <div class="auth-container">
                <div class="auth-box">
                    <h2><i class="fas fa-sign-in-alt"></i> Hyrje në Loli Shop</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="auth-form">
                        <div class="form-group">
                            <label for="email">Emaili</label>
                            <input type="email" id="email" name="email" required 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Fjalëkalimi</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-full">
                            <i class="fas fa-sign-in-alt"></i> Hyr
                        </button>
                    </form>
                    
                    <div class="auth-links">
                        <p>Nuk keni llogari? <a href="register.php">Regjistrohuni këtu</a></p>
                        <a href="forgot-password.php">Keni harruar fjalëkalimin?</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/script.js"></script>
</body>
</html>
