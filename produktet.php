<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$kategoria = isset($_GET['kategoria']) ? (int)$_GET['kategoria'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Build WHERE clause
$where_conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where_conditions[] = "(p.emri LIKE ? OR p.pershkrimi LIKE ?)";
    $search_param = '%' . $search . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if ($kategoria > 0) {
    $where_conditions[] = "p.id_kategoria = ?";
    $params[] = $kategoria;
    $types .= 'i';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Build ORDER BY clause
$order_by = 'ORDER BY ';
switch ($sort) {
    case 'oldest':
        $order_by .= 'p.id_produkti ASC';
        break;
    case 'price_low':
        $order_by .= 'p.cmimi ASC';
        break;
    case 'price_high':
        $order_by .= 'p.cmimi DESC';
        break;
    case 'name':
        $order_by .= 'p.emri ASC';
        break;
    default: // newest
        $order_by .= 'p.id_produkti DESC';
        break;
}

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM produktet p 
              LEFT JOIN kategorite k ON p.id_kategoria = k.id_kategoria 
              $where_clause";
if (!empty($params)) {
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param($types, ...$params);
    $count_stmt->execute();
    $total_result = $count_stmt->get_result();
} else {
    $total_result = $conn->query($count_sql);
}
$total_products = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_products / $per_page);

// Get products with pagination
$sql = "SELECT p.*, k.emri as kategoria FROM produktet p 
        LEFT JOIN kategorite k ON p.id_kategoria = k.id_kategoria 
        $where_clause $order_by LIMIT $per_page OFFSET $offset";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

$produktet = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $produktet[] = $row;
    }
}

