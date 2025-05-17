<?php
session_start();

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart actions
if (isset($_POST['action'])) {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    
    switch ($_POST['action']) {
        case 'add':
            if (isset($_POST['quantity']) && $product_id > 0) {
                if (isset($_SESSION['cart'][$product_id])) {
                    $_SESSION['cart'][$product_id] += (int)$_POST['quantity'];
                } else {
                    $_SESSION['cart'][$product_id] = (int)$_POST['quantity'];
                }
            }
            break;
            
        case 'remove':
            if ($product_id > 0 && isset($_SESSION['cart'][$product_id])) {
                unset($_SESSION['cart'][$product_id]);
            }
            break;
            
        case 'update':
            if (isset($_POST['quantity']) && $product_id > 0) {
                if ((int)$_POST['quantity'] > 0) {
                    $_SESSION['cart'][$product_id] = (int)$_POST['quantity'];
                } else {
                    unset($_SESSION['cart'][$product_id]);
                }
            }
            break;
    }
}

$servername = "mysql_vm_ip";
$username = "your_user";
$password = "your_password";
$database = "your_database";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully!<br>";

// Query
$sql = "SELECT * FROM Producto";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<div class='products-container'>";
    echo "<h2>Productos</h2>";
    echo "<div class='products-grid'>";
    
    // Output rows
    while($row = $result->fetch_assoc()) {
        $imageUrl = "https://source.unsplash.com/300x300/?" . urlencode($row['nombre']);
        ?>
        <div class="product-card">
            <a href="product_detail.php?id=<?php echo $row['id']; ?>" class="product-link">
                <div class="product-image">
                    <img src="<?php echo $imageUrl; ?>" alt="<?php echo htmlspecialchars($row['nombre']); ?>">
                </div>
                <div class="product-info">
                    <h3><?php echo htmlspecialchars($row['nombre']); ?></h3>
                    <p class="description"><?php echo htmlspecialchars($row['descripcion']); ?></p>
                    <div class="product-details">
                        <span class="price">$<?php echo number_format($row['precio'], 2); ?></span>
                        <span class="stock">Stock: <?php echo $row['stock']; ?></span>
                    </div>
                </div>
            </a>
            <div class="product-actions">
                <form method="POST" class="cart-form">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                    <div class="quantity-wrapper">
                        <input type="number" name="quantity" value="1" min="1" max="<?php echo $row['stock']; ?>" class="quantity-input">
                        <button type="submit" class="add-to-cart-btn">Agregar al carrito</button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
    
    echo "</div></div>";
} else {
    echo "<p class='no-results'>No se encontraron productos</p>";
}

// Get cart items details
$cart_items = [];
$cart_total = 0;

if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $cart_sql = "SELECT * FROM Producto WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($cart_sql);
    $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
    $stmt->execute();
    $cart_result = $stmt->get_result();
    
    while ($row = $cart_result->fetch_assoc()) {
        $cart_items[$row['id']] = $row;
        $cart_total += $row['precio'] * $_SESSION['cart'][$row['id']];
    }
}

$conn->close();
?>

<!-- Shopping Cart -->
<div class="cart-container">
    <div class="cart-header">
        <h2>Carrito de Compras</h2>
        <span><?php echo count($_SESSION['cart']); ?> items</span>
    </div>
    
    <div class="cart-items">
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <p>Tu carrito está vacío</p>
            </div>
        <?php else: ?>
            <?php foreach ($cart_items as $id => $item): ?>
                <div class="cart-item">
                    <div class="cart-item-image">
                        <img src="https://source.unsplash.com/100x100/?" . urlencode($item['nombre']) . "" alt="<?php echo htmlspecialchars($item['nombre']); ?>">
                    </div>
                    <div class="cart-item-info">
                        <h4><?php echo htmlspecialchars($item['nombre']); ?></h4>
                        <p class="price">$<?php echo number_format($item['precio'], 2); ?></p>
                        <div class="cart-item-quantity">
                            <form method="POST" class="update-form">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                                <input type="number" name="quantity" value="<?php echo $_SESSION['cart'][$id]; ?>" 
                                       min="1" max="<?php echo $item['stock']; ?>" class="quantity-input">
                                <button type="submit" class="quantity-btn">✓</button>
                            </form>
                            <form method="POST" class="remove-form">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                                <button type="submit" class="remove-btn">×</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($cart_items)): ?>
        <div class="cart-total">
            <div class="subtotal">
                <span>Subtotal:</span>
                <span>$<?php echo number_format($cart_total, 2); ?></span>
            </div>
            <div class="total">
                <span>Total:</span>
                <span>$<?php echo number_format($cart_total, 2); ?></span>
            </div>
        </div>
        <button class="checkout-btn">Proceder al pago</button>
    <?php endif; ?>
