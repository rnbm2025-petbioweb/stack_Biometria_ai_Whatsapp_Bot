<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require('fpdf/fpdf.php');
require_once('libs/phpqrcode/qrlib.php');

// --- DATOS EJEMPLO ---
$nombre = "JUAN LONDOÑO";
$raza = "Simplemente raza";
$edad = "3 años";
$ciudad = "Medellín";
$tipo_mascota = "Felino";
$imagen_perfil = 'uploads/felinos_simplemente_raza_3_8_medell__n_campo_valdes_050007_juan_perfil.jpeg';

// --- QR dinámico ---
$qr_data = "PetBio ID: $nombre - $tipo_mascota ($raza), $edad - $ciudad";
$qr_file = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
QRcode::png($qr_data, $qr_file, QR_ECLEVEL_L, 3);

// --- PDF formato cédula (1 sola página horizontal) ---
$pdf = new FPDF('L', 'mm', array(85.6, 54));
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);

// Fondo blanco
$pdf->SetFillColor(255, 255, 255);
$pdf->Rect(0, 0, 85.6, 54, 'F');

// Encabezado
$pdf->SetFillColor(5, 52, 80); // azul oscuro
$pdf->Rect(0, 0, 85.6, 10, 'F');
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetXY(3, 2);
$pdf->Cell(0, 6, mb_convert_encoding('CÉDULA MASCOTA PETBIO', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');

// Imagen
if (file_exists($imagen_perfil)) {
    $pdf->Image($imagen_perfil, 3, 12, 20, 25);
} else {
    $pdf->Rect(3, 12, 20, 25); // placeholder
}

// Info textual
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 7);
$pdf->SetXY(25, 13);
$pdf->Cell(55, 4, "Nombre: " . mb_convert_encoding($nombre, 'ISO-8859-1', 'UTF-8'), 0);
$pdf->SetXY(25, 18);
$pdf->Cell(55, 4, "Tipo: " . $tipo_mascota, 0);
$pdf->SetXY(25, 23);
$pdf->Cell(55, 4, "Raza: " . mb_convert_encoding($raza, 'ISO-8859-1', 'UTF-8'), 0);
$pdf->SetXY(25, 28);
$pdf->Cell(55, 4, "Edad: " . mb_convert_encoding($edad, 'ISO-8859-1', 'UTF-8'), 0);
$pdf->SetXY(25, 33);
$pdf->Cell(55, 4, "Ciudad: " . mb_convert_encoding($ciudad, 'ISO-8859-1', 'UTF-8'), 0);

// Código QR
$pdf->Image($qr_file, 65, 34, 17, 17); // parte inferior derecha

// Guardar PDF
$output_dir = __DIR__ . "/documentos";
if (!file_exists($output_dir)) {
    mkdir($output_dir, 0755, true);
}
$pdf_output_path = $output_dir . "/cedula_mascota_" . str_replace(" ", "_", $nombre) . "_" . date("Ymd") . ".pdf";
$pdf->Output('F', $pdf_output_path);

echo "✅ PDF generado en: $pdf_output_path\n";
@unlink($qr_file);
