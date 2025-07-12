<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validimi
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Ju lutemi plotësoni të gjitha fushat!';
    } elseif ($password !== $confirm_password) {
        $error = 'Fjalëkalimet nuk përputhen!';
    } elseif (strlen($password) < 6) {
        $error = 'Fjalëkalimi duhet të ketë të paktën 6 karaktere!';
    } else {
        // Kontrolloni nëse emaili ekziston
        $sql = "SELECT id_perdoruesi FROM perdoruesit WHERE emaili = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Ky email është i regjistruar më parë!';
        } else {
            // Regjistroni përdoruesin (pa enkriptim)
            $sql = "INSERT INTO perdoruesit (emri, emaili, fjalekalimi) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $name, $email, $password);
            
            if ($stmt->execute()) {
                $success = 'Regjistrimi u krye me sukses! Mund të hyni tani.';
            } else {
                $error = 'Gabim gjatë regjistrimit. Provoni përsëri!';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regjistrohu - Loli Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="auth-main">
        <div class="container">
            <div class="auth-container">
                <div class="auth-box">
                    <h2><i class="fas fa-user-plus"></i> Regjistrohu në Loli Shop</h2>
                    
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
                            <label for="name">Emri i plotë</label>
                            <input type="text" id="name" name="name" required 
                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Emaili</label>
                            <input type="email" id="email" name="email" required 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Fjalëkalimi</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Konfirmo fjalëkalimin</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-full">
                            <i class="fas fa-user-plus"></i> Regjistrohu
                        </button>
                    </form>
                    
                    <div class="auth-links">
                        <p>Keni llogari? <a href="login.php">Hyni këtu</a></p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/script.js"></script>
</body>
</html>
