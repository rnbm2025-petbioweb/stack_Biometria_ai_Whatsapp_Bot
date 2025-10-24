<?php
session_start();
require_once 'sesion_global.php';

if (!isset($_SESSION['id_usuario'])) {
  echo "<!DOCTYPE html><html><head><script>
    alert('⚠️ Debes iniciar sesión primero en PETBIO.');
    window.location.href = 'https://petbio.siac2025.com/loginpetbio.php';
  </script></head><body></body></html>";
  exit;
}

$mensaje = '';
$preview_hf15 = $_SESSION['preview_hf15'] ?? '';

$ext_permitidas = ['jpg', 'jpeg', 'png'];
$mime_permitidos = ['image/jpeg', 'image/png'];
$tamano_max = 10 * 1024 * 1024;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['img_hf15']) && $_FILES['img_hf15']['error'] === UPLOAD_ERR_OK) {
  $archivo = $_FILES['img_hf15'];
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
    $nombre_final = 'hf15_' . $_SESSION['id_usuario'] . '_' . time() . '.' . $ext;
    $carpeta_uploads = 'uploads/';
    $ruta_destino = $carpeta_uploads . $nombre_final;

    // Crear carpeta uploads si no existe
    if (!is_dir($carpeta_uploads)) {
      mkdir($carpeta_uploads, 0775, true);
    }

    if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
      $preview_hf15 = $ruta_destino;
      $_SESSION['preview_hf15'] = $ruta_destino;
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
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Paso 4 - Imagen frontal derecho 15°</title>

  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gradient-to-b from-[#FFF8F0] to-[#E0F7FA] p-4">

<header class="bg-white shadow w-full">
  <div class="max-w-screen-xl mx-auto px-4 py-4 flex justify-between items-center">
    <a href="/" class="flex items-center gap-3 text-xl font-bold text-gray-800">
      <img src="/imagenes_guias_2025/logo_siac_preto.jpeg" alt="Logo SIAC" class="h-10 w-auto rounded" />
      <span class="text-blue-800">🐾 SIAC2025</span>
    </a>

    <button onclick="toggleMobileMenu()" class="md:hidden text-gray-700 hover:text-blue-600 transition">
      <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
      </svg>
    </button>
  </div>

  <div id="mobileMenu" class="md:hidden hidden px-4 pb-4">
    <nav class="flex flex-col gap-3 text-base text-gray-700 font-medium">
      <a href="/" class="transition-colors hover:text-white hover:bg-blue-600 px-3 py-2 rounded">Inicio</a>
      <a href="https://petbio.siac2025.com/registropetbio.php" class="transition-colors hover:text-white hover:bg-blue-600 px-3 py-2 rounded" target="_blank">Registro PETBIO</a>
      <a href="https://registro.siac2025.com/" class="transition-colors hover:text-white hover:bg-blue-600 px-3 py-2 rounded" target="_blank">PETBIO</a>
      <a href="https://petbio.siac2025.com/loginpetbio.php" class="transition-colors hover:text-white hover:bg-blue-600 px-3 py-2 rounded" target="_blank">Login PETBIO</a>
      <a href="https://petbio.siac2025.com/identidad_rubm.php" class="transition-colors hover:text-white hover:bg-blue-600 px-3 py-2 rounded" target="_blank">Identidad RNBM</a>
      <a href="https://registro.siac2025.com/category/noticias-mas/" class="transition-colors hover:text-white hover:bg-blue-600 px-3 py-2 rounded" target="_blank">Noticias</a>
      <a href="/contacto.html" class="transition-colors hover:text-white hover:bg-blue-600 px-3 py-2 rounded">Contacto</a>
      <a href="https://petbio11rubm2025.blogspot.com/" class="transition-colors hover:text-white hover:bg-blue-600 px-3 py-2 rounded" target="_blank">Blog PETBIO</a>
    </nav>
  </div>
</header>

<!-- CONTENIDO PRINCIPAL -->
<div class="bg-white rounded-xl shadow-lg p-6 max-w-2xl w-full space-y-6 mx-auto mt-8">
  <h2 class="text-2xl font-bold text-[#114358] text-center">📸 Paso 4: Imagen frontal lateral derecha de 10° a 15°</h2>
  <p class="text-gray-700 text-center">
    Captura una imagen clara del frente de la trufa de tu mascota a 15°.
  </p>

  <?php if (!empty($mensaje)): ?>
    <div class="p-4 rounded text-sm <?= str_starts_with($mensaje, '✅') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
      <?= htmlspecialchars($mensaje) ?>
    </div>
  <?php endif; ?>

  <div class="text-center">
    <p class="text-sm text-gray-500 mb-2">Ejemplo esperado:</p>
    <img src="imagenes_guias_2025/fld_10.jpeg" alt="Ejemplo 15° frontal derecho" class="h-40 mx-auto rounded shadow border" />
  </div>

  <form method="POST" enctype="multipart/form-data" class="space-y-4">
    <div class="text-center">
      <input
        type="file"
        name="img_hf15"
        accept="image/*"
        required
        class="block w-full text-sm text-gray-700 border rounded p-2"
        onchange="mostrarVistaPrevia(this, 'preview_hf15')"
      />
      <img
        id="preview_hf15"
        src="<?= htmlspecialchars($preview_hf15 ?: '') ?>"
        class="w-64 mx-auto rounded shadow border mt-4 <?= $preview_hf15 ? '' : 'hidden' ?>"
        alt="Vista previa 15°"
      />
    </div>

    <div class="flex justify-between pt-2">
      <a href="mockup2.php" class="text-[#114358] hover:underline">⬅️ Atrás</a>
      <button type="submit" class="bg-[#114358] text-white px-6 py-2 rounded-full shadow hover:bg-[#0d3d4d]">
        Subir Imagen
      </button>
    </div>
  </form>

  <?php if ($preview_hf15): ?>
    <div class="text-center pt-6">
      <button onclick="window.location.href='mockup4.php'" class="bg-green-600 text-white px-6 py-2 rounded-full shadow hover:bg-green-700">
        Continuar ➡️
      </button>
    </div>
  <?php endif; ?>
</div>

<!-- Combo Box de navegación -->
<div
  id="comboBoxContainer"
  class="fixed top-4 right-4 z-50 bg-white shadow-lg p-2 rounded-lg border border-gray-300 transition-all duration-300 ease-in-out"
>
  <button
    onclick="toggleComboBox()"
    class="absolute -top-2 -right-2 bg-blue-500 hover:bg-blue-600 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center shadow-md focus:outline-none"
    title="Mostrar/Ocultar menú"
  >
    <span id="toggleIcon">−</span>
  </button>
  <label for="mockupSelector" class="text-sm font-semibold">Ir a paso:</label>
  <select id="mockupSelector" class="ml-2 border border-gray-400 p-1 rounded" onchange="irAMockup(this.value)">
    <option value="">Seleccionar</option>
    <option value="mockup0.php">Paso 1 – Nombre mascota / Foto de Perfil</option>
    <option value="mockup1.php">Paso 2 – Clase / Raza / Edad / Imagen lateral derecha</option>
    <option value="mockup2.php">Paso 3 – Imagen lateral izquierda</option>
    <option value="mockup3.php" selected>Paso 4 – Imagen frontal lateral derecha 10° a 15°</option>
    <option value="mockup4.php">Paso 5 – Imagen frontal lateral izquierda 10° a 15°</option>
    <option value="mockup5.php">Paso 6 – Imagen nariz 15°</option>
    <option value="mockup6.php">Paso 7 – Imagen nariz 30°</option>
    <option value="mockup7.php">Paso 8 – Imagen frontal nariz 0°</option>
    <option value="mockup9.php">Paso 9 – Confirmación</option>
  </select>
</div>

<script>
  function toggleMobileMenu() {
    const menu = document.getElementById("mobileMenu");
    menu.classList.toggle("hidden");
  }

  function irAMockup(url) {
    if (url) {
      window.location.href = url;
    }
  }

  function toggleComboBox() {
    const container = document.getElementById('comboBoxContainer');
    const icon = document.getElementById('toggleIcon');
    container.classList.toggle('minimizado');
    icon.textContent = container.classList.contains('minimizado') ? '+' : '−';
  }

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

  function validarYContinuar() {
    const previewImg = document.getElementById('preview_hf15');
    if (previewImg && previewImg.src && !previewImg.classList.contains('hidden')) {
      window.location.href = 'mockup4.php';
    } else {
      alert('Por favor sube una imagen para continuar.');
    }
  }

  window.addEventListener('DOMContentLoaded', () => {
    localStorage.setItem('paso_petbio', 'mockup4');
    <?php if ($preview_hf15): ?>
      localStorage.setItem('img_hf15', '<?= $preview_hf15 ?>');
    <?php endif; ?>
  });
</script>

<style>
  .minimizado select,
  .minimizado label {
    display: none;
  }
</style>

</body>
</html>
