<?php
// Configuración de conexión a base de datos
session_start();

$host = 'localhost';
$db   = 'energy';
$user = 'root';

$pass = 'Anubis@68';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Error de conexión a la base de datos',
        'error' => $e->getMessage() // Solo para desarrollo, quitar en producción
    ]);
    exit;
}
?>
