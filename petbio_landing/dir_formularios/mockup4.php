<?php
session_start();



require_once 'sesion_global.php';

if (!isset($_SESSION['id_usuario'])) {
  echo "<script>
    alert('⚠️ Debes iniciar sesión primero en PETBIO.');
    window.location.href = 'https://petbio.siac2025.com/loginpetbio.php';
  </script>";
  exit;
}

$mensaje = '';
$preview_hf30 = $_SESSION['preview_hf30'] ?? '';

$ext_permitidas = ['jpg', 'jpeg', 'png'];
$mime_permitidos = ['image/jpeg', 'image/png'];
$tamano_max = 10 * 1024 * 1024;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['img_hf30']) && $_FILES['img_hf30']['error'] === UPLOAD_ERR_OK) {
  $archivo = $_FILES['img_hf30'];
  $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

  if (!in_array($ext, $ext_permitidas)) {
    $mensaje = "❌ Extensión no permitida ($ext).";
  } elseif (!in_array($archivo['type'], $mime_permitidos)) {
    $mensaje = "❌ Tipo MIME no permitido ({$archivo['type']}).";
  } elseif ($archivo['size'] > $tamano_max) {
    $mensaje = "❌ Archivo excede los 10MB.";
  } elseif (!getimagesize($archivo['tmp_name'])) {
    $mensaje = "❌ Archivo no es una imagen válida.";
  } else {
    $nombre_final = 'hf30_' . $_SESSION['id_usuario'] . '_' . time() . '.' . $ext;
    $ruta_destino = 'uploads/' . $nombre_final;

    if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
      $preview_hf30 = $ruta_destino;
      $_SESSION['preview_hf30'] = $ruta_destino;
      $mensaje = "✅ Imagen subida correctamente.";
    } else {
      $mensaje = "❌ Error al mover el archivo.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Paso 5 - Imagen Frontal de 15 a 30°</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<!-- <body class="min-h-screen bg-gradient-to-b from-[#FFF8F0] to-[#E0F7FA] flex items-center justify-center p-4"> --> 
    
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




  <div class="bg-white rounded-xl shadow-lg p-6 max-w-2xl w-full space-y-6">
    <h2 class="text-2xl font-bold text-[#114358] text-center">📸 Paso 5: Imagen frontal de 15° a 30°</h2>

    <p class="text-gray-700 text-center">
      Captura una imagen clara del frente de la trufa de tu mascota a 30°.
    </p>

    <?php if (!empty($mensaje)): ?>
      <div class="p-4 rounded text-sm <?= str_starts_with($mensaje, '✅') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
        <?= $mensaje ?>
      </div>
    <?php endif; ?>

    <!-- Ejemplo visual (opcional) -->
    <div class="text-center">
      <p class="text-sm text-gray-500 mb-2">Ejemplo esperado:</p>
      <img src="imagenes_guias_2025/fld_15.jpeg" alt="Ejemplo 15° Frontal Derecho" class="h-40 mx-auto rounded shadow border">
    </div>

    <!-- Formulario -->
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
      <div class="text-center">
        <input type="file" name="img_hf30" accept="image/*" required
               class="block w-full text-sm text-gray-700 border rounded p-2"
               onchange="mostrarVistaPrevia(this, 'preview_hf30')">

        <img id="preview_hf30"
             src="<?= htmlspecialchars($preview_hf30 ?: '') ?>"
             class="w-64 mx-auto rounded shadow border mt-4 <?= $preview_hf30 ? '' : 'hidden' ?>"
             alt="Vista previa 30°">
      </div>

      <div class="flex justify-between pt-2">
        <a href="mockup3.php" class="text-[#114358] hover:underline">⬅️ Atrás</a>
        <button type="submit"
                class="bg-[#114358] text-white px-6 py-2 rounded-full shadow hover:bg-[#0d3d4d]">
          Subir Imagen
        </button>
      </div>
    </form>

    <?php if ($preview_hf30): ?>
      <div class="text-center pt-6">
        <button onclick="window.location.href='mockup5.php'"
                class="bg-green-600 text-white px-6 py-2 rounded-full shadow hover:bg-green-700">
          Continuar ➡️
        </button>
      </div>
    <?php endif; ?>
  </div>

  <!-- Scripts -->
  <script>
    function mostrarVistaPrevia(input, idPreview) {
      const file = input.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          const img = document.getElementById(idPreview);
          img.src = e.target.result;
          img.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
      }
    }

    window.addEventListener('DOMContentLoaded', () => {
      localStorage.setItem('paso_petbio', 'mockup4');
      <?php if ($preview_hf30): ?>
        localStorage.setItem('foto30', '<?= $preview_hf30 ?>');
      <?php endif; ?>
    });
  </script>

</body>
</html>
