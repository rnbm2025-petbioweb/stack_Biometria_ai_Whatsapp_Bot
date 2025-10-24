<?php

/*   este primer fragmento causaba el error arriba de sesion star  revisar en los demas archivos

// 🔐 Sesión segura compartida entre subdominios
ini_set('session.cookie_domain', '.siac2025.com');
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_samesite', 'None');
session_start();   */

// ✅ Solo conexión a BD aquí. Nada de sesiones.
//host = 'localhost';
//$db   = 'db__produccion_petbio_segura_2025';
//$user = 'tu_usuario_bd';
//$pass = 'tu_contraseña_bd';
$host = 'mysql_petbio_secure'; // o '127.0.0.1' si MySQL está en la misma máquina fuera de Docker
$db   = 'db__produccion_petbio_segura_2025';
$user = 'root';
$pass = 'R00t_Segura_2025!';
//$charset = 'utf8mb4';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error en la conexión: " . $e->getMessage());
}
?>
