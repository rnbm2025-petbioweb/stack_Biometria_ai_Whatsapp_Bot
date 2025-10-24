<?php

file_put_contents('debug_post.log', print_r($_POST, true));

/*
ob_start(); // Inicia el buffer de salida
print_r($_POST);
$contenido = ob_get_clean();

// Guardar en un archivo de logs si quieres
file_put_contents('debug_post.log', $contenido);  */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Sesión segura
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_domain', '.siac2025.com');
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_samesite', 'None');
}

require_once 'sesion_global.php';

require_once '../dir_config/sesion_global.php';

//equire_once 'config/conexion_petbio_nueva.php';

session_start();



// Validar campos obligatorios
$campos_obligatorios = [
    'nombre', 'apellidos', 'raza', 'edad', 'relacion',
    'ciudad', 'barrio', 'codigo_postal', 'clase_mascota', 'condicion_mascota',
    'img_perfil','img_hf0', 'img_latiz', 'img_latdr',
    'img_hf15', 'img_hf30', 'img_hfld15', 'img_hfli15'
];

foreach ($campos_obligatorios as $campo) {
    if (str_starts_with($campo, 'img_')) {
        $es_base64 = isset($_POST[$campo]) && is_string($_POST[$campo]) &&
                     preg_match('/^data:image\/(png|jpeg|jpg);base64,/', $_POST[$campo]);

        $es_archivo = isset($_FILES[$campo]) &&
                      $_FILES[$campo]['error'] === UPLOAD_ERR_OK &&
                      $_FILES[$campo]['size'] > 0;

        if (!$es_base64 && !$es_archivo) {
            die("❌ Imagen faltante o inválida: $campo");
        }
    } else {
        if (!isset($_POST[$campo]) || trim((string)$_POST[$campo]) === '') {
            die("❌ Campo obligatorio faltante o vacío: $campo");
        }
    }
}

// Verifica sesión
if (!isset($_SESSION['id_usuario'])) die("Error: No hay sesión iniciada.");

// Conexión MySQL
$conn = new mysqli("mysql_petbio_secure", "root", "R00t_Segura_2025!", "db__produccion_petbio_segura_2025", 3306);
if ($conn->connect_error) die("Conexión fallida: " . $conn->connect_error);

