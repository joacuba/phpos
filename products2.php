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
</style>";

$conn->close();
?>
