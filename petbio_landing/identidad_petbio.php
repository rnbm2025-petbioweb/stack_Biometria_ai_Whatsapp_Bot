<?php
ini_set('display_errors', 0); // Producción
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);


// Sesión segura
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_domain', '.siac2025.com');
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_samesite', 'None');
}
session_start();


// Directorio de logs
$log_dir = __DIR__ . '/petbio_storage/logs/';
if (!is_dir($log_dir)) mkdir($log_dir, 0755, true);
file_put_contents($log_dir . 'debug_post.log', print_r($_POST, true), FILE_APPEND);


require_once 'sesion_global.php';
//require_once '../dir_config/sesion_global.php';


// Campos obligatorios
$campos_obligatorios = [
    'nombre', 'apellidos', 'raza', 'edad', 'relacion',
    'ciudad', 'barrio', 'codigo_postal', 'clase_mascota', 'condicion_mascota',
    'img_perfil','img_hf0', 'img_latiz', 'img_latdr',
    'img_hf15', 'img_hf30', 'img_hfld15', 'img_hfli15'
];


foreach ($campos_obligatorios as $campo) {
    if (substr($campo,0,4)==='img_') {
        $es_base64 = isset($_POST[$campo]) && is_string($_POST[$campo]) &&
                     preg_match('/^data:image\/(png|jpeg|jpg);base64,/', $_POST[$campo]);
        $es_archivo = isset($_FILES[$campo]) &&
                      $_FILES[$campo]['error'] === UPLOAD_ERR_OK &&
                      $_FILES[$campo]['size'] > 0;
        if (!$es_base64 && !$es_archivo) die("❌ Imagen faltante o inválida: $campo");
    } else {
        if (!isset($_POST[$campo]) || trim((string)$_POST[$campo])==='') {
            die("❌ Campo obligatorio faltante o vacío: $campo");
        }
    }
}


// Verifica sesión
if (!isset($_SESSION['id_usuario'])) die("Error: No hay sesión iniciada.");


// Conexión MySQL
$conn = new mysqli("mysql_petbio_secure", "root", "R00t_Segura_2025!", "db__produccion_petbio_segura_2025", 3306);
if ($conn->connect_error) die("Conexión fallida: " . $conn->connect_error);


// Limpieza
function limpiar($valor){
    return htmlspecialchars(trim($valor ?? ''), ENT_QUOTES,'UTF-8');
}


// Datos POST
$id_usuario = (int)$_SESSION['id_usuario'];
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


// Documentos
$con_documento = $_POST['con_documento'] ?? 'No';
$entidad_expedidora = 'PETBIO11 - RNBM';
$tipo_documento = '';
$descripcion_documento = '';
$numero_documento_externo = null;
$numero_documento_petbio = '';


if($con_documento==="Sí"){
    $tipo_documento = limpiar($_POST['tipoDocumento'] ?? '');
    $descripcion_documento = limpiar($_POST['descripcionDocumento'] ?? '');
    $entidad_expedidora = limpiar($_POST['entidadExpedidora'] ?? '');
    $numero_documento_externo = limpiar($_POST['numeroDocumento'] ?? '');
    if(!$numero_documento_externo) exit('❌ Falta el número de documento.');
}


// Número PETBIO
$numero_aleatorio = str_pad(rand(0,999999),6,'0',STR_PAD_LEFT);
$fecha = date('dmY');
$numero_documento_petbio = $codigo_postal.$fecha.$numero_aleatorio;
$ultimos6 = substr($numero_documento_petbio, -6); // Solo los últimos 6 dígitos


// Verificar duplicado
$check = $conn->prepare("SELECT id FROM registro_mascotas WHERE numero_documento=?");
$check->bind_param("s",$numero_documento_petbio);
$check->execute(); $check->store_result();
if($check->num_rows>0) exit("⚠️ Ya existe un registro con ese número PETBIO.");
$check->close();