// Función limpieza
function limpiar($valor) {
    return htmlspecialchars(trim($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

// Datos POST
$id_usuario = (int) $_SESSION['id_usuario'];
$nombre = limpiar($_POST['nombre']);
$apellidos = limpiar($_POST['apellidos']);
$raza = limpiar($_POST['raza']);
$edad = intval($_POST['edad'] ?? 0);
$relacion = limpiar($_POST['relacion']);
$ciudad = limpiar($_POST['ciudad']);
$barrio = limpiar($_POST['barrio']);
$codigo_postal = limpiar($_POST['codigo_postal']);
$clase_mascota = limpiar($_POST['clase_mascota']);
$condicion_mascota = limpiar($_POST['condicion_mascota']);
$ciudad_y_barrio = "$ciudad - $barrio";

$con_documento = $_POST['con_documento'] ?? 'No';
$entidad_expedidora = 'PETBIO11 - RNBM';
$tipo_documento = '';
$descripcion_documento = '';
$numero_documento_entidad = 'GENERADO_O_ENTRADA';

if ($con_documento === "Sí") {
    $tipo_documento = limpiar($_POST['tipoDocumento'] ?? '');
    $descripcion_documento = limpiar($_POST['descripcionDocumento'] ?? '');
    $numero_documento = limpiar($_POST['numero_documento'] ?? '');

    if (!$numero_documento) {
        exit('❌ Falta el número de documento.');
    }
    $documento_pasaporte = $numero_documento;
    $documento_visible = substr($documento_pasaporte, -6); // últimos 6 dígitos


} else {
    $numero_aleatorio = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $fecha = date('dmY');
    $documento_pasaporte = $codigo_postal . $fecha . $numero_aleatorio;
    $numero_documento = $documento_pasaporte;
    $documento_visible = substr($documento_pasaporte, -6); // <-- solución aquí

}

// Verificar duplicado
$check = $conn->prepare("SELECT id FROM registro_mascotas WHERE numero_documento = ?");
$check->bind_param("s", $numero_documento);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo "⚠️ Ya existe un registro con ese número de documento.";
    exit();
}
$check->close();

// Función para guardar imagen
function guardarImagen($campo, $etiqueta, $datos, $upload_dir, $web_base) {
    if (isset($_POST[$campo]) && preg_match('/^data:image\/(\w+);base64,/', $_POST[$campo], $tipo)) {
        $extension = strtolower($tipo[1]);
        $base64 = base64_decode(substr($_POST[$campo], strpos($_POST[$campo], ',') + 1));
    } elseif (isset($_FILES[$campo]) && $_FILES[$campo]['error'] === UPLOAD_ERR_OK) {
        $extension = pathinfo($_FILES[$campo]['name'], PATHINFO_EXTENSION);
        $base64 = file_get_contents($_FILES[$campo]['tmp_name']);
    } else {
        return ['ruta' => null, 'path' => null];
    }

    $nombre_archivo = strtolower(
        implode('_', [
            $datos['clase_mascota'] ?? 'sinclase',
            $datos['raza'] ?? 'sinraza',
            $datos['edad'] ?? '0',
            $datos['id_usuario'] ?? 'anon',
            $datos['ciudad'] ?? 'sincuidad',
            $datos['barrio'] ?? 'sinbarrio',
            $datos['codigo_postal'] ?? '00000',
            $datos['nombre'] ?? 'sin_nombre',
            $etiqueta
        ]) . '.' . $extension
    );

    $nombre_archivo = preg_replace('/[^a-zA-Z0-9_\.\-]/', '_', $nombre_archivo);
    $ruta_completa = rtrim($upload_dir, '/') . '/' . $nombre_archivo;

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    if (file_put_contents($ruta_completa, $base64) === false) {
        return ['ruta' => null, 'path' => null];
    }

    return [
        'ruta' => rtrim($web_base, '/') . '/' . $nombre_archivo,
        'path' => $ruta_completa
    ];
}

// Directorios
$upload_dir = __DIR__ . '/petbio_storage/uploads/';
$web_base = '/formularios/petbio_storage/uploads/';
$pdf_dir = __DIR__ . '/petbio_storage/documentos/';
if (!file_exists($upload_dir)) mkdir($upload_dir, 0755, true);
if (!file_exists($pdf_dir)) mkdir($pdf_dir, 0755, true);

// Imágenes
$datos = compact('clase_mascota', 'raza', 'edad', 'id_usuario', 'ciudad', 'barrio', 'codigo_postal', 'nombre');
$imagenes = [
    'ruta_img_hf0'     => guardarImagen('img_hf0', 'hf0', $datos, $upload_dir, $web_base),
    'ruta_img_hf15'    => guardarImagen('img_hf15', 'hf15', $datos, $upload_dir, $web_base),
    'ruta_img_hf30'    => guardarImagen('img_hf30', 'hf30', $datos, $upload_dir, $web_base),
    'ruta_img_hfld15'  => guardarImagen('img_hfld15', 'hfld15', $datos, $upload_dir, $web_base),
    'ruta_img_hfli15'  => guardarImagen('img_hfli15', 'hfli15', $datos, $upload_dir, $web_base),
    'ruta_img_perfil'  => guardarImagen('img_perfil', 'perfil', $datos, $upload_dir, $web_base),
    'ruta_img_latdr'   => guardarImagen('img_latdr', 'latdr', $datos, $upload_dir, $web_base),
    'ruta_img_latiz'   => guardarImagen('img_latiz', 'latiz', $datos, $upload_dir, $web_base),
];

// Insertar en base de datos
$sql = "INSERT INTO registro_mascotas (
    id_usuario, nombre, apellidos, raza, edad, relacion, con_documento,
    tipo_documento, descripcion_documento, entidad_expedidora, numero_documento,
    barrio, ciudad, ciudad_y_barrio, codigo_postal,
    documento_pasaporte, clase_mascota, condicion_mascota, numero_documento_entidad,
    created_at, ruta_img_hf0, ruta_img_hf15, ruta_img_hf30, ruta_img_hfld15,
    ruta_img_hfli15, ruta_img_perfil, ruta_img_latdr, ruta_img_latiz
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("❌ Error en la preparación del SQL: " . $conn->error);
}

$stmt->bind_param(
    "isssissssssssssssssssssssss",
    $id_usuario,
    $nombre,
    $apellidos,
    $raza,
    $edad,
    $relacion,
    $con_documento,
    $tipo_documento,
    $descripcion_documento,
    $entidad_expedidora,
    $numero_documento,
    $barrio,
    $ciudad,
    $ciudad_y_barrio,
    $codigo_postal,
    $documento_pasaporte,
    $clase_mascota,
    $condicion_mascota,
    $numero_documento_entidad,
    $imagenes['ruta_img_hf0']['ruta'],
    $imagenes['ruta_img_hf15']['ruta'],
    $imagenes['ruta_img_hf30']['ruta'],
    $imagenes['ruta_img_hfld15']['ruta'],
    $imagenes['ruta_img_hfli15']['ruta'],
    $imagenes['ruta_img_perfil']['ruta'],
    $imagenes['ruta_img_latdr']['ruta'],
    $imagenes['ruta_img_latiz']['ruta']
);

if ($stmt->execute()) {

    // Auditoría
    $accion = "Registro de mascota";
    $descripcion = "El usuario $id_usuario registró la mascota '$nombre' con doc. $documento_pasaporte.";
    $audit_sql = "INSERT INTO auditoria_registros (id_usuario, accion, descripcion, tabla_afectada)
                  VALUES (?, ?, ?, 'registro_mascotas')";
    $audit = $conn->prepare($audit_sql);
    if ($audit) {
        $audit->bind_param("iss", $id_usuario, $accion, $descripcion);
        $audit->execute();
        $audit->close();
    }

    // Generar PDF

    
    require_once(__DIR__ . '/fpdf/fpdf.php');
    $pdf = new FPDF();
    $pdf->AddPage();

    // Estilos
    $color_titulo = [39, 68, 93];     // #27445D
    $color_texto  = [73, 125, 116];   // #497D74
    $fondo        = [239, 233, 213];  // #EFE9D5

    $pdf->SetFillColor(...$fondo);
    $pdf->Rect(0, 0, 210, 297, 'F');

    // Logo
    $logo_path = __DIR__ . '/petbio_storage/uploads/logo_siac_preto.jpeg';
    if (file_exists($logo_path)) {
        $pdf->Image($logo_path, 10, 10, 40);
    }

    // Título
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->SetTextColor(...$color_titulo);
   // $pdf->Cell(0, 30, utf8_decode('Documento de Identidad Biométrica PETBIO – RNBM'), 0, 1, 'C');
   $pdf->Cell(0, 30, mb_convert_encoding('Documento de Identidad Biométrica PETBIO – RNBM', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');

    // Datos
    $pdf->SetTextColor(...$color_texto);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Ln(10);
    $pdf->Cell(50, 10, 'Nombre:', 0, 0);         $pdf->Cell(100, 10, $nombre, 0, 1);
    $pdf->Cell(50, 10, 'Raza:', 0, 0);           $pdf->Cell(100, 10, $raza, 0, 1);
    $pdf->Cell(50, 10, 'Edad:', 0, 0);           $pdf->Cell(100, 10, $edad . ' años', 0, 1);
    $pdf->Cell(50, 10, 'Ubicación:', 0, 0);      $pdf->Cell(100, 10, $ciudad_y_barrio, 0, 1);
    $pdf->Cell(50, 10, 'Clase:', 0, 0);          $pdf->Cell(100, 10, $clase_mascota, 0, 1);
    $pdf->Cell(50, 10, 'Condición:', 0, 0);      $pdf->Cell(100, 10, $condicion_mascota, 0, 1);
    $pdf->Cell(50, 10, 'Documento ID:', 0, 0);   $pdf->Cell(100, 10, $documento_pasaporte, 0, 1);

    // Imagen de perfil
    $perfil_path = $imagenes['ruta_img_perfil']['path'] ?? null;
    if ($perfil_path && file_exists($perfil_path)) {
        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Imagen de Perfil:', 0, 1, 'C');
        $pdf->Image($perfil_path, $pdf->GetX() + 40, $pdf->GetY(), 100);
        $pdf->Ln(60);
    }

    // Pie
    $pdf->Ln(15);
    $pdf->SetFont('Arial', 'I', 10);
    //$pdf->Cell(0, 10, utf8_decode("Documento generado por PETBIO11 – Red Nacional de Biometría de Mascotas"), 0, 1, 'C');
    $pdf->Cell(0, 10, mb_convert_encoding("Documento generado por PETBIO11 – Red Nacional de Biometría de Mascotas", 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');


    // Guardar PDF
    $pdf_name = 'registro_' . preg_replace('/[^a-zA-Z0-9]/', '_', $nombre) . '_' . date('Ymd') . '.pdf';
    $pdf_path = $pdf_dir . $pdf_name;
    $pdf->Output('F', $pdf_path);

    // Variables de sesión
    $_SESSION['pdf_path'] = '/formularios/petbio_storage/documentos/' . $pdf_name;
    $_SESSION['registro_completado'] = true;


    // --- Generar Cédula tipo Tarjeta (formato horizontal) ---
require_once(__DIR__ . '/libs/phpqrcode/qrlib.php');

$qr_data = "PetBio ID: $nombre - $clase_mascota ($raza), $edad años - $ciudad - ID:$documento_visible";

//$qr_data = "PetBio ID: $nombre - $clase_mascota ($raza), $edad años - $ciudad";
$qr_file = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
QRcode::png($qr_data, $qr_file, QR_ECLEVEL_L, 3);

// Crear PDF de cédula (tamaño tarjeta CR-80)
$cedula = new FPDF('L', 'mm', array(85.6, 54));
$cedula->AddPage();

// Fondo blanco
$cedula->SetFillColor(255, 255, 255);
$cedula->Rect(0, 0, 85.6, 54, 'F');

// Encabezado azul oscuro
$cedula->SetFillColor(5, 52, 80);
$cedula->Rect(0, 0, 85.6, 12, 'F');
$cedula->SetTextColor(255, 255, 255);
$cedula->SetFont('Arial', 'B', 10);
$cedula->SetXY(3, 3);
$cedula->Cell(0, 5, 'CÉDULA MASCOTA PETBIO', 0, 1, 'L');

// Imagen de perfil
$img_perfil_path = $imagenes['ruta_img_perfil']['path'] ?? null;
if ($img_perfil_path && file_exists($img_perfil_path)) {
    $cedula->Image($img_perfil_path, 3, 16, 20, 20);
} else {
    $cedula->Rect(3, 16, 20, 20);
}

$qr_data = "PetBio ID: $nombre - $clase_mascota ($raza), $edad años - $ciudad - ID:$documento_visible";


// Texto
$cedula->SetTextColor(0, 0, 0);
$cedula->SetFont('Arial', '', 7);
$cedula->SetXY(25, 16);
$cedula->Cell(0, 4, "Nombre: $nombre", 0, 1);
$cedula->SetX(25);
$cedula->Cell(0, 4, "Tipo: $clase_mascota", 0, 1);
$cedula->SetX(25);
$cedula->Cell(0, 4, "Raza: $raza", 0, 1);
$cedula->SetX(25);
$cedula->Cell(0, 4, "Edad: $edad años", 0, 1);
$cedula->SetX(25);
$cedula->Cell(0, 4, "Ciudad: $ciudad", 0, 1);
$cedula->SetX(25);
$cedula->Cell(0, 4, "ID PETBIO: $documento_visible", 0, 1); // NUEVA LÍNEA

/*
// Texto
$cedula->SetTextColor(0, 0, 0);
$cedula->SetFont('Arial', '', 7);
$cedula->SetXY(25, 16);
$cedula->Cell(0, 4, "Nombre: $nombre", 0, 1);
$cedula->SetX(25);
$cedula->Cell(0, 4, "Tipo: $clase_mascota", 0, 1);
$cedula->SetX(25);
$cedula->Cell(0, 4, "Raza: $raza", 0, 1);
$cedula->SetX(25);
$cedula->Cell(0, 4, "Edad: $edad años", 0, 1);
$cedula->SetX(25);
$cedula->Cell(0, 4, "Ciudad: $ciudad", 0, 1);
*/
// QR
$cedula->Image($qr_file, 64, 30, 17, 17);

// Guardar
$cedula_filename = 'cedula_mascota_' . preg_replace('/[^a-zA-Z0-9]/', '_', $nombre) . '_' . date('Ymd') . '.pdf';
$cedula_path = $pdf_dir . $cedula_filename;
$cedula->Output('F', $cedula_path);

// (opcional) Guardar ruta en sesión si se desea mostrar después
$_SESSION['cedula_path'] = '/formularios/petbio_storage/documentos/' . $cedula_filename;

@unlink($qr_file);

    // ✅ Redirigir limpio
    header("Location: registro_exitoso.php");
    exit();

} else {
    die("❌ Error al guardar datos: " . $stmt->error);
}

// Finaliza y cierra
#ob_end_flush();
$stmt->close();
$conn->close();
