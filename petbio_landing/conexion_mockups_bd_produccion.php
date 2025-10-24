<?php
// ⚠️ NO MANEJAR SESSIONES AQUÍ. Solo conexión a la BD.

$host = 'mysql_petbio_secure'; // o 'mysql_petbio_secure' si usas Docker
$db   = 'db__produccion_petbio_segura_2025';
$user = 'root';
$pass = 'R00t_Segura_2025!';
$charset = 'utf8mb4';

// ✅ Crear conexión
$conexion = new mysqli($host, $user, $pass, $db);

// ✅ Verificar errores
if ($conexion->connect_error) {
    die("❌ Error en la conexión: " . $conexion->connect_error);
}

// ✅ Charset
$conexion->set_charset($charset);
