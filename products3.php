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
    echo "<table class='products-table'>";
    
    // Output column headers
    echo "<thead><tr>";
    while ($fieldinfo = $result->fetch_field()) {
        echo "<th>" . htmlspecialchars($fieldinfo->name) . "</th>";
    }
    echo "</tr></thead><tbody>";

    // Output rows
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $key => $cell) {
            // Format price column
            if ($key === 'precio') {
                echo "<td class='price'>$" . number_format($cell, 2) . "</td>";
            }
            // Format date column
            else if ($key === 'fecha_creacion') {
                echo "<td class='date'>" . date('d/m/Y H:i', strtotime($cell)) . "</td>";
            }
            else {
                echo "<td>" . htmlspecialchars($cell) . "</td>";
            }
        }
        echo "</tr>";
    }
    
    echo "</tbody></table></div>";
} else {
    echo "<p class='no-results'>No se encontraron productos</p>";
}

// Add CSS styles
echo "<style>
    .products-container {
        max-width: 1200px;
        margin: 20px auto;
        padding: 20px;
    }
    .products-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background: white;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }
    .products-table th {
        background: #f4f4f4;
        padding: 12px;
        text-align: left;
        font-weight: bold;
    }
    .products-table td {
        padding: 12px;
        border-bottom: 1px solid #ddd;
    }
    .products-table tr:hover {
        background: #f9f9f9;
    }
    .price {
        font-weight: bold;
        color: #2ecc71;
    }
    .date {
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
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #eee;
    }
    
    .cart-item-info {
        flex-grow: 1;
        margin-right: 15px;
    }
    
    .cart-item-info h4 {
        margin: 0 0 5px 0;
        color: #333;
        font-size: 1.1em;
    }
    
    .cart-item-info p {
        margin: 0;
        color: #2ecc71;
        font-weight: bold;
    }
    
    .cart-item-quantity {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .cart-item-quantity input[type='number'] {
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
        text-align: right;
        font-size: 1.2em;
        font-weight: bold;
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
</style>";

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
                    <div class="cart-item-info">
                        <h4><?php echo htmlspecialchars($item['nombre']); ?></h4>
                        <p>$<?php echo number_format($item['precio'], 2); ?></p>
                    </div>
                    <div class="cart-item-quantity">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                            <input type="number" name="quantity" value="<?php echo $_SESSION['cart'][$id]; ?>" 
                                   min="1" max="<?php echo $item['stock']; ?>">
                            <button type="submit" class="quantity-btn">✓</button>
                        </form>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                            <button type="submit" class="remove-btn">×</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($cart_items)): ?>
        <div class="cart-total">
            Total: $<?php echo number_format($cart_total, 2); ?>
        </div>
        <button class="checkout-btn">Proceder al pago</button>
    <?php endif; ?>
</div>
