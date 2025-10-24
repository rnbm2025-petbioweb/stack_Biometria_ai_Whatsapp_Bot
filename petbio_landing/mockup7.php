<?php
session_start();
require_once 'sesion_global.php';

if (!isset($_SESSION['id_usuario'])) {
  $_SESSION['id_usuario'] = uniqid('usr_');
}

$mensaje = '';
$preview_img_hf0 = $_SESSION['preview_img_hf0'] ?? '';

$ext_permitidas = ['jpg', 'jpeg', 'png'];
$mime_permitidos = ['image/jpeg', 'image/png'];
$tamano_max = 10 * 1024 * 1024;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['img_hf0']) && $_FILES['img_hf0']['error'] === UPLOAD_ERR_OK) {
  $archivo = $_FILES['img_hf0'];
  $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

  if (!in_array($ext, $ext_permitidas)) {
    $mensaje .= "❌ Extensión no permitida ($ext).<br>";
  } elseif (!in_array($archivo['type'], $mime_permitidos)) {
    $mensaje .= "❌ Tipo MIME no permitido ({$archivo['type']}).<br>";
  } elseif ($archivo['size'] > $tamano_max) {
    $mensaje .= "❌ El archivo excede los 10MB.<br>";
  } elseif (!getimagesize($archivo['tmp_name'])) {
    $mensaje .= "❌ El archivo no es una imagen válida.<br>";
  } else {
    $nombre_final = 'hf0_' . $_SESSION['id_usuario'] . '_' . time() . '.' . $ext;
    $ruta_destino = 'uploads/' . $nombre_final;

    if (!is_dir('uploads')) {
      mkdir('uploads', 0755, true);
    }

    if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
      $mensaje .= "✅ Imagen frontal subida correctamente.<br>";
      $_SESSION['preview_img_hf0'] = $ruta_destino;
      header("Location: mockup9.php");
      exit;
    } else {
      $mensaje .= "❌ Error al mover la imagen.<br>";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Paso 7 - Imagen Frontal 0°</title>

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
      <a href="https://siac2025.com/contacto.html" class="transition-colors hover:text-white hover:bg-blue-600 px-3 py-2 rounded">Contacto</a>
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

  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#FFF8F0] min-h-screen flex flex-col justify-center items-center p-6">

  <!-- Cabecera -->
  <div class="flex flex-col md:flex-row items-center justify-between mb-10 gap-6 px-4">
    <div class="flex-1 text-center md:text-left">
      <h1 class="text-2xl font-bold mb-4">🐾 PETBIO - Paso 7</h1>
      <p class="text-gray-700 mb-2">Carga la imagen frontal (0°) de la trufa.</p>
      <div class="flex justify-center md:justify-start gap-4 mt-4">
        <a href="mockup6.php" class="bg-gray-300 hover:bg-gray-400 text-black px-4 py-2 rounded-full">⬅️ Anterior</a>
        <button onclick="irASiac2025()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-full shadow">🌐 Ir a SIAC2025</button>
      </div>
    </div>
    <div>
      <img src="imagenes_guias_2025/bienvenida_00011.png" class="w-48 rounded-xl shadow" alt="Mascota PETBIO">
    </div>
  </div>

  <!-- Contenido -->
  <div class="bg-white p-6 rounded-xl shadow-xl max-w-2xl w-full text-center">
    <h2 class="text-2xl font-bold text-[#114358] mb-4">📷 Captura Frontal (0°)</h2>
    <p class="text-gray-600 mb-4">Sube la imagen nítida y centrada de la trufa en ángulo 0°.</p>

    <div class="mb-4">
      <img src="imagenes_guias_2025/frontal_0.jpeg" class="rounded-xl shadow mx-auto max-w-sm" alt="Ejemplo frontal" />
      <p class="text-sm text-gray-500 mt-1 italic">Ejemplo de imagen aceptada (frontal 0°)</p>
    </div>

    <!-- Formulario -->
    <form method="POST" enctype="multipart/form-data" onsubmit="return validarFormulario();">
      <input type="file" name="img_hf0" id="fileInput" accept="image/*" required onchange="procesarImagenFrontal()" class="block w-full border border-gray-300 p-2 rounded mb-2" />
      <p class="text-sm text-gray-700 mb-2">Nombre sugerido: <span id="nombreSugerido" class="font-mono text-blue-800"></span></p>

      <!-- Vista previa -->
      <div class="my-4">
        <img id="previewImagen" class="hidden mx-auto rounded-xl shadow max-h-64" alt="Vista previa imagen">
      </div>

      <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-full shadow">✅ Continuar</button>
    </form>

    <!-- Mensaje de validación -->
    <?php if ($mensaje): ?>
      <div class="mt-6 text-sm text-left <?= str_contains($mensaje, '✅') ? 'text-green-600' : 'text-red-600' ?>">
        <?= $mensaje ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Footer -->
  <footer class="text-sm text-gray-500 mt-6">© 2025 PETBIO | Registro Biométrico de Mascotas</footer>

  <!-- Script -->
  <script>
    function irASiac2025() {
      window.location.href = 'identidad_rubm.php?forzar_identidad=1';
    }

    function generarNombreSugerido() {
      const datos = {
        clase: localStorage.getItem('clase_mascota') || 'clase',
        raza: localStorage.getItem('raza') || 'raza',
        edad: localStorage.getItem('edad') || 'edad',
        cuidador: localStorage.getItem('apellidos') || 'cuidador',
        ciudad: localStorage.getItem('ciudad') || 'ciudad',
        barrio: localStorage.getItem('barrio') || 'barrio',
        cp: localStorage.getItem('codigo_postal') || '00000',
        nombre: localStorage.getItem('nombre') || 'nombre'
      };
      return `${datos.clase}_${datos.raza}_${datos.edad}_${datos.cuidador}_${datos.ciudad}_${datos.barrio}_${datos.cp}_${datos.nombre}_hf0.jpg`
        .toLowerCase().replace(/\s+/g, '_');
    }

    function procesarImagenFrontal() {
      const input = document.getElementById('fileInput');
      const file = input.files[0];
      const preview = document.getElementById('previewImagen');

      if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
          preview.src = e.target.result;
          preview.classList.remove('hidden');
          localStorage.setItem('img_hf0', e.target.result);
        };
        reader.readAsDataURL(file);
      } else {
        alert("⚠️ Archivo inválido. Solo se aceptan imágenes.");
        return;
      }

      const sugerido = generarNombreSugerido();
      document.getElementById('nombreSugerido').textContent = sugerido;
      localStorage.setItem('foto_frontal_nombre', sugerido);
    }

    function validarFormulario() {
      const fileInput = document.getElementById('fileInput');
      const archivo = fileInput.files[0];
      if (!archivo) {
        alert("⚠️ Por favor selecciona una imagen antes de continuar.");
        return false;
      }

      const extPermitidas = ['jpg', 'jpeg', 'png'];
      const extension = archivo.name.split('.').pop().toLowerCase();

      if (!extPermitidas.includes(extension)) {
        alert('❌ Archivo no permitido. Solo se aceptan JPG o PNG.');
        return false;
      }

      return true;
    }

    window.addEventListener('DOMContentLoaded', () => {
      document.getElementById('nombreSugerido').textContent = generarNombreSugerido();
      const previewSrc = localStorage.getItem('img_hf0');
      if (previewSrc) {
        const preview = document.getElementById('previewImagen');
        preview.src = previewSrc;
        preview.classList.remove('hidden');
      }
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
