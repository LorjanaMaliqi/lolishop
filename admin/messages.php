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

// Create email_replies table if it doesn't exist
try {
    $create_table_sql = "CREATE TABLE IF NOT EXISTS `email_replies` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `original_email` varchar(255) NOT NULL,
      `reply_subject` varchar(500) NOT NULL,
      `reply_message` text NOT NULL,
      `admin_user` varchar(100) NOT NULL,
      `data_dergimit` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conn->query($create_table_sql);
} catch (Exception $e) {
    // Table creation failed, continue anyway
}

// Handle email reply
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_reply'])) {
    $reply_email = $_POST['reply_email'];
    $reply_subject = $_POST['reply_subject'];
    $reply_message = $_POST['reply_message'];
    
    try {
        $save_reply_sql = "INSERT INTO email_replies (original_email, reply_subject, reply_message, admin_user, data_dergimit) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($save_reply_sql);
        $admin_user = $_SESSION['user_id'] ?? 'admin';
        $stmt->bind_param("ssss", $reply_email, $reply_subject, $reply_message, $admin_user);
        
        if ($stmt->execute()) {
            $success_message = "Përgjigja u ruajt me sukses!";
        } else {
            $error_message = "Gabim në ruajtjen e përgjigjes!";
        }
    } catch (Exception $e) {
        $error_message = "Gabim në database: " . $e->getMessage();
    }
}

// Get sent replies for display
$sent_replies = [];
try {
    $replies_sql = "SELECT * FROM email_replies ORDER BY data_dergimit DESC";
    $stmt = $conn->prepare($replies_sql);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $sent_replies[$row['original_email']][] = $row;
    }
} catch (Exception $e) {
    // Table doesn't exist or other error, ignore
}

// Handle message deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_message'])) {
    $message_id = intval($_POST['message_id']);
    
    try {
        $delete_sql = "DELETE FROM kontaktet WHERE id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $message_id);
        
        if ($stmt->execute()) {
            $success_message = "Mesazhi u fshi me sukses!";
        } else {
            $error_message = "Gabim në fshirjen e mesazhit!";
        }
    } catch (Exception $e) {
        $error_message = "Gabim: " . $e->getMessage();
    }
}

// Handle mark as read
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_read'])) {
    $message_id = intval($_POST['message_id']);
    
    try {
        $update_sql = "UPDATE kontaktet SET status = 'lexuar' WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("i", $message_id);
        
        if ($stmt->execute()) {
            $success_message = "Mesazhi u shënua si i lexuar!";
        }
    } catch (Exception $e) {
        $error_message = "Gabim: " . $e->getMessage();
    }
}

