<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root";  // Your database username
$password = "";      // Your database password
$dbname = "tienda";  // Your database name

try {
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Get product ID from URL and validate it
    $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($product_id <= 0) {
        throw new Exception("Invalid product ID");
    }

    // Query for specific product
    $sql = "SELECT * FROM Producto WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $product_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
        
        // Generate image URLs based on product category
        $mainImage = "https://source.unsplash.com/800x600/?" . urlencode($product['nombre']);
        $galleryImages = [
            "https://source.unsplash.com/400x300/?" . urlencode($product['nombre'] . " 1"),
            "https://source.unsplash.com/400x300/?" . urlencode($product['nombre'] . " 2"),
            "https://source.unsplash.com/400x300/?" . urlencode($product['nombre'] . " 3")
        ];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['nombre']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .product-header {
            margin-bottom: 40px;
        }

        .product-title {
            font-size: 2.5em;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .product-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .product-gallery {
            position: relative;
        }

        .main-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .thumbnail-gallery {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .thumbnail {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
            cursor: pointer;
            transition: opacity 0.3s;
        }

        .thumbnail:hover {
            opacity: 0.8;
        }

        .product-info {
            padding: 20px;
        }

        .price {
            font-size: 2em;
            color: #2ecc71;
            margin: 20px 0;
        }

        .stock {
            color: #666;
            margin-bottom: 20px;
        }

        .description {
            margin-bottom: 30px;
            line-height: 1.8;
        }

        .buy-button {
            background: #2ecc71;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 1.2em;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
            width: 100%;
        }

        .buy-button:hover {
            background: #27ae60;
        }

        .product-features {
            margin-top: 40px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .features-list {
            list-style: none;
        }

        .features-list li {
            margin-bottom: 10px;
            padding-left: 20px;
            position: relative;
        }

        .features-list li:before {
            content: "✓";
            color: #2ecc71;
            position: absolute;
            left: 0;
        }

        @media (max-width: 768px) {
            .product-content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="product-header">
            <h1 class="product-title"><?php echo htmlspecialchars($product['nombre']); ?></h1>
        </div>

        <div class="product-content">
            <div class="product-gallery">
                <img src="<?php echo $mainImage; ?>" alt="<?php echo htmlspecialchars($product['nombre']); ?>" class="main-image">
                <div class="thumbnail-gallery">
                    <?php foreach ($galleryImages as $image): ?>
                        <img src="<?php echo $image; ?>" alt="Gallery image" class="thumbnail">
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="product-info">
                <div class="price">$<?php echo number_format($product['precio'], 2); ?></div>
                <div class="stock">Stock disponible: <?php echo $product['stock']; ?> unidades</div>
                <p class="description"><?php echo htmlspecialchars($product['descripcion']); ?></p>
                <button class="buy-button">Comprar Ahora</button>

                <div class="product-features">
                    <h3>Características principales</h3>
                    <ul class="features-list">
                        <?php
                        // Extract features from description
                        $features = explode(',', $product['descripcion']);
                        foreach ($features as $feature) {
                            echo "<li>" . trim($feature) . "</li>";
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Simple image gallery functionality
        document.querySelectorAll('.thumbnail').forEach(thumb => {
            thumb.addEventListener('click', function() {
                document.querySelector('.main-image').src = this.src;
            });
        });
    </script>
</body>
</html>

<?php
    } else {
        echo "<div class='container'><p>Producto no encontrado</p></div>";
    }

} catch (Exception $e) {
    // Log the error and show a user-friendly message
    error_log("Error in product_detail.php: " . $e->getMessage());
    echo "<div class='container'><p>Lo sentimos, ha ocurrido un error. Por favor, intente más tarde.</p></div>";
} finally {
    // Close the database connection
    if (isset($conn)) {
        $conn->close();
    }
}
?> 