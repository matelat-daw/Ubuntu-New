<?php
// Test PHP installation
echo "<h1>PHP está funcionando correctamente!</h1>";
echo "<p><strong>Versión de PHP:</strong> " . phpversion() . "</p>";

// Test MySQL connection
echo "<h2>Prueba de conexión a MySQL</h2>";

$host = 'localhost';
$user = 'root';
$password = 'password123'; // Contraseña configurada

try {
    $conn = new mysqli($host, $user, $password);
    
    if ($conn->connect_error) {
        echo "<p style='color: red;'>❌ Error de conexión: " . $conn->connect_error . "</p>";
    } else {
        echo "<p style='color: green;'>✅ Conexión a MySQL exitosa!</p>";
        echo "<p><strong>Versión de MySQL:</strong> " . $conn->server_info . "</p>";
        $conn->close();
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// Show PHP info
echo "<hr>";
echo "<h2>Extensiones PHP cargadas</h2>";
echo "<ul>";
$extensions = get_loaded_extensions();
sort($extensions);
foreach ($extensions as $ext) {
    echo "<li>$ext</li>";
}
echo "</ul>";

// Check for important Moodle extensions
echo "<hr>";
echo "<h2>Extensiones importantes para Moodle</h2>";
$required = ['mysqli', 'mbstring', 'curl', 'zip', 'xml', 'gd', 'intl', 'soap'];
foreach ($required as $ext) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? '✅' : '❌';
    $color = $loaded ? 'green' : 'red';
    echo "<p style='color: $color;'>$status $ext</p>";
}
?>
