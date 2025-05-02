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
    echo "<h2>Productos: </h2>";
    echo "<table border='1' cellpadding='10'>";
    
    // Output column headers
    echo "<tr>";
    while ($fieldinfo = $result->fetch_field()) {
        echo "<th>" . htmlspecialchars($fieldinfo->name) . "</th>";
    }
    echo "</tr>";

    // Output rows
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $cell) {
            echo "<td>" . htmlspecialchars($cell) . "</td>";
        }
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "0 results";
}

$conn->close();
?>
