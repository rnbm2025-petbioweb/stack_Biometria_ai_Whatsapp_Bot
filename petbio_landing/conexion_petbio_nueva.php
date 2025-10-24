<?php
// ⚠️ Archivo de conexión a la base de datos (NO manejar sesiones aquí)
//require_once __DIR__ . '/conexion.php';
//require_once '/conexion_petbio_nueva.php';

// Parámetros de conexión
$host     = 'mysql_petbio_secure'; // Nombre del servicio del contenedor MySQL en Docker
$db       = 'db__produccion_petbio_segura_2025';
$user     = 'root';
$pass     = 'R00t_Segura_2025!';
$charset  = 'utf8mb4';

// Crear conexión con MySQL
$conexion = new mysqli($host, $user, $pass, $db);

// Verificar errores de conexión
if ($conexion->connect_error) {
    die("❌ Error en la conexión: " . $conexion->connect_error);
}

// Configurar charset para evitar problemas con acentos y ñ
if (!$conexion->set_charset($charset)) {
    die("❌ Error cargando el conjunto de caracteres $charset: " . $conexion->error);
}
?>
