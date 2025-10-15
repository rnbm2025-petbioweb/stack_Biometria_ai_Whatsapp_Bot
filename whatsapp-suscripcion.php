<?php
header('Content-Type: application/json');

require_once "conexion.php"; 
require_once "modulo_finanzas.php"; // contiene guardarFinanzaYRegistroPago

$nombres = $_POST['nombres'] ?? '';
$apellidos = $_POST['apellidos'] ?? '';
$documento = $_POST['documento'] ?? '';
$cantidad_mascotas = $_POST['cantidad_mascotas'] ?? 0;
$periodo = $_POST['periodo'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$whatsapp = $_POST['whatsapp'] ?? 0;
$numero = $_POST['numero'] ?? '';

if (!$nombres || !$cantidad_mascotas || !$periodo) {
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos obligatorios']);
    exit;
}

$id_usuario = 0; // O buscar en DB según número

$result = guardarFinanzaYRegistroPago(
    $mysqli,
    $id_usuario,
    $nombres,
    $apellidos,
    $documento,
    $cantidad_mascotas,
    $periodo,
    $telefono,
    $whatsapp,
    ''
);

if (isset($result['ok'])) {
    echo json_encode(['status' => 'success', 'message' => $result['ok']]);
} else {
    echo json_encode(['status' => 'error', 'message' => $result['error']]);
}
