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

// Get cart items details
$cart_items = [];
$cart_total = 0;
if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $cart_sql = "SELECT * FROM Producto WHERE id IN (" . implode(',', $ids) . ")";
    $cart_result = $conn->query($cart_sql);
    while ($row = $cart_result->fetch_assoc()) {
        $cart_items[$row['id']] = $row;
        $cart_total += $row['precio'] * $_SESSION['cart'][$row['id']];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos</title>
    <style>
        /* Existing styles */
        .landing-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            font-family: 'Arial', sans-serif;
        }
        
        /* Cart styles */
        .cart-container {
            position: fixed;
            right: 20px;
            top: 20px;
            width: 300px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            z-index: 1000;
        }
        
        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .cart-items {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .cart-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .cart-item-info {
            flex-grow: 1;
            margin-right: 10px;
        }
        
        .cart-item-quantity {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .quantity-btn {
            background: #f0f0f0;
            border: none;
            border-radius: 3px;
            padding: 2px 8px;
            cursor: pointer;
        }
        
        .remove-btn {
            color: #e74c3c;
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
        }
        
        .cart-total {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #eee;
            text-align: right;
            font-weight: bold;
        }
        
        .checkout-btn {
            width: 100%;
            padding: 10px;
            background: #2ecc71;
            color: white;
            border: none;
            border-radius: 5px;
            margin-top: 10px;
            cursor: pointer;
        }
        
        .checkout-btn:hover {
            background: #27ae60;
        }
    </style>
</head>
<body>
    <!-- Shopping Cart -->
    <div class="cart-container">
        <div class="cart-header">
            <h2>Carrito</h2>
            <span><?php echo count($_SESSION['cart']); ?> items</span>
        </div>
        
        <div class="cart-items">
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
                                   min="1" max="<?php echo $item['stock']; ?>" style="width: 50px;">
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
        </div>
        
        <div class="cart-total">
            Total: $<?php echo number_format($cart_total, 2); ?>
        </div>
        
        <button class="checkout-btn">Proceder al pago</button>
    </div>

    <!-- Products Grid -->
    <div class="landing-container">
        <h1>Nuestros Productos</h1>
        <div class="products-grid">
            <?php
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $imageUrl = "https://source.unsplash.com/300x300/?" . urlencode($row['nombre']);
                    ?>
                    <div class="product-card">
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
                            <form method="POST">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                <input type="number" name="quantity" value="1" min="1" max="<?php echo $row['stock']; ?>" style="width: 60px; margin-bottom: 10px;">
                                <button type="submit" class="buy-button">Agregar al carrito</button>
                            </form>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
    </div>

    <script>
        // Add smooth scrolling for cart
        document.querySelectorAll('.quantity-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                this.closest('form').submit();
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
