<?php
session_start();

require_once '../dir_config/sesion_global.php';


// Verificar sesión activa
if (!isset($_SESSION['id_usuario'])) {
    header("Location: loginpetbio.php?error=no_sesion");
    exit();
}

// Validar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Acceso no permitido.");
}

// Calcular precio
require_once 'calcular_precio.php';

// Conexión a la base de datos
$conexion = new mysqli("mysql_petbio_secure", "root", "R00t_Segura_2025!", "db__produccion_petbio_segura_2025", 3306);
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtener datos del usuario desde la sesión
$id_usuario = $_SESSION['id_usuario'];
$username = $_SESSION['username'];
$apellidos_usuario = $_SESSION['apellidos'];

// Obtener datos del formulario
$nombres = $_POST['nombres'];
$apellidos = $_POST['apellidos'];
$documento = $_POST['documento'];
$cantidad_exacta = $_POST['cantidad_exacta'];
$telefono = $_POST['telefono'];
$whatsapp = isset($_POST['whatsapp']) ? 1 : 0;
$cantidad_mascotas = isset($_POST['cantidad_mascotas']) ? intval($_POST['cantidad_mascotas']) : null;
$total_pagado = isset($_POST['total_pagado']) ? intval($_POST['total_pagado']) : null;
$periodo = $_POST['periodo'] ?? null;

// Cálculo de precio
$resultado = calcularPrecio($cantidad_mascotas, $periodo);
if (isset($resultado['error'])) {
    die("Error en el cálculo: " . $resultado['error']);
}
$precio_total = $resultado['total'];
$precio_unitario = $resultado['precio_unitario'];

// Procesamiento de la evidencia de pago
$evidencia = null;
$evidencia_pago_ruta = null;

if (isset($_FILES['evidencia_pago']) && $_FILES['evidencia_pago']['error'] == UPLOAD_ERR_OK) {
    $directorio_relativo = "uploads/Evidencias_Pagos/";
    $directorio_absoluto = __DIR__ . "/" . $directorio_relativo;

    if (!is_dir($directorio_absoluto)) {
        if (!mkdir($directorio_absoluto, 0777, true)) {
            die("❌ No se pudo crear el directorio de evidencia: $directorio_absoluto");
        }
    }

    $archivo_nombre = basename($_FILES['evidencia_pago']['name']);
    $archivo_nombre_unico = time() . "_" . $archivo_nombre;
    $ruta_archivo_absoluto = $directorio_absoluto . $archivo_nombre_unico;

    if (move_uploaded_file($_FILES['evidencia_pago']['tmp_name'], $ruta_archivo_absoluto)) {
        $evidencia = $directorio_relativo . $archivo_nombre_unico;
        $evidencia_pago_ruta = $archivo_nombre_unico;
    } else {
        die("❌ Error al guardar la evidencia de pago.");
    }
}

// Insertar en la base de datos
$stmt = $conexion->prepare("INSERT INTO pago_suscripcion 
(id_usuario, nombres, apellidos, documento, cantidad_exacta, telefono, whatsapp, evidencia_pago, evidencia_pago_ruta, cantidad_mascotas, periodo, total_pagado) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("isssisissisi", 
    $id_usuario, $nombres, $apellidos, $documento, $cantidad_exacta, $telefono, $whatsapp, 
    $evidencia, $evidencia_pago_ruta, $cantidad_mascotas, $periodo, $total_pagado);

// Mostrar respuesta con confetti en caso de éxito
if ($stmt->execute()) {
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
      <meta charset='UTF-8'>
      <title>Suscripción Exitosa</title>
      <script src='https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js'></script>
      <style>
        body {
          font-family: sans-serif;
          text-align: center;
          padding-top: 50px;
          background: #f9f9f9;
          color: #333;
        }
        .mensaje {
          background: white;
          border-radius: 16px;
          box-shadow: 0 4px 20px rgba(0,0,0,0.1);
          display: inline-block;
          padding: 30px;
        }
        .volver {
          margin-top: 20px;
        }
        .volver a {
          text-decoration: none;
          background-color: #38bdf8;
          color: white;
          padding: 10px 20px;
          border-radius: 10px;
          font-weight: bold;
          transition: background-color 0.3s ease;
        }
        .volver a:hover {
          background-color: #0ea5e9;
        }
      </style>
    </head>
    <body>
      <div class='mensaje'>
        <h1>🎉 ¡Suscripción guardada con éxito!</h1>
        <p>💵 Precio por mascota: $" . number_format($precio_unitario) . "</p>
        <p>🐾 Total pagado: $" . number_format($precio_total) . "</p>
        <p>📎 Evidencia guardada como: $evidencia_pago_ruta</p>
        <div class='volver'>
          <a href='bienvenida.html'>Volver al inicio</a>
        </div>
      </div>
      <script>
        confetti({
          particleCount: 200,
          spread: 100,
          origin: { y: 0.6 }
        });
        setTimeout(() => {
          window.location.href = 'bienvenida.html';
        }, 4000);
      </script>
    </body>
    </html>";
    exit();
} else {
    echo "❌ Error al registrar: " . $conexion->error;
}

$stmt->close();
$conexion->close();
?>
