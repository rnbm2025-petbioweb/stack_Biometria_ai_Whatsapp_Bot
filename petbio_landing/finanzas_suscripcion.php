<?php
function calcularPrecio($cantidad, $periodo = 'semestral') {
    $tarifas = [
        ['min' => 1,   'max' => 5,    'semestral' => 17000, 'anual' => 30000],
        ['min' => 6,   'max' => 12,   'semestral' => 16500, 'anual' => 29000],
        ['min' => 13,  'max' => 50,   'semestral' => 15500, 'anual' => 27000],
        ['min' => 51,  'max' => 100,  'semestral' => 14500, 'anual' => 25000],
        ['min' => 101, 'max' => 500,  'semestral' => 13500, 'anual' => 23000],
    ];

    $IVA = 0.19;
    $costoUnitario = 5000;

    foreach ($tarifas as $rango) {
        if ($cantidad >= $rango['min'] && $cantidad <= $rango['max']) {
            $precioBruto = $rango[$periodo]; // Incluye IVA
            $precioSinIVA = round($precioBruto / (1 + $IVA));
            $totalSinIVA = $precioSinIVA * $cantidad;
            $totalBruto = $precioBruto * $cantidad;
            $utilidadNeta = ($precioSinIVA - $costoUnitario) * $cantidad;

            return [
                'precio_unitario_bruto' => $precioBruto,
                'precio_unitario_sin_iva' => $precioSinIVA,
                'total_bruto' => $totalBruto,
                'total_sin_iva' => $totalSinIVA,
                'utilidad_neta' => $utilidadNeta
            ];
        }
    }

    return ['error' => 'Cantidad fuera de rango'];
}

// Si se recibe cantidad y periodo por POST
$cantidad = $_POST['cantidad'] ?? null;
$periodo = $_POST['periodo'] ?? 'semestral';
$resultado = null;

if ($cantidad) {
    $resultado = calcularPrecio((int)$cantidad, $periodo);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Calculadora Financiera HUELLIT@S</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#F0FAF6] min-h-screen flex items-center justify-center p-4">
  <div class="bg-white shadow-xl rounded-xl p-8 w-full max-w-xl space-y-6">
    <h2 class="text-2xl font-bold text-center text-[#114358]">📊 Módulo Financiero - Suscripción</h2>

    <form method="POST" class="space-y-4">
      <div>
        <label class="block text-gray-700">Cantidad de mascotas:</label>
        <input type="number" name="cantidad" required min="1" max="500" class="w-full border rounded p-2" value="<?= htmlspecialchars($cantidad ?? '') ?>">
      </div>
      <div>
        <label class="block text-gray-700">Periodo:</label>
        <select name="periodo" class="w-full border rounded p-2">
          <option value="semestral" <?= ($periodo === 'semestral') ? 'selected' : '' ?>>Semestral</option>
          <option value="anual" <?= ($periodo === 'anual') ? 'selected' : '' ?>>Anual</option>
        </select>
      </div>
      <button type="submit" class="w-full bg-[#114358] text-white py-2 rounded hover:bg-[#0d3d4d]">Calcular</button>
    </form>

    <?php if ($resultado && !isset($resultado['error'])): ?>
      <div class="bg-green-100 border border-green-300 p-4 rounded text-sm space-y-2">
        <p><strong>💸 Precio por mascota (con IVA):</strong> $<?= number_format($resultado['precio_unitario_bruto']) ?></p>
        <p><strong>🧾 Precio sin IVA:</strong> $<?= number_format($resultado['precio_unitario_sin_iva']) ?></p>
        <p><strong>🟢 Total a pagar (con IVA):</strong> $<?= number_format($resultado['total_bruto']) ?></p>
        <p><strong>🔵 Total sin IVA:</strong> $<?= number_format($resultado['total_sin_iva']) ?></p>
        <p><strong>💰 Utilidad neta estimada:</strong> $<?= number_format($resultado['utilidad_neta']) ?></p>
      </div>
    <?php elseif ($resultado): ?>
      <p class="text-red-500"><?= $resultado['error'] ?></p>
    <?php endif; ?>
  </div>
</body>
</html>