// Get all messages
$messages_data = [];
try {
    $messages_sql = "SELECT * FROM kontaktet ORDER BY data_krijimit DESC";
    $stmt = $conn->prepare($messages_sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $messages_data = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $error_message = "Gabim në leximin e mesazheve: " . $e->getMessage();
}

// Count unread messages
$unread_count = 0;
try {
    $count_sql = "SELECT COUNT(*) as count FROM kontaktet WHERE status = 'i_ri'";
    $stmt = $conn->prepare($count_sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $unread_count = $result->fetch_assoc()['count'];
} catch (Exception $e) {
    // Handle error
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesazhet - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body style="background: linear-gradient(135deg, #e0d1b6 0%, #f0e8d8 100%); min-height: 100vh; margin: 0;">
    
    <!-- Header with Navigation -->
    <header style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); padding: 1rem 0; box-shadow: 0 2px 20px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 100;">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; padding: 0 2rem;">
            <!-- Logo -->
            <div style="display: flex; align-items: center;">
                <i class="fas fa-store" style="color: #c7a76f; font-size: 1.8rem; margin-right: 0.5rem;"></i>
                <h1 style="color: #c7a76f; font-size: 1.8rem; margin: 0; font-weight: 700;">Loli Shop</h1>
            </div>
            
            <!-- Navigation Menu -->
            <nav style="display: flex; gap: 2rem; align-items: center;">
                <a href="../index.php" style="color: #8a7a68; text-decoration: none; font-weight: 500; transition: color 0.3s ease;">Ballina</a>
                <a href="../products.php" style="color: #8a7a68; text-decoration: none; font-weight: 500; transition: color 0.3s ease;">Produktet</a>
                <a href="../categories.php" style="color: #8a7a68; text-decoration: none; font-weight: 500; transition: color 0.3s ease;">Kategorite</a>
                <a href="../contact.php" style="color: #8a7a68; text-decoration: none; font-weight: 500; transition: color 0.3s ease;">Kontakti</a>
            </nav>
            
            <!-- Admin Buttons -->
            <div style="display: flex; gap: 0.8rem;">
                <a href="index.php" style="background: linear-gradient(135deg, #8a7a68 0%, #6d5a4f 100%); color: white; padding: 0.7rem 1.5rem; border-radius: 25px; text-decoration: none; font-weight: 600; transition: all 0.3s ease; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-arrow-left"></i>
                    Admin Panel
                </a>
                <a href="../logout.php" style="background: linear-gradient(135deg, #8a7a68 0%, #6d5a4f 100%); color: white; padding: 0.7rem 1.5rem; border-radius: 25px; text-decoration: none; font-weight: 600; transition: all 0.3s ease; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-sign-out-alt"></i>
                    Dalje
                </a>
            </div>
        </div>
    </header>

    <div style="max-width: 1200px; margin: 0 auto; padding: 2rem;">
        
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 2rem;">
            <h1 style="color: #4d4b49; font-size: 2.5rem; margin-bottom: 0.5rem;">
                <i class="fas fa-envelope" style="color: #c7a76f; margin-right: 0.5rem;"></i>
                Mesazhet e Kontaktit
            </h1>
            <p style="color: #6c757d; font-size: 1.1rem;">
                <?php echo $unread_count; ?> mesazhe të palexuara | <?php echo count($messages_data); ?> total
            </p>
        </div>

        <!-- Success/Error Messages -->
        <?php if (!empty($success_message)): ?>
            <div style="background: #4caf50; color: white; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; text-align: center;">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div style="background: #ef5350; color: white; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; text-align: center;">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Messages List -->
        <div style="background: white; border-radius: 15px; padding: 2rem; box-shadow: 0 8px 32px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <?php if (!empty($messages_data)): ?>
                
                <?php foreach ($messages_data as $message): ?>
                    <div style="background: <?php echo $message['status'] == 'i_ri' ? 'linear-gradient(135deg, #faf8f3 0%, #f5f0e8 100%)' : '#f9f9f9'; ?>; border: 1px solid <?php echo $message['status'] == 'i_ri' ? '#c7a76f' : '#ddd'; ?>; border-radius: 10px; padding: 1.5rem; margin-bottom: 1rem; border-left: 4px solid <?php echo $message['status'] == 'i_ri' ? '#c7a76f' : '#4caf50'; ?>; box-shadow: <?php echo $message['status'] == 'i_ri' ? '0 4px 15px rgba(199, 167, 111, 0.15)' : '0 2px 8px rgba(0,0,0,0.1)'; ?>;">
                        
                        <!-- Message Header -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <div>
                                <h3 style="color: <?php echo $message['status'] == 'i_ri' ? '#8b6914' : '#333'; ?>; margin: 0; font-size: 1.2rem; font-weight: <?php echo $message['status'] == 'i_ri' ? '600' : '600'; ?>;">
                                    <?php echo htmlspecialchars($message['emri']); ?>
                                    <?php if ($message['status'] == 'i_ri'): ?>
                                        <span style="background: linear-gradient(135deg, #c7a76f 0%, #d4b896 100%); color: white; padding: 0.3rem 0.6rem; border-radius: 15px; font-size: 0.7rem; margin-left: 0.5rem; box-shadow: 0 2px 8px rgba(199, 167, 111, 0.3); animation: pulse 2s infinite;">I RI</span>
                                    <?php endif; ?>
                                </h3>
                                <p style="color: <?php echo $message['status'] == 'i_ri' ? '#a0845c' : '#666'; ?>; margin: 0.2rem 0 0 0; font-size: 0.9rem; font-weight: <?php echo $message['status'] == 'i_ri' ? '500' : '400'; ?>;">
                                    <?php echo htmlspecialchars($message['email']); ?> | 
                                    <?php echo date('d/m/Y H:i', strtotime($message['data_krijimit'])); ?>
                                </p>
                            </div>
                            <div>
                                <?php if ($message['status'] == 'i_ri'): ?>
                                    <form method="POST" style="display: inline-block; margin-right: 0.5rem;">
                                        <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                        <button type="submit" name="mark_read" style="background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%); border: none; color: white; padding: 0.6rem 1.2rem; border-radius: 8px; cursor: pointer; font-size: 0.8rem; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 3px 10px rgba(76, 175, 80, 0.3);">
                                            <i class="fas fa-check"></i> Shëno si lexuar
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <!-- Reply Button -->
                                <button onclick="openReplyModal(<?php echo $message['id']; ?>, '<?php echo htmlspecialchars($message['email'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($message['emri'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($message['subjekti'], ENT_QUOTES); ?>')" 
                                   style="background: linear-gradient(135deg, #2196f3 0%, #64b5f6 100%); color: white; padding: 0.6rem 1.2rem; border-radius: 8px; border: none; cursor: pointer; font-size: 0.8rem; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 3px 10px rgba(33, 150, 243, 0.3); display: inline-block; margin-right: 0.5rem;">
                                    <i class="fas fa-reply"></i> Përgjigju
                                </button>
                                
                                <form method="POST" style="display: inline-block;">
                                    <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                    <button type="submit" name="delete_message" onclick="return confirm('A jeni të sigurt?');" style="background: linear-gradient(135deg, #ef5350 0%, #e57373 100%); border: none; color: white; padding: 0.6rem 1.2rem; border-radius: 8px; cursor: pointer; font-size: 0.8rem; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 3px 10px rgba(239, 83, 80, 0.3);">
                                        <i class="fas fa-trash"></i> Fshi
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Subject -->
                        <h4 style="color: <?php echo $message['status'] == 'i_ri' ? '#8b6914' : '#333'; ?>; margin-bottom: 0.5rem; font-size: 1.1rem; font-weight: <?php echo $message['status'] == 'i_ri' ? '600' : '500'; ?>;">
                            Subjekti: <?php echo htmlspecialchars($message['subjekti']); ?>
                        </h4>

                        <!-- Message Content -->
                        <div style="background: <?php echo $message['status'] == 'i_ri' ? 'linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%)' : '#f8f9fa'; ?>; padding: 1rem; border-radius: 8px; border-left: 4px solid <?php echo $message['status'] == 'i_ri' ? '#c7a76f' : '#c7a76f'; ?>;">
                            <p style="margin: 0; color: <?php echo $message['status'] == 'i_ri' ? '#4e342e' : '#555'; ?>; line-height: 1.6; font-weight: <?php echo $message['status'] == 'i_ri' ? '500' : '400'; ?>;">
                                <?php echo nl2br(htmlspecialchars($message['mesazhi'])); ?>
                            </p>
                        </div>

                        <!-- Show sent replies if any -->
                        <?php if (isset($sent_replies[$message['email']])): ?>
                            <div style="margin-top: 1rem; padding: 1rem; background: #e8f4fd; border-radius: 8px; border-left: 3px solid #2196f3;">
                                <h5 style="color: #1976d2; margin: 0 0 0.8rem 0; font-size: 0.95rem; display: flex; align-items: center;">
                                    <i class="fas fa-reply" style="margin-right: 0.5rem;"></i> 
                                    Përgjigjet e dërguara (<?php echo count($sent_replies[$message['email']]); ?>):
                                </h5>
                                <?php foreach ($sent_replies[$message['email']] as $reply): ?>
                                    <div style="background: white; padding: 0.8rem; border-radius: 6px; margin-bottom: 0.5rem; border-left: 2px solid #2196f3;">
                                        <div style="color: #1976d2; font-weight: 600; margin-bottom: 0.3rem; font-size: 0.9rem;">
                                            📧 <?php echo htmlspecialchars($reply['reply_subject']); ?>
                                        </div>
                                        <div style="color: #424242; line-height: 1.4; font-size: 0.85rem; margin-bottom: 0.5rem;">
                                            <?php echo nl2br(htmlspecialchars($reply['reply_message'])); ?>
                                        </div>
                                        <div style="color: #666; font-size: 0.75rem; text-align: right;">
                                            Dërguar nga: <?php echo htmlspecialchars($reply['admin_user']); ?> | 
                                            <?php echo date('d/m/Y H:i', strtotime($reply['data_dergimit'])); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                
            <?php else: ?>
                <div style="text-align: center; padding: 3rem; color: #666;">
                    <i class="fas fa-inbox" style="font-size: 3rem; color: #ddd; margin-bottom: 1rem;"></i>
                    <h3>Nuk ka mesazhe</h3>
                    <p>Ende nuk ka mesazhe kontakti të dërguara.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- Reply Modal -->
    <div id="replyModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: white; border-radius: 15px; padding: 2rem; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="color: #4d4b49; margin: 0;">
                    <i class="fas fa-reply" style="color: #2196f3; margin-right: 0.5rem;"></i>
                    Përgjigju Mesazhit
                </h2>
                <button onclick="closeReplyModal()" style="background: none; border: none; font-size: 1.5rem; color: #666; cursor: pointer;">×</button>
            </div>

            <form method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; color: #333; font-weight: 600;">Për:</label>
                    <input type="email" name="reply_email" id="replyTo" readonly style="width: 100%; padding: 0.8rem; border: 2px solid #ddd; border-radius: 8px; background: #f9f9f9; color: #666;">
                </div>

                <div>
                    <label style="display: block; margin-bottom: 0.5rem; color: #333; font-weight: 600;">Subjekti:</label>
                    <input type="text" name="reply_subject" id="replySubject" readonly style="width: 100%; padding: 0.8rem; border: 2px solid #ddd; border-radius: 8px; background: #f9f9f9; color: #666;">
                </div>

                <div>
                    <label style="display: block; margin-bottom: 0.5rem; color: #333; font-weight: 600;">Mesazhi:</label>
                    <textarea name="reply_message" id="replyMessage" rows="8" placeholder="Shkruaj përgjigjen tuaj këtu..." style="width: 100%; padding: 0.8rem; border: 2px solid #ddd; border-radius: 8px; resize: vertical; font-family: inherit;" required></textarea>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" onclick="closeReplyModal()" style="background: #6c757d; color: white; padding: 0.8rem 1.5rem; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                        Anulo
                    </button>
                    <button type="submit" name="send_reply" style="background: linear-gradient(135deg, #2196f3 0%, #64b5f6 100%); color: white; padding: 0.8rem 1.5rem; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                        <i class="fas fa-paper-plane"></i> Dërgo
                    </button>
                </div>
            </form>
        </div>
    </div>

    <footer style="background: #4d4b49; color: white; text-align: center; padding: 2rem 0; margin-top: 2rem;">
        <div class="container">
            <p>&copy; 2025 Loli Shop. Të gjitha të drejtat e rezervuara.</p>
        </div>
    </footer>

    <script>
    function openReplyModal(messageId, email, name, subject) {
        document.getElementById('replyTo').value = email;
        document.getElementById('replySubject').value = 'RE: ' + subject;
        document.getElementById('replyMessage').value = `Përshëndetje ${name},\n\nFaleminderit për mesazhin tuaj.\n\nNë lidhje me kërkesën tuaj, ju informojmë se...\n\nPër çdo pyetje shtesë, mos hezitoni të na kontaktoni.\n\nMe respekt,\nLoli Shop Team`;
        
        const modal = document.getElementById('replyModal');
        modal.style.display = 'flex';
        
        // Focus on message textarea and move cursor to middle
        setTimeout(() => {
            const textarea = document.getElementById('replyMessage');
            textarea.focus();
            const lines = textarea.value.split('\n');
            const middleLine = Math.floor(lines.length / 2);
            const position = lines.slice(0, middleLine).join('\n').length;
            textarea.setSelectionRange(position, position);
        }, 100);
    }

    function closeReplyModal() {
        document.getElementById('replyModal').style.display = 'none';
        // Clear form
        document.getElementById('replyMessage').value = '';
    }

    // Close modal when clicking outside
    document.getElementById('replyModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeReplyModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeReplyModal();
        }
    });

    // Auto-close modal after successful submission
    <?php if (!empty($success_message) && strpos($success_message, 'Përgjigja') !== false): ?>
    setTimeout(() => {
        closeReplyModal();
    }, 2000);
    <?php endif; ?>
    </script>

    <style>
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }
    
    nav a:hover {
        color: #c7a76f !important;
    }
    
    header a:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    /* Reply button hover effect */
    a[href^="mailto:"]:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(33, 150, 243, 0.4) !important;
    }
    </style>

</body>
</html>