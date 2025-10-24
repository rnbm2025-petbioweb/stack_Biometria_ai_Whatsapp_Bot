<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    die("Error: Sesión no iniciada.");
}

$host = "mysql_petbio_secure";
$usuario = "root";
$clave = 'R00t_Segura_2025!';
$dbname = "db__produccion_petbio_segura_2025";
$puerto = 3306;

$conn = new mysqli($host, $usuario, $clave, $dbname, $puerto);
if ($conn->connect_error) die("Conexión fallida: " . $conn->connect_error);

$id_usuario = $_SESSION['id_usuario'];
$id_mascota = $_POST['id_mascota'];
$aliado_nombre = $_POST['aliado_nombre'];
$aliado_tipo = $_POST['aliado_tipo'];
$fecha_cita = $_POST['fecha_cita'];
$hora_cita = $_POST['hora_cita'];
$modalidad = $_POST['modalidad'];
$motivo = $_POST['motivo'];
$observaciones = $_POST['observaciones'] ?? null;

$evidencia_pdf = null;

// Subir archivo PDF si se incluye
if (!empty($_FILES['evidencia_pdf']['name'])) {
    $directorio = "uploads/citas_veterinarias/";
    if (!file_exists($directorio)) {
        mkdir($directorio, 0775, true);
    }

    $nombre_archivo = basename($_FILES["evidencia_pdf"]["name"]);
    $ruta_final = $directorio . time() . "_" . $nombre_archivo;

    if (move_uploaded_file($_FILES["evidencia_pdf"]["tmp_name"], $ruta_final)) {
        $evidencia_pdf = $ruta_final;
    } else {
        echo "Error al subir el archivo.";
        exit;
    }
}

// Consultar datos de la mascota
$sql_mascota = "SELECT nombre, clase_mascota, raza, edad, ciudad, barrio FROM registro_mascotas WHERE id = ? AND id_usuario = ?";
$stmt = $conn->prepare($sql_mascota);
$stmt->bind_param("ii", $id_mascota, $id_usuario);
$stmt->execute();
$stmt->bind_result($nombre_mascota, $clase, $raza, $edad, $ciudad, $barrio);
if (!$stmt->fetch()) {
    echo "Mascota no encontrada.";
    exit;
}
$stmt->close();

// Insertar en la tabla de citas
$sql = "INSERT INTO citas_veterinarias (
    id_usuario, id_mascota, nombre_mascota, clase_mascota, raza, edad, ciudad, barrio,
    aliado_nombre, aliado_tipo, fecha_cita, hora_cita, modalidad, motivo, observaciones, evidencia_pdf
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "iisssissssssssss",
    $id_usuario, $id_mascota, $nombre_mascota, $clase, $raza, $edad, $ciudad, $barrio,
    $aliado_nombre, $aliado_tipo, $fecha_cita, $hora_cita, $modalidad, $motivo, $observaciones, $evidencia_pdf
);

if ($stmt->execute()) {
    echo "<script>alert('✅ Cita registrada con éxito'); window.location.href = 'historia_clinica.php';</script>";
} else {
    echo "Error al guardar cita: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>
