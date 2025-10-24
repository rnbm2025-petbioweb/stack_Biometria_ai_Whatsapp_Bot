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
  <title>Paso 5 - Imagen Frontal 15° a 30°</title>

<header class="bg-white shadow w-full">
  <div class="max-w-screen-xl mx-auto px-4 py-4 flex justify-between items-center">
    <!-- Logo -->
    <a href="/" class="flex items-center gap-3 text-xl font-bold text-gray-800">
      <img src="/imagenes_guias_2025/logo_siac_preto.jpeg" alt="Logo SIAC" class="h-10 w-auto rounded" />
      <span class="text-blue-800">🐾 SIAC2025</span>
    </a>

    <!-- Botón menú móvil -->
    <button onclick="toggleMobileMenu()" class="md:hidden text-gray-700 hover:text-blue-600 transition">
      <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
      </svg>
    </button>
  </div>

  <!-- Menú móvil -->
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

<script>
  function toggleMobileMenu() {
    const menu = document.getElementById("mobileMenu");
    menu.classList.toggle("hidden");
  }
</script>


  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-b from-[#FFF8F0] to-[#E0F7FA] p-4">

<div class="flex flex-col md:flex-row items-center justify-between mb-10 gap-6 px-4">
  <div class="flex-1 text-center md:text-left">
    <h1 class="text-2xl font-bold mb-4">🐾 PETBIO - Registro Nacional Biométrico</h1>
    <div class="flex justify-center md:justify-start gap-4 mt-4">
      <button onclick="window.location.href='mockup3.php'" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold px-6 py-2 rounded-full shadow">⬅️ Atrás</button>
      <button onclick="window.location.href='identidad_rubm.php?forzar_identidad=1'" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-full shadow">🌐 Ir a SIAC2025</button>
    </div>
  </div>
  <div class="flex-shrink-0">
    <img src="./imagenes_guias_2025/bienvenida_00011.png" alt="Bienvenida" class="w-64 rounded-xl shadow-lg">
  </div>
</div>

<div class="bg-white rounded-xl shadow-lg p-6 max-w-2xl w-full mx-auto space-y-6">
  <h2 class="text-2xl font-bold text-[#114358] text-center">📸 Paso 5: Imagen frontal de 15° a 30°</h2>

  <h3 class="text-lg font-semibold text-gray-700 flex items-center gap-2">⚖️ Consideraciones importantes:</h3>
  <p class="text-sm text-gray-600">Asegúrate de que la imagen esté bien enfocada, sin sombras ni desenfoques.</p>

  <?php if (!empty($mensaje)): ?>
    <div class="p-4 rounded text-sm <?= str_starts_with($mensaje, '✅') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
      <?= $mensaje ?>
    </div>
  <?php endif; ?>

  <!-- Imagen de ejemplo -->
  <div class="text-center">
    <label class="label-clara block text-sm font-medium text-gray-700">Frontal 15° a 30°</label>
    <img src="imagenes_guias_2025/frontal_15_30.jpeg" width="150" alt="Ejemplo Frontal 15° a 30°" class="mx-auto my-2 rounded shadow border">
  </div>

  <!-- Formulario -->
  <form method="POST" enctype="multipart/form-data" class="space-y-4" id="formulario_hf30">
    <div class="text-center">
      <input type="file" name="img_hf30" accept="image/*" required
             class="block w-full text-sm text-gray-700 border rounded p-2 mx-auto"
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

  // Validación JS antes de subir
  document.getElementById('formulario_hf30').addEventListener('submit', function(e) {
    const archivo = document.querySelector('input[type=file]').files[0];
    if (archivo) {
      const ext = archivo.name.split('.').pop().toLowerCase();
      const permitidas = ['jpg', 'jpeg', 'png'];
      if (!permitidas.includes(ext)) {
        alert('❌ Archivo no permitido. Solo se aceptan JPG o PNG.');
        e.preventDefault();
      }
    }
  });

  // Guardar paso actual y preview
  window.addEventListener('DOMContentLoaded', () => {
    localStorage.setItem('paso_petbio', 'mockup4');
    <?php if ($preview_hf30): ?>
      localStorage.setItem('img_hf30', '<?= $preview_hf30 ?>');
    <?php endif; ?>
  });
</script>

<style>
  .minimizado select,
  .minimizado label {
    display: none;
  }
</style>

<div id="comboBoxContainer" class="fixed top-4 right-4 z-50 bg-white shadow-lg p-2 rounded-lg border border-gray-300 transition-all duration-300 ease-in-out">
  <button onclick="toggleComboBox()" class="absolute -top-2 -right-2 bg-blue-500 hover:bg-blue-600 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center shadow-md focus:outline-none" title="Mostrar/Ocultar menú">
    <span id="toggleIcon">−</span>
  </button>
  <label for="mockupSelector" class="text-sm font-semibold">Ir a paso:</label>
  <select id="mockupSelector" class="ml-2 border border-gray-400 p-1 rounded" onchange="irAMockup(this.value)">
    <option value="">Seleccionar</option>
    <option value="mockup0.php">Paso 1 – Nombre mascota / Foto de Perfil</option> 
    <option value="mockup1.php">Paso 2 – Clase / Raza /edad/ Imagen Cuerpo lado lateral derecha</option>
    <option value="mockup2.php">Paso 3 – Imagen Cuerpo lado lateral izquierdo</option>
    <option value="mockup3.php">Paso 4 – Imagen frontal lateral derecha 10° a 15°</option>
    <option value="mockup4.php">Paso 5 – Imagen frontal lateral izquierda 10° a 15°</option>
    <option value="mockup5.php">Paso 6 – Imagen nariz 15°</option>
    <option value="mockup6.php">Paso 7 – Imagen nariz 30°</option>
    <option value="mockup7.php">Paso 8 – Imagen frontal nariz 0°</option>
    <option value="mockup9.php">Paso 9 – Confirmación</option>
  </select>
</div>

<script>
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
</script>


</body>
</html>
