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
            <form method="POST" class="cart-form">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                <input type="number" name="quantity" value="1" min="1" max="<?php echo $row['stock']; ?>" class="quantity-input">
                <button type="submit" class="buy-button">Agregar al carrito</button>
            </form>
        </div>
        <?php
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
    .product-card {
        position: relative;
        display: flex;
        flex-direction: column;
    }
    
    .product-link {
        text-decoration: none;
        color: inherit;
        flex-grow: 1;
    }
    
    .cart-form {
        margin-top: auto;
        padding: 10px;
        border-top: 1px solid #eee;
    }
    
    .quantity-input {
        width: 60px;
        padding: 5px;
        margin-right: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    
    .buy-button {
        width: calc(100% - 70px);
        padding: 8px;
        background: #2ecc71;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    
    .buy-button:hover {
        background: #27ae60;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
</style>";

$conn->close();
?>
