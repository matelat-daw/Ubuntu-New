<?php
// Definición de rutas de la API
require_once __DIR__ . '/../Router.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../controllers/ProviderController.php';
require_once __DIR__ . '/../controllers/PlanController.php';
require_once __DIR__ . '/../controllers/ContractController.php';

$router = new Router();

// Base dinámica (evita hardcodear rutas)
$apiBase = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

// ===============================
// RUTAS DE AUTENTICACIÓN
// ===============================
$router->post("$apiBase/register", [AuthController::class, 'register']);
$router->post("$apiBase/login", [AuthController::class, 'login']);
$router->post("$apiBase/logout", [AuthController::class, 'logout']);
$router->post("$apiBase/activate", [AuthController::class, 'activateAccount']);
$router->get("$apiBase/auth/validate", [AuthController::class, 'validateToken']);
$router->put("$apiBase/auth/profile", [AuthController::class, 'updateProfile']);

// ===============================
// RUTAS DE ADMINISTRACIÓN
// ===============================
$router->post("$apiBase/admin/create-seller", [AuthController::class, 'createSeller']);
$router->get("$apiBase/admin/contracts", [ContractController::class, 'adminContracts']);

// ===============================
// RUTAS DE USUARIOS
// ===============================
$router->get("$apiBase/users", [UserController::class, 'index']);
$router->get("$apiBase/users/sellers", function() {
    $controller = new UserController();
    $controller->getUsersByRole('seller');
});
$router->get("$apiBase/users/clients", function() {
    $controller = new UserController();
    $controller->getUsersByRole('user');
});
$router->get("$apiBase/users/{id}", [UserController::class, 'show']);
$router->put("$apiBase/users/{id}", [UserController::class, 'update']);
$router->put("$apiBase/users/{id}/password", [UserController::class, 'updatePassword']);
$router->delete("$apiBase/users/{id}", [UserController::class, 'destroy']);
$router->post("$apiBase/users/{id}/role", [UserController::class, 'assignRole']);
$router->delete("$apiBase/users/{id}/role", [UserController::class, 'removeRole']);

// ===============================
// RUTAS DE PROVEEDORES
// ===============================
$router->get("$apiBase/providers", [ProviderController::class, 'index']);
$router->get("$apiBase/providers/{id}", [ProviderController::class, 'show']);
$router->post("$apiBase/providers", [ProviderController::class, 'store']);
$router->put("$apiBase/providers/{id}", [ProviderController::class, 'update']);
$router->delete("$apiBase/providers/{id}", [ProviderController::class, 'destroy']);
$router->get("$apiBase/providers/{id}/plans", [ProviderController::class, 'getPlans']);

// ===============================
// RUTAS DE PLANES
// ===============================
$router->get("$apiBase/plans", [PlanController::class, 'index']);
$router->get("$apiBase/plans/{id}", [PlanController::class, 'show']);
$router->post("$apiBase/plans", [PlanController::class, 'store']);
$router->put("$apiBase/plans/{id}", [PlanController::class, 'update']);
$router->delete("$apiBase/plans/{id}", [PlanController::class, 'destroy']);

// ===============================
// RUTAS DE CONTRATOS
// ===============================
$router->get("$apiBase/contracts", [ContractController::class, 'index']);
$router->get("$apiBase/contracts/my", [ContractController::class, 'myContracts']);
$router->get("$apiBase/contracts/stats", [ContractController::class, 'sellerStats']);
$router->get("$apiBase/contracts/{id}", [ContractController::class, 'show']);
$router->post("$apiBase/contracts", [ContractController::class, 'store']);
$router->put("$apiBase/contracts/{id}", [ContractController::class, 'update']);
$router->delete("$apiBase/contracts/{id}", [ContractController::class, 'destroy']);

// Ejecutar el router
$router->run();
?>
