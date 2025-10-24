<?php
require_once('fpdf/fpdf.php');
require_once('libs/phpqrcode/qrlib.php');

// --- Datos de Wilson (del PDF existente) ---
$nombre = "Wilson";
$raza = "Pastor belga mailinois";
$edad = "7 años";
$ciudad = "Medellín";
$barrio = "Campo Valdés";
$tipo_mascota = "Caninos";
$documento_pasaporte = "05000720072025930893";
$imagen_perfil = __DIR__ . '/petbio_storage/uploads/caninos_pastor_belga_mailinois_7_0_medellin_campo_valdes_050007_wilson_perfil.jpeg';

// --- QR dinámico ---
$qr_data = "PetBio ID: $nombre - $tipo_mascota ($raza), $edad - $ciudad";
$qr_file = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
QRcode::png($qr_data, $qr_file, QR_ECLEVEL_L, 3);

// --- PDF de cédula (formato tarjeta) ---
$pdf = new FPDF('L', 'mm', array(85.6, 54));
$pdf->AddPage();

// Fondo blanco
$pdf->SetFillColor(255, 255, 255);
$pdf->Rect(0, 0, 85.6, 54, 'F');

// Encabezado azul oscuro
$pdf->SetFillColor(5, 52, 80);
$pdf->Rect(0, 0, 85.6, 12, 'F');
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetXY(3, 3);
$pdf->Cell(0, 5, 'CÉDULA MASCOTA PETBIO', 0, 1, 'L');

// Imagen de perfil
if (file_exists($imagen_perfil)) {
    $pdf->Image($imagen_perfil, 3, 16, 20, 20);
} else {
    $pdf->Rect(3, 16, 20, 20); // Placeholder
}

// Texto informativo
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 7);
$pdf->SetXY(25, 16);
$pdf->Cell(0, 4, "Nombre: $nombre", 0, 1);
$pdf->SetX(25);
$pdf->Cell(0, 4, "Tipo: $tipo_mascota", 0, 1);
$pdf->SetX(25);
$pdf->Cell(0, 4, "Raza: $raza", 0, 1);
$pdf->SetX(25);
$pdf->Cell(0, 4, "Edad: $edad", 0, 1);
$pdf->SetX(25);
$pdf->Cell(0, 4, "Ciudad: $ciudad", 0, 1);
$pdf->SetX(25);
$pdf->Cell(0, 4, "Doc. ID: $documento_pasaporte", 0, 1);

// QR
$pdf->Image($qr_file, 64, 30, 17, 17);

// Guardar en carpeta documentos
$output_dir = __DIR__ . "/petbio_storage/documentos";
if (!file_exists($output_dir)) {
    mkdir($output_dir, 0755, true);
}
$cedula_output_path = $output_dir . "/cedula_mascota_Wilson_" . date("Ymd") . ".pdf";
$pdf->Output('F', $cedula_output_path);

@unlink($qr_file);

echo "✅ Cédula PDF generada: $cedula_output_path\n";
?>
