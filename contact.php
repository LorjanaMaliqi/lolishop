<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Emri është i detyrueshëm.';
    }
    
    if (empty($email)) {
        $errors[] = 'Email-i është i detyrueshëm.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email-i nuk është valid.';
    }
    
    if (empty($subject)) {
        $errors[] = 'Subjekti është i detyrueshëm.';
    }
    
    if (empty($message)) {
        $errors[] = 'Mesazhi është i detyrueshëm.';
    }
    
    if (empty($errors)) {
        // Save message to database (you can create a contacts table)
        $sql = "INSERT INTO kontaktet (emri, email, subjekti, mesazhi, data_krijimit) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("ssss", $name, $email, $subject, $message);
            if ($stmt->execute()) {
                $success_message = 'Mesazhi juaj u dërgua me sukses! Do t\'ju përgjigjemi sa më shpejt.';
                // Clear form data
                $name = $email = $subject = $message = '';
            } else {
                $error_message = 'Ka ndodhur një gabim gjatë dërgimit të mesazhit.';
            }
        } else {
            $error_message = 'Ka ndodhur një gabim në sistem.';
        }
    } else {
        $error_message = implode('<br>', $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontakti - Loli Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <!-- Hero Section -->
        <section style="background: linear-gradient(135deg, #c7a76f 0%, #4d4b49 100%); color: white; padding: 4rem 0; text-align: center;">
            <div class="container">
                <h1 style="font-size: 3rem; margin-bottom: 1rem; font-weight: 700; color: white;">Na Kontaktoni</h1>
                <p style="font-size: 1.2rem; opacity: 0.9; max-width: 600px; margin: 0 auto; color: white;">Jemi këtu për t'ju ndihmuar! Dërgoni pyetjet, komentet ose sugjerimet tuaja.</p>
            </div>
        </section>

        <!-- Contact Section -->
        <section style="padding: 4rem 0; background: linear-gradient(135deg, #e0d1b6 0%, #f0e8d8 100%); min-height: 100vh;">
            <div class="container">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; max-width: 1200px; margin: 0 auto;">
                    
                    <!-- Contact Info -->
                    <div class="contact-info">
                        <div style="background: white; padding: 3rem; border-radius: 20px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); height: fit-content;">
                            <h2 style="color: #4d4b49; margin-bottom: 2rem; font-size: 2rem; font-weight: 700;">Informacioni i Kontaktit</h2>
                            
                            <div class="contact-item" style="display: flex; align-items: center; margin-bottom: 2rem; padding: 1.5rem; background: linear-gradient(135deg, #c7a76f 0%, #b8986a 100%); border-radius: 15px; color: white;">
                                <div style="background: rgba(255,255,255,0.3); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 1.5rem;">
                                    <i class="fas fa-phone" style="font-size: 1.5rem; color: white;"></i>
                                </div>
                                <div>
                                    <h3 style="margin-bottom: 0.5rem; font-size: 1.2rem; color: white;">Telefoni</h3>
                                    <p style="opacity: 0.9; margin: 0; color: white;">+383 44 123 456</p>
                                </div>
                            </div>

                            <div class="contact-item" style="display: flex; align-items: center; margin-bottom: 2rem; padding: 1.5rem; background: linear-gradient(135deg, #e0d1b6 0%, #d4c6a8 100%); border-radius: 15px; color: #4d4b49;">
                                <div style="background: rgba(255,255,255,0.3); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 1.5rem;">
                                    <i class="fas fa-envelope" style="font-size: 1.5rem; color: #4d4b49;"></i>
                                </div>
                                <div>
                                    <h3 style="margin-bottom: 0.5rem; font-size: 1.2rem; color: #4d4b49;">Email</h3>
                                    <p style="opacity: 0.9; margin: 0; color: #4d4b49;">info@lolishop.com</p>
                                </div>
                            </div>

                            <div class="contact-item" style="display: flex; align-items: center; margin-bottom: 2rem; padding: 1.5rem; background: linear-gradient(135deg, #c7a76f 0%, #b8986a 100%); border-radius: 15px; color: white;">
                                <div style="background: rgba(255,255,255,0.3); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 1.5rem;">
                                    <i class="fas fa-map-marker-alt" style="font-size: 1.5rem; color: white;"></i>
                                </div>
                                <div>
                                    <h3 style="margin-bottom: 0.5rem; font-size: 1.2rem; color: white;">Adresa</h3>
                                    <p style="opacity: 0.9; margin: 0; color: white;"><a href="https://maps.apple.com/place?coordinate=42.477281,21.469214&name=Qendresa%E2%80%99s%20Location&map=explore" target="_blank" style="color: inherit; text-decoration: none;">Gjilan, Kosovë</a></p>
                                </div>
                            </div>

                            <div class="contact-item" style="display: flex; align-items: center; padding: 1.5rem; background: linear-gradient(135deg, #e0d1b6 0%, #d4c6a8 100%); border-radius: 15px; color: #4d4b49;">
                                <div style="background: rgba(255,255,255,0.3); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 1.5rem;">
                                    <i class="fas fa-clock" style="font-size: 1.5rem; color: #4d4b49;"></i>
                                </div>
                                <div>
                                    <h3 style="margin-bottom: 0.5rem; font-size: 1.2rem; color: #4d4b49;">Orari i Punës</h3>
                                    <p style="opacity: 0.9; margin: 0; color: #4d4b49;">Hënë - Shtunë: 08:00 - 20:00</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Form -->
                    <div class="contact-form">
                        <div style="background: white; padding: 3rem; border-radius: 20px; box-shadow: 0 8px 32px rgba(0,0,0,0.1);">
                            <h2 style="color: #4d4b49; margin-bottom: 2rem; font-size: 2rem; font-weight: 700;">Dërgoni Mesazh</h2>
                            
                            <?php if ($success_message): ?>
                                <div style="background: linear-gradient(135deg, #e0d1b6 0%, #d4c6a8 100%); color: #4d4b49; padding: 1rem; border-radius: 10px; margin-bottom: 2rem; display: flex; align-items: center; border: 2px solid #c7a76f;">
                                    <i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i>
                                    <?= htmlspecialchars($success_message) ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($error_message): ?>
                                <div style="background: linear-gradient(135deg, #c7a76f 0%, #b8986a 100%); color: white; padding: 1rem; border-radius: 10px; margin-bottom: 2rem; display: flex; align-items: center; border: 2px solid #4d4b49;">
                                    <i class="fas fa-exclamation-triangle" style="margin-right: 0.5rem;"></i>
                                    <?= $error_message ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="contact.php">
                                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                                    <div class="form-group">
                                        <label style="display: block; margin-bottom: 0.8rem; color: #4d4b49; font-weight: 600; font-size: 0.95rem;">Emri i Plotë *</label>
                                        <input type="text" name="name" value="<?= htmlspecialchars($name ?? '') ?>" 
                                               style="width: 100%; padding: 1rem 1.2rem; border: 2px solid #c7a76f; border-radius: 12px; font-size: 1rem; transition: all 0.3s ease; background: white;"
                                               onfocus="this.style.borderColor='#4d4b49'; this.style.background='white'; this.style.transform='translateY(-1px)'"
                                               onblur="this.style.borderColor='#c7a76f'; this.style.background='white'; this.style.transform='translateY(0)'"
                                               required>
                                    </div>
                                    <div class="form-group">
                                        <label style="display: block; margin-bottom: 0.8rem; color: #4d4b49; font-weight: 600; font-size: 0.95rem;">Email Adresa *</label>
                                        <input type="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" 
                                               style="width: 100%; padding: 1rem 1.2rem; border: 2px solid #c7a76f; border-radius: 12px; font-size: 1rem; transition: all 0.3s ease; background: white;"
                                               onfocus="this.style.borderColor='#4d4b49'; this.style.background='white'; this.style.transform='translateY(-1px)'"
                                               onblur="this.style.borderColor='#c7a76f'; this.style.background='white'; this.style.transform='translateY(0)'"
                                               required>
                                    </div>
                                </div>

                                <div class="form-group" style="margin-bottom: 1.5rem;">
                                    <label style="display: block; margin-bottom: 0.8rem; color: #4d4b49; font-weight: 600; font-size: 0.95rem;">Subjekti *</label>
                                    <input type="text" name="subject" value="<?= htmlspecialchars($subject ?? '') ?>" 
                                           style="width: 100%; padding: 1rem 1.2rem; border: 2px solid #c7a76f; border-radius: 12px; font-size: 1rem; transition: all 0.3s ease; background: white;"
                                           onfocus="this.style.borderColor='#4d4b49'; this.style.background='white'; this.style.transform='translateY(-1px)'"
                                           onblur="this.style.borderColor='#c7a76f'; this.style.background='white'; this.style.transform='translateY(0)'"
                                           required>
                                </div>

                                <div class="form-group" style="margin-bottom: 2rem;">
                                    <label style="display: block; margin-bottom: 0.8rem; color: #4d4b49; font-weight: 600; font-size: 0.95rem;">Mesazhi *</label>
                                    <textarea name="message" rows="6" 
                                              style="width: 100%; padding: 1rem 1.2rem; border: 2px solid #c7a76f; border-radius: 12px; font-size: 1rem; transition: all 0.3s ease; background: white; resize: vertical; font-family: inherit;"
                                              onfocus="this.style.borderColor='#4d4b49'; this.style.background='white'; this.style.transform='translateY(-1px)'"
                                              onblur="this.style.borderColor='#c7a76f'; this.style.background='white'; this.style.transform='translateY(0)'"
                                              placeholder="Shkruani mesazhin tuaj këtu..."
                                              required><?= htmlspecialchars($message ?? '') ?></textarea>
                                </div>

                                <button type="submit" 
                                        style="width: 100%; padding: 1.2rem; background: linear-gradient(135deg, #c7a76f 0%, #4d4b49 100%); color: white; border: none; border-radius: 12px; font-size: 1.1rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; cursor: pointer; transition: all 0.3s ease;"
                                        onmouseover="this.style.background='linear-gradient(135deg, #b8986a 0%, #3a3937 100%)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(199, 167, 111, 0.4)'"
                                        onmouseout="this.style.background='linear-gradient(135deg, #c7a76f 0%, #4d4b49 100%)'; this.style.transform='translateY(0)'; this.style.boxShadow='none'">>
                                    <i class="fas fa-paper-plane" style="margin-right: 0.5rem;"></i>
                                    Dërgo Mesazhin
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Social Media Section - Na Ndiqni -->
                <div style="margin-top: 3rem; text-align: center;">
                    <div style="background: white; padding: 1rem 4rem; border-radius: 20px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); max-width: 1100px; min-width: 1000px; margin: 0 auto; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                        <div style="display: flex; justify-content: center; align-items: center; gap: 5rem; width: 100%; flex-wrap: wrap;">
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 0.8rem;">
                                <a href="https://www.facebook.com/share/1C2wSmMSd8/?mibextid=wwXIfr" target="_blank" style="width: 70px; height: 70px; background: linear-gradient(135deg, #c7a76f 0%, #b8986a 100%); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.3s ease; font-size: 1.8rem; box-shadow: 0 6px 20px rgba(199, 167, 111, 0.3);">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <p style="color: #4d4b49; font-weight: 600; font-size: 0.9rem; margin: 0;">Facebook</p>
                            </div>
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 0.8rem;">
                                <a href="https://www.instagram.com/qendresaamaliqii?igsh=MWRwcDdyaWo3NW16eA%3D%3D&utm_source=qr" target="_blank" style="width: 70px; height: 70px; background: linear-gradient(135deg, #e0d1b6 0%, #d4c6a8 100%); color: #4d4b49; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.3s ease; font-size: 1.8rem; box-shadow: 0 6px 20px rgba(224, 209, 182, 0.3);">
                                    <i class="fab fa-instagram"></i>
                                </a>
                                <p style="color: #4d4b49; font-weight: 600; font-size: 0.9rem; margin: 0;">Instagram</p>
                            </div>
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 0.8rem;">
                                <a href="#" style="width: 70px; height: 70px; background: linear-gradient(135deg, #4d4b49 0%, #3a3937 100%); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.3s ease; font-size: 1.8rem; box-shadow: 0 6px 20px rgba(77, 75, 73, 0.3);">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                                <p style="color: #4d4b49; font-weight: 600; font-size: 0.9rem; margin: 0;">LinkedIn</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FAQ Section -->
                <div style="margin-top: 4rem;">
                    <div style="background: white; padding: 2rem 4rem; border-radius: 20px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); max-width: 1200px; min-width: 1000px; margin: 0 auto;">
                        <h2 style="color: #4d4b49; margin-bottom: 1.5rem; font-size: 1.8rem; font-weight: 700; text-align: center;">Pyetjet e Shpeshta</h2>
                        
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 2rem;">
                            <div class="faq-item" style="border-bottom: 1px solid #e8ecef; padding-bottom: 1rem; margin-bottom: 1rem;">
                                <h3 style="color: #c7a76f; margin-bottom: 0.5rem; font-size: 1.1rem;">Si mund të porosis produktet?</h3>
                                <p style="color: #4d4b49; line-height: 1.4; font-size: 0.9rem;">Mund të porositni produktet duke i shtuar në shportë dhe duke ndjekur procesin e checkout-it. Pranojmë pagesa me para në dorë dhe kartë krediti.</p>
                            </div>

                            <div class="faq-item" style="border-bottom: 1px solid #e8ecef; padding-bottom: 1rem; margin-bottom: 1rem;">
                                <h3 style="color: #c7a76f; margin-bottom: 0.5rem; font-size: 1.1rem;">Sa kohë zgjat dërgimi?</h3>
                                <p style="color: #4d4b49; line-height: 1.4; font-size: 0.9rem;">Dërgimet brenda Kosovës zgjatin 1-2 ditë pune. Për dërgime ndërkombëtare, koha është 3-7 ditë pune.</p>
                            </div>

                            <div class="faq-item" style="border-bottom: 1px solid #e8ecef; padding-bottom: 1rem; margin-bottom: 1rem;">
                                <h3 style="color: #c7a76f; margin-bottom: 0.5rem; font-size: 1.1rem;">A mund të kthej produktet?</h3>
                                <p style="color: #4d4b49; line-height: 1.4; font-size: 0.9rem;">Po, pranojmë kthime brenda 14 ditëve nga data e blerjes, nëse produkti është në gjendje origjinale.</p>
                            </div>

                            <div class="faq-item">
                                <h3 style="color: #c7a76f; margin-bottom: 0.5rem; font-size: 1.1rem;">Si mund t'ju kontaktoj?</h3>
                                <p style="color: #4d4b49; line-height: 1.4; font-size: 0.9rem;">Mund të na kontaktoni përmes formës së kontaktit, emailit, telefonit ose rrjeteve sociale. Jemi aktiv gjatë orarit të punës.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add animations to contact items
        const contactItems = document.querySelectorAll('.contact-item');
        
        contactItems.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateX(-30px)';
            
            setTimeout(() => {
                item.style.transition = 'all 0.6s ease';
                item.style.opacity = '1';
                item.style.transform = 'translateX(0)';
            }, index * 200);
        });

        // Add hover effects to social media icons
        const socialIcons = document.querySelectorAll('a[style*="border-radius: 50%"]');
        
        socialIcons.forEach(icon => {
            icon.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px) scale(1.1)';
                this.style.boxShadow = '0 8px 25px rgba(0,0,0,0.2)';
            });
            
            icon.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
                this.style.boxShadow = 'none';
            });
        });

        // Add animation to FAQ items
        const faqItems = document.querySelectorAll('.faq-item');
        
        faqItems.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                item.style.transition = 'all 0.6s ease';
                item.style.opacity = '1';
                item.style.transform = 'translateY(0)';
            }, 1000 + (index * 150));
        });

        // Form validation enhancement
        const form = document.querySelector('form');
        const inputs = form.querySelectorAll('input, textarea');
        
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.hasAttribute('required') && !this.value.trim()) {
                    this.style.borderColor = '#FADADD';
                    this.style.boxShadow = '0 0 0 3px rgba(250, 173, 221, 0.1)';
                } else if (this.type === 'email' && this.value && !this.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                    this.style.borderColor = '#FADADD';
                    this.style.boxShadow = '0 0 0 3px rgba(250, 173, 221, 0.1)';
                } else {
                    this.style.borderColor = '#FBEEDB';
                    this.style.boxShadow = '0 0 0 3px rgba(251, 238, 219, 0.1)';
                }
            });
        });
    });
    </script>

    <style>
    /* Additional styles for contact page */
    @media (max-width: 768px) {
        .container {
            padding: 0 15px;
        }
        
        div[style*="display: grid; grid-template-columns: 1fr 1fr"] {
            display: grid !important;
            grid-template-columns: 1fr !important;
            gap: 2rem !important;
        }
        
        .form-row {
            display: grid !important;
            grid-template-columns: 1fr !important;
            gap: 1.5rem !important;
        }
        
        section[style*="padding: 4rem 0"] h1 {
            font-size: 2.2rem !important;
        }
        
        .contact-info div[style*="padding: 3rem"] {
            padding: 2rem !important;
        }
        
        .contact-form div[style*="padding: 3rem"] {
            padding: 2rem !important;
        }
        
        .contact-item {
            flex-direction: column !important;
            text-align: center !important;
        }
        
        .contact-item div[style*="margin-right: 1.5rem"] {
            margin-right: 0 !important;
            margin-bottom: 1rem !important;
        }
    }
    
    @media (max-width: 480px) {
        .contact-info div[style*="padding: 2rem"] {
            padding: 1.5rem !important;
        }
        
        .contact-form div[style*="padding: 2rem"] {
            padding: 1.5rem !important;
        }
        
        .contact-item {
            padding: 1rem !important;
        }
    }
    </style>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
