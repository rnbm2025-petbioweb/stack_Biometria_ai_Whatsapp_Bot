<?php
// index.php - Router principal para petbio.siac2025.com

// ✅ Sesión segura compartida (activada solo cuando se requiera)
if (php_sapi_name() !== 'cli') {
    ini_set('session.cookie_domain', '.siac2025.com');
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_samesite', 'None');
    session_start();
}

// 💾 Conexión a la BD vía archivo PDO centralizado
require_once __DIR__ . '/../conexion_petbio_nueva.php';

// 🧭 Tabla de rutas amigables
$routes = [
    ''                      => 'dir_formularios/petbio_index.php', // Landing
    'petbio_index.php'      => 'dir_formularios/petbio_index.php',
    'registropetbio.php'    => 'dir_formularios/registropetbio.php',
    'loginpetbio.php'       => 'dir_formularios/loginpetbio.php',
    'identidad_rubm'        => 'dir_modulos/identidad_rubm.php',
    'crear_cita'            => 'dir_controladores/crear_cita.php',
    'historia_clinica'      => 'dir_modulos/historia_clinica.php',
];

// Mockups dinámicos
for ($i = 0; $i <= 9; $i++) {
    $routes["mockup$i.php"] = "dir_formularios/mockup$i.php";
}

// 🧼 Limpieza de URI
$requestUri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// 🧹 Eliminar prefijo tipo /dir_vistas/ si lo hay
$request = preg_replace('#^(.*?/)?dir_vistas/#', '', $requestUri);

// 🚦 Enrutamiento
if (isset($routes[$request]) && file_exists(__DIR__ . '/' . $routes[$request])) {
    require __DIR__ . '/' . $routes[$request];
    exit;
}

// 📄 Si accede a raíz
if ($request === '' && file_exists(__DIR__ . '/' . $routes[''])) {
    require __DIR__ . '/' . $routes[''];
    exit;
}

// ❌ Si no coincide nada
http_response_code(404);
echo "<h1>404 - Página no encontrada</h1>";
?>