</div>

<style>
    .products-container {
        max-width: 1200px;
        margin: 20px auto;
        padding: 20px;
    }
    .products-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }
    .product-card {
        position: relative;
        display: flex;
        flex-direction: column;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
        width: calc(33.33% - 20px);
    }
    .product-link {
        text-decoration: none;
        color: inherit;
        flex-grow: 1;
        padding: 15px;
    }
    .product-actions {
        padding: 15px;
        border-top: 1px solid #eee;
        background: #f8f9fa;
    }
    .quantity-wrapper {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    .quantity-input {
        width: 60px;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-align: center;
    }
    .add-to-cart-btn {
        flex-grow: 1;
        padding: 8px 15px;
        background: #2ecc71;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
        transition: background 0.3s ease;
    }
    .add-to-cart-btn:hover {
        background: #27ae60;
    }
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .product-image {
        position: relative;
        padding-top: 100%;
        overflow: hidden;
    }
    .product-image img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    .product-card:hover .product-image img {
        transform: scale(1.05);
    }
    .product-info {
        padding: 15px 0;
    }
    .product-info h3 {
        margin: 0 0 10px 0;
        font-size: 1.2em;
        color: #333;
    }
    .description {
        color: #666;
        font-size: 0.9em;
        margin-bottom: 10px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .product-details {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .price {
        color: #2ecc71;
        font-weight: bold;
        font-size: 1.2em;
    }
    .stock {
        color: #666;
        font-size: 0.9em;
    }
    .no-results {
        text-align: center;
        color: #666;
        padding: 20px;
    }
    
    /* Cart styles */
    .cart-container {
        position: fixed;
        right: 20px;
        top: 20px;
        width: 350px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        padding: 20px;
        z-index: 1000;
        max-height: 80vh;
        display: flex;
        flex-direction: column;
    }
    
    .cart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 15px;
        border-bottom: 2px solid #eee;
        margin-bottom: 15px;
    }
    
    .cart-header h2 {
        margin: 0;
        color: #333;
        font-size: 1.5em;
    }
    
    .cart-items {
        flex-grow: 1;
        overflow-y: auto;
        padding-right: 10px;
        margin-bottom: 15px;
    }
    
    .cart-item {
        display: flex;
        gap: 15px;
        padding: 15px 0;
        border-bottom: 1px solid #eee;
    }
    
    .cart-item-image {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .cart-item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .cart-item-info {
        flex-grow: 1;
    }
    
    .cart-item-info h4 {
        margin: 0 0 5px 0;
        font-size: 1em;
        color: #333;
    }
    
    .cart-item-info .price {
        color: #2ecc71;
        font-weight: bold;
        margin: 5px 0;
    }
    
    .cart-item-quantity {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 10px;
    }
    
    .quantity-input {
        width: 50px;
        padding: 5px;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-align: center;
    }
    
    .quantity-btn {
        background: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 5px 10px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .quantity-btn:hover {
        background: #e9ecef;
    }
    
    .remove-btn {
        color: #e74c3c;
        background: none;
        border: none;
        cursor: pointer;
        padding: 5px;
        font-size: 1.2em;
        transition: color 0.3s ease;
    }
    
    .remove-btn:hover {
        color: #c0392b;
    }
    
    .cart-total {
        padding: 15px 0;
        border-top: 2px solid #eee;
        margin-top: auto;
    }
    
    .subtotal, .total {
        display: flex;
        justify-content: space-between;
        margin: 5px 0;
    }
    
    .total {
        font-weight: bold;
        font-size: 1.2em;
        color: #333;
    }
    
    .checkout-btn {
        width: 100%;
        padding: 12px;
        background: #2ecc71;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 1.1em;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s ease;
        margin-top: 10px;
    }
    
    .checkout-btn:hover {
        background: #27ae60;
    }
    
    /* Scrollbar styling */
    .cart-items::-webkit-scrollbar {
        width: 8px;
    }
    
    .cart-items::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .cart-items::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    
    .cart-items::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    /* Empty cart message */
    .empty-cart {
        text-align: center;
        padding: 20px;
        color: #666;
    }
    
    /* Responsive cart */
    @media (max-width: 768px) {
        .cart-container {
            width: 100%;
            height: 100%;
            top: 0;
            right: 0;
            border-radius: 0;
            max-height: none;
        }
    }
</style>
