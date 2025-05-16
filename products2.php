<?php
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
    echo "<div class='landing-container'>";
    echo "<h1>Nuestros Productos</h1>";
    echo "<div class='products-grid'>";
    
    while($row = $result->fetch_assoc()) {
        // Generate a placeholder image URL based on product name
        $imageUrl = "https://source.unsplash.com/300x300/?" . urlencode($row['nombre']);
        
        echo "<a href='product_detail.php?id=" . $row['id'] . "' class='product-card-link'>";
        echo "<div class='product-card'>";
        echo "<div class='product-image'>";
        echo "<img src='{$imageUrl}' alt='" . htmlspecialchars($row['nombre']) . "'>";
        echo "</div>";
        echo "<div class='product-info'>";
        echo "<h3>" . htmlspecialchars($row['nombre']) . "</h3>";
        echo "<p class='description'>" . htmlspecialchars($row['descripcion']) . "</p>";
        echo "<div class='product-details'>";
        echo "<span class='price'>$" . number_format($row['precio'], 2) . "</span>";
        echo "<span class='stock'>Stock: " . $row['stock'] . "</span>";
        echo "</div>";
        echo "<button class='buy-button'>Ver Detalles</button>";
        echo "</div>";
        echo "</div>";
        echo "</a>";
    }
    
    echo "</div></div>";
} else {
    echo "<p class='no-results'>No se encontraron productos</p>";
}

// Add CSS styles
echo "<style>
    .landing-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        font-family: 'Arial', sans-serif;
    }
    
    h1 {
        text-align: center;
        color: #333;
        margin-bottom: 40px;
    }
    
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 30px;
        padding: 20px;
    }
    
    .product-card-link {
        text-decoration: none;
        color: inherit;
        display: block;
    }
    
    .product-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
        overflow: hidden;
        height: 100%;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .product-image {
        width: 100%;
        height: 200px;
        overflow: hidden;
    }
    
    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .product-card:hover .product-image img {
        transform: scale(1.05);
    }
    
    .product-info {
        padding: 20px;
    }
    
    .product-info h3 {
        margin: 0 0 10px 0;
        color: #333;
        font-size: 1.2em;
    }
    
    .description {
        color: #666;
        font-size: 0.9em;
        margin-bottom: 15px;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .product-details {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .price {
        font-size: 1.3em;
        font-weight: bold;
        color: #2ecc71;
    }
    
    .stock {
        color: #666;
        font-size: 0.9em;
    }
    
    .buy-button {
        width: 100%;
        padding: 12px;
        background: #2ecc71;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
        transition: background 0.3s ease;
    }
    
    .buy-button:hover {
        background: #27ae60;
    }
    
    .no-results {
        text-align: center;
        color: #666;
        padding: 20px;
    }
    
    @media (max-width: 768px) {
        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 10px;
        }
    }
</style>";

$conn->close();
?>
