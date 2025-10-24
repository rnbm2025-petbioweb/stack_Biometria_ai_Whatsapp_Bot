<?php
session_start();

require_once 'sesion_global.php';

// Asegura un ID de sesión de usuario único
if (!isset($_SESSION['id_usuario'])) {
  $_SESSION['id_usuario'] = uniqid('usr_');
}

$mensaje = '';
$preview_img_15i = $_SESSION['preview_img_15i'] ?? '';

// Procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['img_hfli15']) && $_FILES['img_hfli15']['error'] === UPLOAD_ERR_OK) {
  $archivo = $_FILES['img_hfli15'];
  $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
  $ext_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
  $mime_permitidos = ['image/jpeg', 'image/png', 'image/gif'];
  $tamano_max = 10 * 1024 * 1024; // 10MB

  if (!in_array($ext, $ext_permitidas)) {
    $mensaje = "❌ Extensión no permitida ($ext).";
  } elseif (!in_array($archivo['type'], $mime_permitidos)) {
    $mensaje = "❌ Tipo MIME no permitido.";
  } elseif ($archivo['size'] > $tamano_max) {
    $mensaje = "❌ Archivo excede los 10MB.";
  } elseif (!getimagesize($archivo['tmp_name'])) {
    $mensaje = "❌ No es una imagen válida.";
  } else {
    $nombre_final = 'hfli15_' . $_SESSION['id_usuario'] . '_' . time() . '.' . $ext;
    $ruta_destino = 'uploads/' . $nombre_final;

    if (!is_dir('uploads')) {
      mkdir('uploads', 0755, true);
    }

    if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
      $mensaje = "✅ Imagen lateral izquierda (15°) subida correctamente.";
      $_SESSION['preview_img_15i'] = $ruta_destino;
      $preview_img_15i = $ruta_destino;
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
  <title>Mockup 6 - Lateral Izquierdo 15°</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="js/flujo_control.js"></script>
  <script>
    window.addEventListener('DOMContentLoaded', () => {
      localStorage.setItem('paso_petbio', 'mockup6');
      if (!localStorage.getItem('nombre')) {
        window.location.href = "mockup0.php";
      }
    });
  </script>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="min-h-screen bg-gradient-to-b from-[#FFF8F0] to-[#E0F7FA] p-4">

  <!-- Cabecera -->
  <div class="flex flex-col md:flex-row items-center justify-between mb-10 gap-6 px-4">
    <div class="flex-1 text-center md:text-left">
      <h1 class="text-2xl font-bold mb-4">🐾 Bienvenido a PETBIO - Registro Nacional Biométrico</h1>
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
    <div class="flex-shrink-0">
      <img src="./imagenes_guias_2025/bienvenida_00011.png" alt="Bienvenida" class="w-64 rounded-xl shadow-lg">
    </div>
  </div>

  <!-- Formulario -->
  <div class="bg-white rounded-xl shadow-xl p-6 max-w-3xl mx-auto w-full">
    <h2 class="text-2xl font-bold text-[#114358] mb-4 text-center">📸 Subir imagen lateral izquierda 15°</h2>
    <p class="text-center text-gray-600 mb-6">Captura clara y lateral de la trufa desde el ángulo izquierdo (15°).</p>

    <!-- Imagen de ejemplo -->
    <div class="mb-6 text-center">
      <p class="text-sm text-gray-500 mb-2">Ejemplo de imagen esperada:</p>
      <img src="img/ejemplo_15i.jpg" alt="Ejemplo lateral 15° izquierdo" class="w-48 h-auto mx-auto border rounded shadow">
    </div>

    <!-- Mensaje de estado -->
    <?php if ($mensaje): ?>
      <p class="text-center font-semibold text-<?php echo str_starts_with($mensaje, '✅') ? 'green' : 'red'; ?>-600 mb-4">
        <?php echo $mensaje; ?>
      </p>
    <?php endif; ?>

    <!-- Vista previa -->
    <?php if ($preview_img_15i): ?>
      <div class="mb-6 text-center">
        <p class="text-sm text-gray-600 mb-2">Tu imagen actual:</p>
        <img src="<?php echo htmlspecialchars($preview_img_15i); ?>" alt="Vista previa" class="h-48 mx-auto border rounded shadow">
      </div>
    <?php endif; ?>

    <!-- Formulario -->
    <form action="mockup6.php" method="POST" enctype="multipart/form-data" class="space-y-4">
      <input type="file" name="img_hfli15" accept="image/*" required class="block w-full text-sm text-gray-700 border border-gray-300 rounded p-2">
      <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded">
        Subir imagen
      </button>
    </form>

    <!-- Guardar en localStorage -->
    <?php if ($mensaje && str_starts_with($mensaje, '✅')): ?>
      <script>
        localStorage.setItem('foto30_izq', '<?php echo htmlspecialchars($preview_img_15i); ?>');
        // window.location.href = "mockup7.php"; // Descomenta si deseas avanzar automáticamente
      </script>
    <?php endif; ?>

    <!-- Navegación -->
    <div class="flex justify-between mt-6 text-sm text-blue-600">
      <a href="mockup5.php" class="hover:underline">⬅️ Paso anterior</a>
      <a href="mockup7.php" class="hover:underline">➡️ Siguiente paso</a>
    </div>
  </div>

</body>
</html>
