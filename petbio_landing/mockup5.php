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
  $ext_permitidas = ['jpg', 'jpeg', 'png'];
  $mime_permitidos = ['image/jpeg', 'image/png'];
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

    if (!is_dir('uploads')) {
      mkdir('uploads', 0755, true);
    }

    if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
      $mensaje = "✅ Imagen subida correctamente.";
      $_SESSION['preview_img_10'] = $ruta_destino;
      $preview_img_10 = $ruta_destino;
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
<body class="min-h-screen bg-gradient-to-b from-[#FFF8F0] to-[#E0F7FA] p-4">

<header class="bg-white shadow w-full">
  <div class="max-w-screen-xl mx-auto px-4 py-4 flex justify-between items-center">
    <a href="/" class="flex items-center gap-3 text-xl font-bold text-gray-800">
      <img src="/imagenes_guias_2025/logo_siac_preto.jpeg" alt="Logo SIAC" class="h-10 w-auto rounded">
      <span class="text-blue-800">🐾 SIAC2025</span>
    </a>
    <button onclick="toggleMobileMenu()" class="md:hidden text-gray-700 hover:text-blue-600 transition">
      <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>
  </div>
  <div id="mobileMenu" class="md:hidden hidden px-4 pb-4">
    <nav class="flex flex-col gap-3 text-base text-gray-700 font-medium">
      <a href="/" class="hover:text-white hover:bg-blue-600 px-3 py-2 rounded">Inicio</a>
      <a href="https://petbio.siac2025.com/registropetbio.php" target="_blank" class="hover:text-white hover:bg-blue-600 px-3 py-2 rounded">Registro PETBIO</a>
      <a href="https://registro.siac2025.com/" target="_blank" class="hover:text-white hover:bg-blue-600 px-3 py-2 rounded">PETBIO</a>
      <a href="https://petbio.siac2025.com/loginpetbio.php" target="_blank" class="hover:text-white hover:bg-blue-600 px-3 py-2 rounded">Login PETBIO</a>
      <a href="https://petbio.siac2025.com/identidad_rubm.php" target="_blank" class="hover:text-white hover:bg-blue-600 px-3 py-2 rounded">Identidad RNBM</a>
      <a href="https://registro.siac2025.com/category/noticias-mas/" target="_blank" class="hover:text-white hover:bg-blue-600 px-3 py-2 rounded">Noticias</a>
      <a href="/contacto.html" class="hover:text-white hover:bg-blue-600 px-3 py-2 rounded">Contacto</a>
      <a href="https://petbio11rubm2025.blogspot.com/" target="_blank" class="hover:text-white hover:bg-blue-600 px-3 py-2 rounded">Blog PETBIO</a>
    </nav>
  </div>
</header>

<div class="flex flex-col md:flex-row items-center justify-between mb-10 gap-6 px-4 mt-6">
  <div class="flex-1 text-center md:text-left">
    <h1 class="text-2xl font-bold mb-4">🐾 PETBIO - Registro Nacional Biométrico</h1>
    <div class="flex justify-center md:justify-start gap-4 mt-4">
      <button onclick="validarYContinuar()" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded-full shadow">
        ✅ Continuar
      </button>
      <button onclick="irASiac2025()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-full shadow">
        🌐 Ir a SIAC2025
      </button>
    </div>
  </div>
  <div class="flex-shrink-0">
    <img src="./imagenes_guias_2025/bienvenida_00011.png" alt="Bienvenida" class="w-64 rounded-xl shadow-lg">
  </div>
</div>