// Guardar imágenes
function guardarImagen($campo, $etiqueta, $datos, $upload_dir, $web_base){
    if(isset($_POST[$campo]) && preg_match('/^data:image\/(\w+);base64,/', $_POST[$campo], $tipo)){
        $extension = strtolower($tipo[1]);
        $base64 = base64_decode(substr($_POST[$campo], strpos($_POST[$campo],',')+1));
    } elseif(isset($_FILES[$campo]) && $_FILES[$campo]['error']===UPLOAD_ERR_OK){
        $extension = pathinfo($_FILES[$campo]['name'], PATHINFO_EXTENSION);
        $base64 = file_get_contents($_FILES[$campo]['tmp_name']);
    } else return ['ruta'=>null,'path'=>null];


    $nombre_archivo = strtolower(
        implode('_',[
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
    $nombre_archivo = preg_replace('/[^a-zA-Z0-9_\.\-]/','_',$nombre_archivo);
    $ruta_completa = rtrim($upload_dir,'/').'/'.$nombre_archivo;
    if(!is_dir($upload_dir)) mkdir($upload_dir,0755,true);
    if(file_put_contents($ruta_completa,$base64)===false) return ['ruta'=>null,'path'=>null];
    return ['ruta'=>rtrim($web_base,'/').'/'.$nombre_archivo,'path'=>$ruta_completa];
}


// Directorios
$upload_dir = __DIR__.'/petbio_storage/uploads/';
$web_base = '/formularios/petbio_storage/uploads/';
$pdf_dir = __DIR__.'/petbio_storage/documentos/';
if(!file_exists($upload_dir)) mkdir($upload_dir,0755,true);
if(!file_exists($pdf_dir)) mkdir($pdf_dir,0755,true);


// Guardar todas las imágenes
$datos = compact('clase_mascota','raza','edad','id_usuario','ciudad','barrio','codigo_postal','nombre');
$imagenes = [
    'ruta_img_hf0'    => guardarImagen('img_hf0','hf0',$datos,$upload_dir,$web_base),
    'ruta_img_hf15'   => guardarImagen('img_hf15','hf15',$datos,$upload_dir,$web_base),
    'ruta_img_hf30'   => guardarImagen('img_hf30','hf30',$datos,$upload_dir,$web_base),
    'ruta_img_hfld15' => guardarImagen('img_hfld15','hfld15',$datos,$upload_dir,$web_base),
    'ruta_img_hfli15' => guardarImagen('img_hfli15','hfli15',$datos,$upload_dir,$web_base),
    'ruta_img_perfil' => guardarImagen('img_perfil','perfil',$datos,$upload_dir,$web_base),
    'ruta_img_latdr'  => guardarImagen('img_latdr','latdr',$datos,$upload_dir,$web_base),
    'ruta_img_latiz'  => guardarImagen('img_latiz','latiz',$datos,$upload_dir,$web_base),
];


// Insertar en DB
$sql = "INSERT INTO registro_mascotas (
    id_usuario, nombre, apellidos, raza, edad, relacion, con_documento,
    tipo_documento, descripcion_documento, entidad_expedidora, numero_documento_externo,
    numero_documento, barrio, ciudad, ciudad_y_barrio, codigo_postal,
    clase_mascota, condicion_mascota,
    created_at, ruta_img_hf0, ruta_img_hf15, ruta_img_hf30, ruta_img_hfld15,
    ruta_img_hfli15, ruta_img_perfil, ruta_img_latdr, ruta_img_latiz
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?)";


$stmt = $conn->prepare($sql);
if(!$stmt) die("❌ Error en la preparación del SQL: ".$conn->error);


$stmt->bind_param(
    "isssisssssssssssssssssssss",
    $id_usuario,$nombre,$apellidos,$raza,$edad,$relacion,$con_documento,
    $tipo_documento,$descripcion_documento,$entidad_expedidora,$numero_documento_externo,
    $numero_documento_petbio,$barrio,$ciudad,$ciudad_y_barrio,$codigo_postal,
    $clase_mascota,$condicion_mascota,
    $imagenes['ruta_img_hf0']['ruta'],$imagenes['ruta_img_hf15']['ruta'],
    $imagenes['ruta_img_hf30']['ruta'],$imagenes['ruta_img_hfld15']['ruta'],
    $imagenes['ruta_img_hfli15']['ruta'],$imagenes['ruta_img_perfil']['ruta'],
    $imagenes['ruta_img_latdr']['ruta'],$imagenes['ruta_img_latiz']['ruta']
);

if($stmt->execute()) {
    // Auditoría
    $accion = "Registro de mascota";
    $descripcion = "El usuario $id_usuario registró la mascota '$nombre' con doc. $numero_documento_petbio.";
    $audit_sql = "INSERT INTO auditoria_registros (id_usuario, accion, descripcion, tabla_afectada) VALUES (?, ?, ?, 'registro_mascotas')";
    $audit = $conn->prepare($audit_sql);
    if($audit){
        $audit->bind_param("iss",$id_usuario,$accion,$descripcion);
        $audit->execute();
        $audit->close();
    }

    // Preparar payload
    $id_petbio = $stmt->insert_id;
    $payload = [
        "id_petbio"   => $id_petbio,
        "nombre"      => $nombre,
        "raza"        => $raza,
        "ciudad"      => $ciudad,
        "edad"        => $edad,
        "clase"       => $clase_mascota,
        "condicion"   => $condicion_mascota,
        "documento"   => $ultimos6,
        "responsable" => $apellidos." ".$nombre,
        "img_perfil"  => $imagenes['ruta_img_perfil']['path'] ?? null,
        "salida_pdf"  => $pdf_dir."cedula_{$id_petbio}.pdf"
    ];

    require_once(__DIR__ . '/fpdf/fpdf.php');
    require_once(__DIR__ . '/libs/phpqrcode/qrlib.php');

    $pdf = new FPDF('L', 'mm', array(85.6, 54));
    $pdf->AddPage();

    // Fondo beige
    $pdf->SetFillColor(245, 240, 220);
    $pdf->Rect(0,0,85.6,54,'F');

    // Banda superior azul más suave
    $pdf->SetFillColor(60,120,180);
    $pdf->Rect(0,0,85.6,12,'F');   

    

    // Título centrado
    $pdf->SetFont('Arial','B',10);
    $pdf->SetTextColor(255,255,255);
    $pdf->SetXY(0,3);
    $pdf->Cell(85.6,6,utf8_decode('CÉDULA BIOMETRICA PETBIO'),0,0,'C');

    $logo1_path = __DIR__ . '/imagenes_guias_2025/logo_petbio_transparente.png';
    $logo2_path = __DIR__ . '/imagenes_guias_2025/paw.png';

    // Logo superior derecho
    if(file_exists($logo1_path)){
        list($w1, $h1) = getimagesize($logo1_path);
        $scale1 = 10 / max($w1,$h1);
        $w1_pdf = $w1 * $scale1;
        $h1_pdf = $h1 * $scale1;
        $pdf->Image($logo1_path, 85.6 - $w1_pdf - 2, 1, $w1_pdf, $h1_pdf);
    }

    // Imagen de perfil
    if($payload['img_perfil'] && file_exists($payload['img_perfil'])){
        $pdf->Image($payload['img_perfil'],2,13,23,23);
    } else {
        $pdf->Rect(2,17,23,23);
    }

    // Datos principales centrados
    $pdf->SetFont('Arial','',8);
    $pdf->SetTextColor(0,0,0);
    $x = 19 ;
    $y = 17;
    $pdf->SetXY($x,$y);
    //$pdf->Cell(1,2,utf8_decode("Nombre: $nombre"),0,1,'C');
    
    
    $pdf->SetX($x); $pdf->Cell(0,3,utf8_decode("ID PETBIO: $ultimos6"), 0,1,'C');  
    
    $pdf->SetX($x); $pdf->Cell(0,3,utf8_decode("Tipo: $clase_mascota"),0,1,'C');
    $pdf->SetX($x); $pdf->Cell(0,3,utf8_decode("Raza: $raza"),0,1,'C');
    $pdf->SetX($x); $pdf->Cell(0,3,utf8_decode("Edad: $edad años"),0,1,'C');
    $pdf->SetX($x); $pdf->Cell(0,3,utf8_decode("Ciudad: $ciudad"),0,1,'C');

    // QR en esquina inferior derecha
    $qr_file = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
    $qr_data = "PetBio ID: $nombre - $clase_mascota ($raza), $edad años - $ciudad - ID:$ultimos6";
    QRcode::png($qr_data, $qr_file, QR_ECLEVEL_L, 3);
    $pdf->Image($qr_file, 85.6-15-4, 54-15-3, 17, 17);
    @unlink($qr_file);

    // Logo secundario al pie centrado
    if(file_exists($logo2_path)){
        list($w2,$h2) = getimagesize($logo2_path);
        $scale2 = 10 / max($w2,$h2);
        $w2_pdf = $w2 * $scale2;
        $h2_pdf = $h2 * $scale2;
        $x_center = (90.6 - $w2_pdf)/2;
        $y_bottom = 54 - $h2_pdf - 2;
        $pdf->Image($logo2_path, $x_center, $y_bottom, $w2_pdf, $h2_pdf);
    }


    // Nombre en la esquina inferior izquierda (footer)
$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(0,0,0);

$x_footer = 2;                   // margen izquierdo
$y_footer = -7.7 - 3;              // 54 mm es altura total, 6 mm de margen desde abajo
$pdf->SetXY($x_footer, $y_footer);
$pdf->Cell(0,-9.6, utf8_decode("Nombre: $nombre"), 2, 5, 'L');


    // Guardar PDF
    $cedula_filename = 'cedula_mascota_'.$ultimos6.'.pdf';
    $cedula_path = $pdf_dir.$cedula_filename;
    $pdf->Output('F',$cedula_path);
    $_SESSION['cedula_path'] = '/formularios/petbio_storage/documentos/'.$cedula_filename;

    // Ejecutar generar_cedula.php con payload JSON
    $payload_json = escapeshellarg(json_encode($payload, JSON_UNESCAPED_UNICODE));
    shell_exec("php ".__DIR__."/generar_cedula.php $payload_json");

    // Redirigir
    header("Location: registro_exitoso.php");
    exit();

    $stmt->close();
    $conn->close();
} else {
    die("❌ Error al insertar registro: " . $stmt->error);
}
