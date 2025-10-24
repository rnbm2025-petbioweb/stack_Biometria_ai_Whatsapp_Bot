<?php
session_start();

require_once 'sesion_global.php';

if (!isset($_SESSION['id_usuario'])) {
  $_SESSION['id_usuario'] = uniqid('usr_');
}

$mensaje = '';
$preview_img_10 = $_SESSION['preview_img_10'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['img_hfld10']) && $_FILES['img_hfld10']['error'] === UPLOAD_ERR_OK) {
  $archivo = $_FILES['img_hfld10'];
  $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
  $ext_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
  $mime_permitidos = ['image/jpeg', 'image/png', 'image/gif'];
  $tamano_max = 10 * 1024 * 1024;

  if (!in_array($ext, $ext_permitidas)) {
    $mensaje = "❌ Extensión no permitida ($ext).";
  } elseif (!in_array($archivo['type'], $mime_permitidos)) {
    $mensaje = "❌ Tipo MIME no permitido.";
  } elseif ($archivo['size'] > $tamano_max) {
    $mensaje = "❌ Archivo excede los 10MB.";
  } elseif (!getimagesize($archivo['tmp_name'])) {
    $mensaje = "❌ No es una imagen válida.";
  } else {
    $nombre_final = 'hlfd10_' . $_SESSION['id_usuario'] . '_' . time() . '.' . $ext;
    $ruta_destino = 'uploads/' . $nombre_final;

    if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
      $mensaje = "✅ Imagen subida correctamente.";
      $_SESSION['preview_img_10'] = $ruta_destino;
      $preview_img_10 = $ruta_destino;
      header("Location: mockup6.php");
      exit;
    } else {
      $mensaje = "❌ Error al mover archivo.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Paso 6 - Lateral Derecha 15°</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<!-- <body onload="validarPasoActual('mockup5')" class="bg-[#FFF8F0] min-h-screen flex items-center justify-center px-4 py-10"> --> 
  
 <body class="min-h-screen bg-gradient-to-b from-[#FFF8F0] to-[#E0F7FA] p-4">
 

<div class="flex flex-col md:flex-row items-center justify-between mb-10 gap-6 px-4">
  <!-- Texto y botones a la izquierda -->
  <div class="flex-1 text-center md:text-left">
    <h1 class="text-2xl font-bold mb-4">
      🐾 Bienvenido a PETBIO - Registro Nacional Biométrico
    </h1>
    <div class="flex justify-center md:justify-start gap-4 mt-4">
      <button onclick="validarYContinuar()" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded-full shadow">
        ✅ Continuar
      </button>
      
<button onclick="irASiac2025()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-full shadow">
  🌐 Ingresar a SIAC2025
</button>

<script>
  function irASiac2025() {
    window.location.href = 'identidad_rubm.php?forzar_identidad=1';
  }
</script>


    </div>
  </div>

  <!-- Imagen a la derecha -->
  <div class="flex-shrink-0">
    <img src="./imagenes_guias_2025/bienvenida_00011.png" alt="Bienvenida" class="w-64 rounded-xl shadow-lg">
  </div>
</div>




  <div class="bg-white rounded-xl shadow-xl p-6 max-w-3xl w-full">
    <h2 class="text-2xl font-bold text-[#114358] mb-4 text-center">📸 Subir imagen lateral derecha 15°</h2>
    <p class="text-center text-gray-600 mb-6">Captura clara y lateral de la trufa desde el ángulo derecho (15°).</p>

    <!-- Imagen de referencia -->
    <div class="mb-6 text-center">
      <p class="text-sm text-gray-500 mb-2">Ejemplo de imagen esperada:</p>
      <img src="imagenes_guias_2025/frontal_admitida.jpeg" alt="Ejemplo lateral derecho 15°" class="w-48 h-auto mx-auto border rounded shadow">
    </div>

    <!-- Mensaje -->
    <?php if ($mensaje): ?>
      <p class="text-center font-semibold text-<?php echo str_starts_with($mensaje, '✅') ? 'green' : 'red'; ?>-600 mb-4">
        <?= $mensaje ?>
      </p>
    <?php endif; ?>

    <!-- Vista previa -->
    <?php if ($preview_img_10): ?>
      <div class="mb-6 text-center">
        <p class="text-sm text-gray-600 mb-2">Tu imagen actual:</p>
        <img src="<?= htmlspecialchars($preview_img_10) ?>" alt="Previsualización" class="h-48 mx-auto border rounded shadow">
      </div>
    <?php endif; ?>

    <!-- Formulario -->
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
      <input type="file" name="img_hfld10" accept="image/*" required class="block w-full border border-gray-300 rounded p-2 text-sm text-gray-700">
      <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded">
        Subir imagen
      </button>
    </form>

    <!-- Navegación -->
    <div class="flex justify-between mt-6">
      <a href="mockup4.php" class="text-blue-500 hover:underline">⬅️ Paso anterior</a>
      <a href="mockup6.php" class="text-blue-500 hover:underline">➡️ Siguiente paso</a>
    </div>
  </div>

  <script>
    window.addEventListener('DOMContentLoaded', () => {
      localStorage.setItem('paso_petbio', 'mockup5');
    });
  </script>

</body>
</html>
