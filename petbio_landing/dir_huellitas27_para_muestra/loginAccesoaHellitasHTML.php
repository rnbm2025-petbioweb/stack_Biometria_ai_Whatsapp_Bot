<?php
// ✅ Mostrar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ✅ Buffer para evitar problemas de header
ob_start();

// ✅ Compartir cookie de sesión entre subdominios
ini_set('session.cookie_domain', '.siac2025.com');
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_samesite', 'None');
session_start();

// ✅ Conexión segura
$conexion = new mysqli("mysql_petbio_secure", "root", "R00t_Segura_2025!", "db__produccion_petbio_segura_2025");
if ($conexion->connect_error) {
    die("❌ Error de conexión: " . $conexion->connect_error);
}

// ✅ Recolección de datos
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// ✅ Consulta segura
$query = "SELECT id_usuario, username, apellidos_usuario, password FROM registro_usuario WHERE email = ?";
$stmt = $conexion->prepare($query);
if (!$stmt) {
    die("❌ Error al preparar la consulta: " . $conexion->error);
}
$stmt->bind_param("s", $email);
$stmt->execute();
$resultado = $stmt->get_result();

if ($usuario = $resultado->fetch_assoc()) {
    $hash = $usuario['password'];

    if (password_verify($password, $hash) || $password === $hash) {
        // 👉 Convertir a hash si era texto plano
        if (!preg_match('/^\$2[ayb]\$/', $hash)) {
            $nuevoHash = password_hash($password, PASSWORD_DEFAULT);
            $update = $conexion->prepare("UPDATE registro_usuario SET password = ? WHERE id_usuario = ?");
            $update->bind_param("si", $nuevoHash, $usuario['id_usuario']);
            $update->execute();
            $update->close();
        }

        // 👉 Guardar sesión
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['nombre'] = $usuario['username'];
        $_SESSION['apellidos'] = $usuario['apellidos_usuario'];

        // ✅ Redirigir
        header("Location: https://petbio.siac2025.com/identidad_rubm.php");
        exit();
    } else {
        $_SESSION['login_error'] = "❌ Contraseña incorrecta.";
    }
} else {
    $_SESSION['login_error'] = "❌ Usuario no encontrado.";
}

// 🔁 Volver al login
header("Location: loginHuellitas27.php");
exit();

ob_end_flush(); // Finaliza el buffer
