<header class="header">
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">
                <i class="fas fa-store"></i>
                Loli Shop
            </a>
            
            <ul class="nav-links">
                <li><a href="index.php">Ballina</a></li>
                
                <li><a href="produktet.php">Produktet</a></li>
                <li><a href="kategorite.php">Kategoritë</a></li>
                <li><a href="contact.php">Kontakti</a></li>
            </ul>
            
            <div class="nav-actions">
                <?php if (isLoggedIn()): ?>
                    <a href="cart.php" class="cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count"><?php echo getCartItemCount(); ?></span>
                    </a>
                    <div class="user-menu">
                        <span>Përshëndetje, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>
                        <div class="dropdown">
                            <?php if (isAdmin()): ?>
                                <a href="admin/"><i class="fas fa-cog"></i> Admin</a>
                            <?php endif; ?>
                            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Dalje</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline">Hyrje</a>
                    <a href="register.php" class="btn btn-primary">Regjistrohu</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>
