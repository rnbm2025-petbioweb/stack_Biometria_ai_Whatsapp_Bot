<?php
session_start();
require_once 'sesion_global.php';
require_once 'config/conexion_petbio_nueva.php'; // tu conexión PDO o mysqli

// Verificar si hay sesión iniciada
if (!isset($_SESSION['id_usuario'])) {
    echo "<!DOCTYPE html><html><head><script>
        alert('⚠️ Debes iniciar sesión primero en PETBIO.');
        window.location.href = 'https://petbio.siac2025.com/loginpetbio.php';
    </script></head><body></body></html>";
    exit;
}

// 🔐 Validar que la sesión sea la activa en sesiones_activas
$host = 'mysql_petbio_secure';
$puerto = 3306;
$bd = 'db__produccion_petbio_segura_2025';
$usuario = 'root';
$clave = 'R00t_Segura_2025!';

try {
    $pdo = new PDO("mysql:host=$host;port=$puerto;dbname=$bd;charset=utf8", $usuario, $clave);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Error en la conexión PDO: " . $e->getMessage());
}

$id_sesion = session_id();
$id_usuario = $_SESSION['id_usuario'];

// Validar sesión única
$stmt = $pdo->prepare("SELECT * FROM sesiones_activas WHERE id_sesion = :s AND id_usuario = :u");
$stmt->execute([':s' => $id_sesion, ':u' => $id_usuario]);

if ($stmt->rowCount() === 0) {
    // Sesión inválida o cerrada desde otro dispositivo
    session_destroy();
    echo "<!DOCTYPE html><html><head><script>
        alert('⚠️ Tu sesión fue cerrada porque iniciaste sesión desde otro dispositivo.');
        window.location.href = 'https://petbio.siac2025.com/loginpetbio.php';
    </script></head><body></body></html>";
    exit;
}

// ✅ Registro exitoso: aquí continúa la lógica de tu página
// Por ejemplo, mostrar mensaje de registro PETBIO o redirigir a identidad biométrica

// =======================
// RUTAS GUARDADAS EN SESIÓN
// =======================
$cedula_path        = $_SESSION['cedula_path'] ?? null;         // Cédula generada con FPDF
$cedula_nueva_path  = $_SESSION['cedula_nueva_path'] ?? null;   // Cédula generada con Python
$pdf_path           = $_SESSION['pdf_path'] ?? null;            // Registro completo

// Dominio base del sistema
$base_url = "https://petbio.siac2025.com";

// =======================
// CONVERTIR RUTA ABSOLUTA A URL WEB
// =======================
function convertirRutaWeb($ruta_absoluta, $base_url) {
    if (!$ruta_absoluta) return null;
    $pos = strpos($ruta_absoluta, "petbio_storage");
    if ($pos !== false) {
        $ruta_relativa = substr($ruta_absoluta, $pos);
        return rtrim($base_url, "/") . "/" . $ruta_relativa;
    }
    return null;
}

$cedula_url        = convertirRutaWeb($cedula_path, $base_url);
$cedula_nueva_url  = convertirRutaWeb($cedula_nueva_path, $base_url);
$pdf_url           = convertirRutaWeb($pdf_path, $base_url);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Registro Exitoso | PETBIO</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
</head>

<body class="min-h-screen bg-gradient-to-b from-[#FFF8F0] to-[#E0F7FA] p-4">

<!-- Encabezado -->
<header class="bg-white shadow w-full mb-8">
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
      <a href="/" class="hover:text-white hover:bg-blue-600 px-3 py-2 rounded">Inicio</a>
      <a href="https://petbio.siac2025.com/registropetbio.php" class="hover:text-white hover:bg-blue-600 px-3 py-2 rounded">Registro PETBIO</a>
      <a href="https://registro.siac2025.com/" class="hover:text-white hover:bg-blue-600 px-3 py-2 rounded">PETBIO</a>
      <a href="https://petbio.siac2025.com/loginpetbio.php" class="hover:text-white hover:bg-blue-600 px-3 py-2 rounded">Login PETBIO</a>
      <a href="https://petbio.siac2025.com/identidad_rubm.php" class="hover:text-white hover:bg-blue-600 px-3 py-2 rounded">Identidad RNBM</a>
      <a href="https://registro.siac2025.com/category/noticias-mas/" class="hover:text-white hover:bg-blue-600 px-3 py-2 rounded">Noticias</a>
      <a href="/contacto.html" class="hover:text-white hover:bg-blue-600 px-3 py-2 rounded">Contacto</a>
      <a href="https://petbio11rubm2025.blogspot.com/" class="hover:text-white hover:bg-blue-600 px-3 py-2 rounded">Blog PETBIO</a>
    </nav>
  </div>
</header>

<!-- Contenido principal -->
<div class="flex justify-center">
  <div class="bg-white rounded-xl shadow-xl p-8 text-center max-w-lg w-full">
    <h1 class="text-3xl font-bold text-green-700 mb-4">✅ Registro completado</h1>
    <p class="text-gray-700 mb-6">
      Tu mascota ha sido registrada exitosamente en el sistema <strong>PETBIO – RNBM</strong>.
    </p>

    <!-- Botón para Cédula FPDF -->
    <?php if ($cedula_url): ?>
      <a href="<?= htmlspecialchars($cedula_url) ?>" target="_blank"
         class="block bg-blue-600 text-white px-6 py-2 rounded-full hover:bg-blue-700 transition mb-4">
        Ver Cédula FPDF
      </a>
    <?php endif; ?>

    <!-- Botón para Cédula Python -->
    <?php if ($cedula_nueva_url): ?>
      <a href="<?= htmlspecialchars($cedula_nueva_url) ?>" target="_blank"
         class="block bg-green-600 text-white px-6 py-2 rounded-full hover:bg-green-700 transition mb-4">
        Ver Cédula Nueva (Python)
      </a>
    <?php endif; ?>

    <!-- Botón para PDF completo -->
    <?php if ($pdf_url): ?>
      <a href="<?= htmlspecialchars($pdf_url) ?>" target="_blank"
         class="block bg-purple-600 text-white px-6 py-2 rounded-full hover:bg-purple-700 transition mb-4">
        Ver Registro Completo (PDF)
      </a>
    <?php endif; ?>


    <!-- Registrar otra mascota -->
    <a href="mockup0.php"
       class="inline-block bg-green-600 text-white px-6 py-2 rounded-full hover:bg-green-700 transition mb-6">
      Registrar otra mascota
    </a>

    
  </div>
</div>

<!-- Script Confetti y Menú -->
<script>
  function toggleMobileMenu() {
    const menu = document.getElementById("mobileMenu");
    menu.classList.toggle("hidden");
  }

  window.addEventListener("DOMContentLoaded", () => {
    localStorage.clear();
    confetti({
      particleCount: 150,
      spread: 70,
      origin: { y: 0.6 }
    });
  });
</script>
</body>
</html>
