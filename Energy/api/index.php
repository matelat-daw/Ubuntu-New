<?php
// api/index.php

// Cabeceras comunes
header("Content-Type: application/json; charset=UTF-8");
// CORS con credenciales: reflejar Origin y permitir cookies
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if ($origin) {
    header("Access-Control-Allow-Origin: " . $origin);
    header("Vary: Origin");
} else {
    // Sin cabecera Origin, mismo origen; no establecer wildcard si se usan credenciales
    header("Access-Control-Allow-Origin: http://localhost");
}
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once 'config/database.php';
require_once 'routes/api.php';

// El router se ejecuta desde routes/api.php
?>
