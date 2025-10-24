<?php
require_once "conexion.php"; // tu archivo de conexión MySQL
require_once "calcularPrecio.php"; // tu función de precios
require_once "funciones_finanzas.php"; // donde pondremos la función guardarFinanzaYRegistroPago

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger variables del formulario
    $id_usuario = $_POST['id_usuario']; // este debe venir del login o sesión
    $nombres = $_POST['nombres'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $documento = $_POST['documento'] ?? '';
    $cantidad_mascotas = intval($_POST['cantidad_mascotas']);
    $periodo = $_POST['periodo']; // 'semestral' o 'anual'
    $telefono = $_POST['telefono'] ?? '';
    $whatsapp = isset($_POST['whatsapp']) ? 1 : 0;

    // Procesar evidencia (opcional)
    $evidencia_ruta = '';
    if (isset($_FILES['evidencia_pago']) && $_FILES['evidencia_pago']['error'] === 0) {
        $rutaDestino = 'uploads/pagos/';
        $nombreArchivo = uniqid() . "_" . basename($_FILES["evidencia_pago"]["name"]);
        $rutaCompleta = $rutaDestino . $nombreArchivo;
        if (move_uploaded_file($_FILES["evidencia_pago"]["tmp_name"], $rutaCompleta)) {
            $evidencia_ruta = $rutaCompleta;
        }
    }

    // Ejecutar función financiera
    $resultado = guardarFinanzaYRegistroPago(
        $mysqli,
        $id_usuario,
        $nombres,
        $apellidos,
        $documento,
        $cantidad_mascotas,
        $periodo,
        $telefono,
        $whatsapp,
        $evidencia_ruta
    );

    // Respuesta al usuario
    if (isset($resultado['ok'])) {
        echo "✅ Registro exitoso. Gracias por tu suscripción.";
    } else {
        echo "❌ Error: " . $resultado['error'];
    }
}
?>
