<?php
session_start();

require_once(__DIR__ . "/conexion_petbio_nueva.php");

// --- 1. Recibir datos del formulario landing ---
$numero_registro = $_POST['numero_registro'] ?? uniqid("PETBIO-");
$nombre          = $_POST['nombre']          ?? 'Sin nombre';
$raza            = $_POST['raza']            ?? 'Desconocida';
$ciudad          = $_POST['ciudad']          ?? 'N/A';
$edad            = $_POST['edad']            ?? '0';
$apellidos       = $_SESSION['apellidos']    ?? 'Anónimo';

// --- 2. Construir datos para Python ---
$datos_cedula = [
    'numero_registro' => $numero_registro,
    'nombre'          => $nombre,
    'raza'            => $raza,
    'ciudad'          => $ciudad,
    'edad'            => $edad . " años",
    'responsable'     => $apellidos,
    'id_petbio'       => $numero_registro,
    'ruta_img_perfil' => isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK
                            ? "/var/www/html/petbio_landing/uploads/" . basename($_FILES['foto_perfil']['name'])
                            : "/var/www/html/imagenes_guias_2025/no_admitida (1).jpeg",
    'ruta_logo'       => "/var/www/html/imagenes_guias_2025/logo_petbio_transparente.png",
    'ruta_huella'     => "/var/www/html/imagenes_guias_2025/paw.png",
    'salida_pdf'      => "/var/www/html/petbio_storage/documentos/cedula_{$numero_registro}.pdf"
];

// --- 3. Guardar foto si fue subida ---
if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = "/var/www/html/petbio_landing/uploads/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $upload_dir . basename($_FILES['foto_perfil']['name']));
}

// --- 4. Ejecutar script Python ---
$json = json_encode($datos_cedula, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$cmd = "/home/josornol341/petbio11-formularios/biometria_ai/venv/bin/python3 "
     . "/home/josornol341/petbio11-formularios/biometria_ai/generar_cedula_debug.py "
     . escapeshellarg($json);

$output = shell_exec($cmd . " 2>&1");

// --- 5. Verificar PDF generado ---
$pdf_absoluto = $datos_cedula['salida_pdf'];
if (file_exists($pdf_absoluto)) {
    $_SESSION['cedula_nueva_path'] = "/petbio_storage/documentos/cedula_{$numero_registro}.pdf";
    
    // Redirigir a página de éxito en landing
    header("Location: gracias.php?pdf=" . urlencode($_SESSION['cedula_nueva_path']));
    exit();
} else {
    error_log("❌ Error al generar PDF de cédula: " . $output);
    echo "<h2>Error al generar cédula</h2>";
    echo "<pre>$output</pre>";
}
?>

