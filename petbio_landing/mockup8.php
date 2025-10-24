<?php
session_start();

// Asignar ID temporal si no hay sesión (solo para pruebas)
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

    if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
      $mensaje .= "✅ Imagen frontal subida correctamente.<br>";
      $preview_img_hf0 = $ruta_destino;
      $_SESSION['preview_img_hf0'] = $ruta_destino;

      // Redireccionar a mockup9.php si la subida fue exitosa
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
  <title>Mockup 6 - Captura Frontal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#FFF8F0] min-h-screen flex flex-col justify-center items-center p-6">

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
    </div>
  </div>

  <!-- Imagen a la derecha -->
  <div class="flex-shrink-0">
    <img src="./imagenes_guias_2025/bienvenida_00011.png" alt="Bienvenida" class="w-64 rounded-xl shadow-lg">
  </div>
</div>

<div class="bg-white rounded-xl shadow-xl p-6 max-w-2xl w-full text-center">
  <h2 class="text-3xl font-bold text-[#114358] mb-4">Paso 6: Captura Frontal de la Trufa</h2>

  <p class="text-gray-700 text-justify mb-4">
    📷 Estamos casi listos. Sube la imagen de la trufa en ángulo frontal 0° de tu mascota.
  </p>

  <!-- Guías visuales -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div>
      <img src="imagenes_guias_2025/frontal_0.jpeg" alt="Frontal correcta" class="rounded-xl shadow" />
      <p class="text-sm mt-2 text-gray-600">Ejemplo de enfoque frontal correcto</p>
    </div>
    <div>
      <img src="imagenes_guias_2025/frontal_admitida.jpeg" alt="Frontal admitida" class="rounded-xl shadow" />
      <p class="text-sm mt-2 text-gray-600">Foto frontal aceptable</p>
    </div>
  </div>

  <div class="mb-6">
    <img src="imagenes_guias_2025/f_0_enfoque.jpeg" alt="Instrucciones" class="rounded-xl shadow max-w-md mx-auto" />
    <p class="text-sm mt-2 text-gray-600">
      Captura la trufa en ángulo 0°, enfocando de cerca con buena luz.
    </p>
  </div>

  <!-- Formulario de carga de imagen -->
  <form method="POST" enctype="multipart/form-data" class="w-full" onsubmit="return validarFormulario()">
    <div class="text-left">
      <label for="fileInput" class="block text-[#114358] font-semibold mb-2">
        📅 Sube imagen frontal (0°):
      </label>
      <input
        id="fileInput"
        name="img_hf0"
        type="file"
        accept="image/*"
        onchange="procesarImagenFrontal()"
        required
        class="block w-full text-sm text-gray-700 mb-2"
      />
      <p class="text-sm text-gray-600">
        Nombre sugerido: <span id="nombreSugerido" class="font-mono text-[#114358]"></span>
      </p>
    </div>

    <!-- Vista previa -->
    <div class="my-4">
      <img id="previewImagen" class="hidden mt-2 rounded-xl shadow mx-auto max-h-64" alt="Vista previa" />
    </div>

    <!-- Navegación -->
    <div class="flex justify-between mt-6">
      <a href="mockup6.php" class="text-blue-500 hover:underline">⬅️ Paso anterior</a>
      <a href="mockup9.php" class="text-blue-500 hover:underline">➡️ Siguiente paso</a>
    </div>

    <button type="submit"
      class="mt-4 bg-[#114358] text-white px-6 py-2 rounded-full shadow hover:bg-[#0d3d4d]">
      ✅ Continuar
    </button>
  </form>

  <?php if ($mensaje): ?>
    <div class="mt-6 text-sm text-left text-red-600">
      <?= $mensaje ?>
    </div>
  <?php endif; ?>
</div>

<footer class="text-center text-sm text-gray-500 py-6">
  © 2025 PETBIO | Identificación Biométrica de Mascotas
</footer>

<script>
  function generarNombreSugerido() {
    const clase = localStorage.getItem('clase_mascota') || 'mascota';
    const raza = localStorage.getItem('raza') || 'raza';
    const edad = localStorage.getItem('edad') || 'edad';
    const cuidador = localStorage.getItem('apellidos') || 'cuidador';
    const ciudad = localStorage.getItem('ciudad') || 'ciudad';
    const barrio = localStorage.getItem('barrio') || 'barrio';
    const codpostal = localStorage.getItem('codigo_postal') || '00000';
    const nombre = localStorage.getItem('nombre') || 'nombre';
    return `${clase}_${raza}_${edad}_${cuidador}_${ciudad}_${barrio}_${codpostal}_${nombre}_hf0.jpg`
      .replace(/\s+/g, '_')
      .toLowerCase();
  }

  function procesarImagenFrontal() {
    const input = document.getElementById('fileInput');
    const preview = document.getElementById('previewImagen');
    const file = input.files[0];

    if (file && file.type.startsWith('image/')) {
      const reader = new FileReader();
      reader.onload = function (e) {
        localStorage.setItem('img_hf0', e.target.result);
        preview.src = e.target.result;
        preview.classList.remove('hidden');
      };
      reader.readAsDataURL(file);
    } else {
      alert("⚠️ El archivo seleccionado no es una imagen válida.");
      return;
    }

    const sugerido = generarNombreSugerido();
    localStorage.setItem('foto_frontal_nombre', sugerido);
    document.getElementById('nombreSugerido').textContent = sugerido;
  }

  function validarFormulario() {
    const imgBase64 = localStorage.getItem('img_hf0');
    if (!imgBase64) {
      alert("⚠️ Por favor sube una imagen frontal antes de continuar.");
      return false;
    }
    return true;
  }

  window.addEventListener('DOMContentLoaded', () => {
    localStorage.setItem('paso_petbio', 'mockup6');
    const sugerido = generarNombreSugerido();
    document.getElementById('nombreSugerido').textContent = sugerido;

    const img = localStorage.getItem('img_hf0');
    if (img) {
      const preview = document.getElementById('previewImagen');
      preview.src = img;
      preview.classList.remove('hidden');
    }

    const requeridos = ['nombre', 'clase_mascota', 'apellidos'];
    const faltantes = requeridos.filter(k => !localStorage.getItem(k));
    if (faltantes.length) {
      alert("⚠️ Datos incompletos. Serás redirigido al inicio.");
      window.location.href = 'mockup6.php';
    }
  });
</script>
</body>
</html>
