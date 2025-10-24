<?php
function calcularPrecio($cantidad, $periodo = 'semestral') {
    $tarifas = [
        ['min' => 1,   'max' => 5,    'semestral' => 17000, 'anual' => 30000],
        ['min' => 6,   'max' => 12,   'semestral' => 16000, 'anual' => 29000],
        ['min' => 13,  'max' => 50,   'semestral' => 15000, 'anual' => 27000],
        ['min' => 51,  'max' => 100,  'semestral' => 14000, 'anual' => 25000],
        ['min' => 101, 'max' => 500,  'semestral' => 13000, 'anual' => 23000],
    ];

    $IVA = 0.19;
    $costoUnitario = 5000;

    foreach ($tarifas as $rango) {
        if ($cantidad >= $rango['min'] && $cantidad <= $rango['max']) {
            $precioBruto = $rango[$periodo];
            $precioSinIVA = round($precioBruto / (1 + $IVA));
            $totalBruto = $precioBruto * $cantidad;
            $totalSinIVA = $precioSinIVA * $cantidad;
            $utilidadNetaTotal = ($precioSinIVA - $costoUnitario) * $cantidad;
            $utilidadPorMascota = $precioSinIVA - $costoUnitario;
            $ahorroPorUnidad = $tarifas[0][$periodo] - $precioBruto; // ahorro comparado con el menor rango
            $ahorroTotal = $ahorroPorUnidad * $cantidad;

            return [
                'precio_unitario_con_iva' => $precioBruto,
                'precio_unitario_sin_iva' => $precioSinIVA,
                'total_con_iva' => $totalBruto,
                'total_sin_iva' => $totalSinIVA,
                'utilidad_neta_total' => $utilidadNetaTotal,
                'utilidad_por_mascota' => $utilidadPorMascota,
                'ahorro_por_unidad' => $ahorroPorUnidad,
                'ahorro_total' => $ahorroTotal
            ];
        }
    }

    return ['error' => 'Cantidad fuera de rango (1-500)'];
}

// Parámetros recibidos (pueden venir de un formulario, POST o backend interno)
$cantidad = 50;
$periodo = 'semestral';

$resultado = calcularPrecio($cantidad, $periodo);

// === Inserción en base de datos ===
if (!isset($resultado['error'])) {
    require_once "conexion.php"; // este archivo debe definir $mysqli

    $stmt = $mysqli->prepare("
        INSERT INTO finanzas_suscripciones (
            cantidad_mascotas, periodo, precio_unitario, total_con_iva, 
            precio_sin_iva, total_sin_iva, utilidad_unitaria, utilidad_total, ahorro_total
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("issiiiiii",
        $cantidad,
        $periodo,
        $resultado['precio_unitario_con_iva'],
        $resultado['total_con_iva'],
        $resultado['precio_unitario_sin_iva'],
        $resultado['total_sin_iva'],
        $resultado['utilidad_por_mascota'],
        $resultado['utilidad_neta_total'],
        $resultado['ahorro_total']
    );

    if ($stmt->execute()) {
        echo "✅ Registro financiero guardado correctamente.<br><br>";
    } else {
        echo "❌ Error al guardar: " . $stmt->error . "<br><br>";
    }

    $stmt->close();
}

// === Mostrar el resumen financiero ===
if (!isset($resultado['error'])) {
    echo "🐾 Cantidad: $cantidad<br>";
    echo "🕓 Periodo: $periodo<br><br>";

    echo "💸 Precio por mascota (con IVA): $" . number_format($resultado['precio_unitario_con_iva']) . "<br>";
    echo "🧾 Precio sin IVA: $" . number_format($resultado['precio_unitario_sin_iva']) . "<br>";
    echo "📦 Total a pagar (con IVA): $" . number_format($resultado['total_con_iva']) . "<br>";
    echo "💼 Total sin IVA: $" . number_format($resultado['total_sin_iva']) . "<br>";
    echo "📊 Utilidad neta total estimada: $" . number_format($resultado['utilidad_neta_total']) . "<br>";
    echo "💰 Utilidad por mascota: $" . number_format($resultado['utilidad_por_mascota']) . "<br>";
    echo "📉 Ahorro frente al plan base: $" . number_format($resultado['ahorro_total']) . "<br>";
} else {
    echo $resultado['error'];
}
?>