<div class="bg-white rounded-xl shadow-xl p-6 max-w-3xl w-full mx-auto">
  <h2 class="text-2xl font-bold text-[#114358] mb-4 text-center">📸 Subir imagen lateral derecha 15°</h2>
  <p class="text-center text-gray-600 mb-6">Captura clara y lateral de la trufa desde el ángulo derecho (15°).</p>

  <div class="mb-6 text-center">
    <p class="text-sm text-gray-500 mb-2">Ejemplo de imagen esperada:</p>
    <img src="imagenes_guias_2025/frontal_admitida.jpeg" alt="Ejemplo lateral derecho 15°" class="w-48 h-auto mx-auto border rounded shadow">
  </div>

  <?php if ($mensaje): ?>
    <p class="text-center font-semibold text-<?php echo str_starts_with($mensaje, '✅') ? 'green' : 'red'; ?>-600 mb-4">
      <?= $mensaje ?>
    </p>
  <?php endif; ?>

  <?php if ($preview_img_10): ?>
    <div class="mb-6 text-center">
      <p class="text-sm text-gray-600 mb-2">Tu imagen actual:</p>
      <img src="<?= htmlspecialchars($preview_img_10) ?>" alt="Previsualización" class="h-48 mx-auto border rounded shadow">
    </div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" class="space-y-4" id="formulario_img10">
    <input type="file" name="img_hfld10" accept="image/*" required
           class="block w-full border border-gray-300 rounded p-2 text-sm text-gray-700"
           onchange="mostrarVistaPrevia(this, 'preview_img_10')">

    <img id="preview_img_10" src="<?= htmlspecialchars($preview_img_10 ?: '') ?>"
         class="w-64 mx-auto rounded shadow border mt-4 <?= $preview_img_10 ? '' : 'hidden' ?>"
         alt="Vista previa lateral 15°">

    <button type="submit"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded">
      Subir imagen
    </button>
  </form>

  <div class="flex justify-between mt-6">
    <a href="mockup4.php" class="text-blue-500 hover:underline">⬅️ Paso anterior</a>
    <?php if ($preview_img_10): ?>
      <a href="mockup6.php" class="text-blue-500 hover:underline">➡️ Siguiente paso</a>
    <?php endif; ?>
  </div>
</div>

<script>
  function toggleMobileMenu() {
    document.getElementById("mobileMenu").classList.toggle("hidden");
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

  document.getElementById('formulario_img10').addEventListener('submit', function(e) {
    const archivo = document.querySelector('input[type=file]').files[0];
    if (archivo) {
      const ext = archivo.name.split('.').pop().toLowerCase();
      const permitidas = ['jpg', 'jpeg', 'png'];
      if (!permitidas.includes(ext)) {
        alert('❌ Solo se aceptan archivos JPG o PNG.');
        e.preventDefault();
      }
    }
  });

  function irASiac2025() {
    window.location.href = 'identidad_rubm.php?forzar_identidad=1';
  }

  function validarYContinuar() {
    const vista = document.getElementById('preview_img_10');
    if (!vista || vista.classList.contains('hidden') || !vista.src || vista.src.trim() === "") {
      alert('⚠️ Por favor, sube una imagen válida antes de continuar.');
    } else {
      window.location.href = 'mockup6.php';
    }
  }

  window.addEventListener('DOMContentLoaded', () => {
    localStorage.setItem('paso_petbio', 'mockup5');
    <?php if ($preview_img_10): ?>
      localStorage.setItem('img_hfld15', '<?= $preview_img_10 ?>');
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
  <button onclick="toggleComboBox()" class="absolute -top-2 -right-2 bg-blue-500 hover:bg-blue-600 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center shadow-md" title="Mostrar/Ocultar menú">
    <span id="toggleIcon">−</span>
  </button>
  <label for="mockupSelector" class="text-sm font-semibold">Ir a paso:</label>
  <select id="mockupSelector" class="ml-2 border border-gray-400 p-1 rounded" onchange="irAMockup(this.value)">
    <option value="">Seleccionar</option>
    <option value="mockup0.php">Paso 1 – Nombre mascota / Foto de Perfil</option>
    <option value="mockup1.php">Paso 2 – Clase / Raza / Edad</option>
    <option value="mockup2.php">Paso 3 – Imagen lateral izquierda</option>
    <option value="mockup3.php">Paso 4 – Frontal lateral derecha 10°</option>
    <option value="mockup4.php">Paso 5 – Frontal lateral izquierda 10°</option>
    <option value="mockup5.php">Paso 6 – Nariz 15°</option>
    <option value="mockup6.php">Paso 7 – Nariz 30°</option>
    <option value="mockup7.php">Paso 8 – Frontal nariz 0°</option>
    <option value="mockup9.php">Paso 9 – Confirmación</option>
  </select>
</div>

<script>
  function irAMockup(url) {
    if (url) window.location.href = url;
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