// Get categories for filter
$kategorite_sql = "SELECT * FROM kategorite ORDER BY emri";
$kategorite_result = $conn->query($kategorite_sql);
$kategorite = [];
if ($kategorite_result) {
    while ($row = $kategorite_result->fetch_assoc()) {
        $kategorite[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produktet - Loli Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

<main>
    <!-- Hero Section for Products -->
    <section style="background: linear-gradient(135deg, #c7a76f 0%, #4d4b49 100%); color: white; padding: 3rem 0; text-align: center;">
        <div class="container">
            <h1 style="font-size: 2.5rem; margin-bottom: 1rem;">Të gjithë Produktet</h1>
            <p style="font-size: 1.1rem; opacity: 0.9;">Zbuloni koleksionin tonë të plotë</p>
        </div>
    </section>

    <!-- Filter Section -->
    <section style="padding: 2rem 0; background: #e0d1b6;">
        <div class="container">
            <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                <form method="GET" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 1.5rem; align-items: end;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: #4d4b49; font-weight: 500;">Kërko Produktet</label>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                               placeholder="Emri ose përshkrimi..." 
                               style="width: 100%; padding: 0.75rem; border: 2px solid #c7a76f; border-radius: 6px; font-size: 1rem;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: #4d4b49; font-weight: 500;">Kategoria</label>
                        <select name="kategoria" style="width: 100%; padding: 0.75rem; border: 2px solid #c7a76f; border-radius: 6px; font-size: 1rem;">
                            <option value="0">Të gjitha kategorit</option>
                            <?php foreach ($kategorite as $kat): ?>
                                <option value="<?= $kat['Id_kategoria'] ?>" <?= $kategoria == $kat['Id_kategoria'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kat['emri']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: #4d4b49; font-weight: 500;">Rendit sipas</label>
                        <select name="sort" style="width: 100%; padding: 0.75rem; border: 2px solid #c7a76f; border-radius: 6px; font-size: 1rem;">
                            <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Më të rejat</option>
                            <option value="oldest" <?= $sort == 'oldest' ? 'selected' : '' ?>>Më të vjetrat</option>
                            <option value="price_low" <?= $sort == 'price_low' ? 'selected' : '' ?>>Çmimi (ulët-lartë)</option>
                            <option value="price_high" <?= $sort == 'price_high' ? 'selected' : '' ?>>Çmimi (lartë-ulët)</option>
                            <option value="name" <?= $sort == 'name' ? 'selected' : '' ?>>Alfabetik</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Filtro</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products-section">
        <div class="container">
            <!-- Products Grid -->
            <?php if (empty($produktet)): ?>
                <div class="no-products">
                    <i class="fas fa-box-open" style="font-size: 3rem; color: #4d4b49; margin-bottom: 1rem;"></i>
                    <h3>Nuk u gjetën produkte</h3>
                    <p>Provo të ndryshosh kriteret e kërkimit.</p>
                    <a href="produktet.php" class="btn btn-primary">Shiko të gjithë produktet</a>
                </div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($produktet as $produkti): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <?php if (!empty($produkti['foto'])): ?>
                                    <img src="assets/images/<?= htmlspecialchars($produkti['foto']) ?>" alt="<?= htmlspecialchars($produkti['emri']) ?>">
                                <?php else: ?>
                                    <div class="placeholder-image">
                                        <i class="fas fa-image" style="font-size: 2.5rem; opacity: 0.8; z-index: 2; position: relative; margin-bottom: 0.5rem;"></i>
                                        <span style="font-size: 0.9rem; z-index: 2; position: relative; font-weight: 600; text-shadow: 0 1px 2px rgba(0,0,0,0.1);"><?= htmlspecialchars($produkti['emri']) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-info">
                                <h3><?= htmlspecialchars($produkti['emri']) ?></h3>
                                <?php if (!empty($produkti['kategoria'])): ?>
                                    <p class="category"><?= htmlspecialchars($produkti['kategoria']) ?></p>
                                <?php endif; ?>
                                <p class="price">€<?= number_format($produkti['cmimi'], 2) ?></p>
                                <?php if (!empty($produkti['pershkrimi'])): ?>
                                    <p class="description"><?= htmlspecialchars(substr($produkti['pershkrimi'], 0, 100)) ?>...</p>
                                <?php endif; ?>
                                
                                <div class="product-actions">
                                    <a href="produkt.php?id=<?= $produkti['Id_produkti'] ?>" class="btn btn-secondary">Detajet</a>
                                    <button class="btn btn-primary add-to-cart" data-id="<?= $produkti['Id_produkti'] ?>">
                                        <i class="fas fa-shopping-cart"></i> Shto në Shportë
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div style="text-align: center; margin-top: 3rem;">
                        <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: white; padding: 1rem 2rem; border-radius: 50px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                            <!-- Previous page -->
                            <?php if ($page > 1): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" 
                                   class="btn btn-secondary btn-small">
                                    <i class="fas fa-chevron-left"></i> Mëparshme
                                </a>
                            <?php endif; ?>

                            <!-- Page numbers -->
                            <?php
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);
                            
                            if ($start_page > 1): 
                            ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" 
                                   class="btn btn-outline" style="padding: 0.5rem 0.75rem;">1</a>
                                <?php if ($start_page > 2): ?>
                                    <span style="padding: 0.5rem;">...</span>
                                <?php endif; ?>
                            <?php 
                            endif; 
                            
                            for ($i = $start_page; $i <= $end_page; $i++): 
                            ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                                   class="btn <?= $i == $page ? 'btn-primary' : 'btn-outline' ?>" 
                                   style="padding: 0.5rem 0.75rem;"><?= $i ?></a>
                            <?php 
                            endfor; 
                            
                            if ($end_page < $total_pages): 
                            ?>
                                <?php if ($end_page < $total_pages - 1): ?>
                                    <span style="padding: 0.5rem;">...</span>
                                <?php endif; ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>" 
                                   class="btn btn-outline" style="padding: 0.5rem 0.75rem;"><?= $total_pages ?></a>
                            <?php 
                            endif; 
                            ?>

                            <!-- Next page -->
                            <?php if ($page < $total_pages): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" 
                                   class="btn btn-secondary btn-small">
                                    Tjetër <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>

<script src="assets/js/script.js"></script>

<style>
/* Mobile responsive styles for products page */
@media (max-width: 768px) {
    .container {
        padding: 0 15px;
    }
    
    /* Hero section mobile */
    section[style*="background: linear-gradient"] h1 {
        font-size: 2rem !important;
    }
    
    /* Filter form mobile */
    form[style*="grid-template-columns"] {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 1rem !important;
    }
    
    form[style*="grid-template-columns"] button {
        width: 100%;
    }
    
    /* Results info mobile */
    div[style*="display: flex; justify-content: space-between"] {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 1rem;
    }
    
    /* Products grid mobile */
    .products-grid {
        grid-template-columns: 1fr !important;
        gap: 1.5rem;
    }
    
    .product-card {
        margin: 0 auto;
        max-width: 350px;
    }
    
    /* Pagination mobile */
    div[style*="display: inline-flex"] {
        flex-wrap: wrap !important;
        justify-content: center !important;
    }
}
</style>

</body>
</html>
