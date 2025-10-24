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
$preview_img_15i = $_SESSION['preview_img_15i'] ?? '';

$ext_permitidas = ['jpg', 'jpeg', 'png'];
$mime_permitidos = ['image/jpeg', 'image/png'];
$tamano_max = 10 * 1024 * 1024;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['img_hfli15']) && $_FILES['img_hfli15']['error'] === UPLOAD_ERR_OK) {
  $archivo = $_FILES['img_hfli15'];
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
    $nombre_final = 'hfli15_' . $_SESSION['id_usuario'] . '_' . time() . '.' . $ext;
    $ruta_destino = 'uploads/' . $nombre_final;

    if (!is_dir('uploads')) {
      mkdir('uploads', 0755, true);
    }

    if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
      $_SESSION['preview_img_15i'] = $ruta_destino;
      $preview_img_15i = $ruta_destino;
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
  <title>Mockup 6 - Lateral Izquierdo 15°</title>

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

<!-- Cabecera -->
<div class="flex flex-col md:flex-row items-center justify-between mb-10 gap-6 px-4">
  <div class="flex-1 text-center md:text-left">
    <h1 class="text-2xl font-bold mb-4">🐾 Bienvenido a PETBIO - Registro Nacional Biométrico</h1>
    <div class="flex justify-center md:justify-start gap-4 mt-4">
      <button onclick="window.location.href='mockup7.php'" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded-full shadow">
        ✅ Continuar
      </button>
      <button onclick="window.location.href='identidad_rubm.php?forzar_identidad=1'" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-full shadow">
        🌐 Ingresar a SIAC2025
      </button>
    </div>
  </div>
  <div class="flex-shrink-0">
    <img src="./imagenes_guias_2025/bienvenida_00011.png" alt="Bienvenida" class="w-64 rounded-xl shadow-lg">
  </div>
</div>

<!-- Contenido principal -->
<div class="bg-white rounded-xl shadow-xl p-6 max-w-3xl mx-auto w-full">
  <h2 class="text-2xl font-bold text-[#114358] mb-4 text-center">📸 Subir imagen lateral izquierda 15°</h2>
  <p class="text-center text-gray-600 mb-6">Captura clara y lateral de la trufa desde el ángulo izquierdo (15°).</p>

  <!-- Imagen guía -->
  <div class="text-center mb-6">
    <p class="text-sm text-gray-500 mb-2">Ejemplo de imagen esperada:</p>
    <img src="imagenes_guias_2025/fld_15.jpeg" alt="Ejemplo 15°" class="h-40 mx-auto rounded shadow border">
  </div>

  <!-- Mensaje -->
  <?php if (!empty($mensaje)): ?>
    <div class="p-4 rounded text-sm <?= str_starts_with($mensaje, '✅') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
      <?= $mensaje ?>
    </div>
  <?php endif; ?>

  <!-- Formulario -->
  <form method="POST" enctype="multipart/form-data" class="space-y-4">
    <div class="text-center">
      <input type="file" name="img_hfli15" accept="image/*" required
             class="block w-full text-sm text-gray-700 border rounded p-2"
             onchange="mostrarVistaPrevia(this, 'preview_img_15i')">

      <img id="preview_img_15i"
           src="<?= htmlspecialchars($preview_img_15i ?: '') ?>"
           class="w-64 mx-auto rounded shadow border mt-4 <?= $preview_img_15i ? '' : 'hidden' ?>"
           alt="Vista previa 15° izquierda">
    </div>

    <div class="flex justify-between pt-2">
      <a href="mockup5.php" class="text-[#114358] hover:underline">⬅️ Paso anterior</a>
      <button type="submit"
              class="bg-[#114358] text-white px-6 py-2 rounded-full shadow hover:bg-[#0d3d4d]">
        Subir Imagen
      </button>
    </div>
  </form>

  <?php if ($preview_img_15i): ?>
    <div class="text-center pt-6">
      <button onclick="window.location.href='mockup7.php'"
              class="bg-green-600 text-white px-6 py-2 rounded-full shadow hover:bg-green-700">
        Continuar ➡️
      </button>
    </div>
  <?php endif; ?>
</div>

<!-- Script para vista previa -->
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
    localStorage.setItem('paso_petbio', 'mockup6');
    <?php if ($preview_img_15i): ?>
      localStorage.setItem('img_hfli15', '<?= $preview_img_15i ?>');
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
