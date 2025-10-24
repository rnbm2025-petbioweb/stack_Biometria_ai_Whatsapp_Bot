<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

define('BASE_PATH', dirname(__DIR__)); 
require_once BASE_PATH . '/dir_config/sesion_global.php';

// Configuración de base de datos
$host = 'mysql_petbio_secure';
$puerto = 3306;
$bd = 'db__produccion_petbio_segura_2025';
$usuario = 'root';
$clave = 'R00t_Segura_2025!';

try {
    $conn = new PDO("mysql:host=$host;port=$puerto;dbname=$bd;charset=utf8", $usuario, $clave);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "Debes ingresar el correo y la contraseña.";
        header("Location: loginpetbio.php");
        exit;
    }

    // Validación de correo
    $dominiosPermitidos = ['gmail.com', 'hotmail.com', 'outlook.com', 'siac2025.com', 'petbio.com.co'];
    $prohibidos = ['example', 'ejemplo', 'test', 'demo'];
    foreach ($prohibidos as $patron) {
        if (stripos($email, $patron) !== false) {
            $_SESSION['login_error'] = "Correo no válido. Usa uno real.";
            header("Location: loginpetbio.php");
            exit;
        }
    }
    $partes = explode('@', $email);
    if (count($partes) !== 2 || !in_array(strtolower($partes[1]), $dominiosPermitidos)) {
        $_SESSION['login_error'] = "Correo no autorizado. Usa Gmail, Outlook o institucional.";
        header("Location: loginpetbio.php");
        exit;
    }

    // Buscar usuario
    $stmt = $conn->prepare("SELECT * FROM registro_usuario WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($password, $usuario['password'])) {
        // Regenerar ID de sesión
        session_regenerate_id(true);
        $id_sesion = session_id();
        $id_usuario = $usuario['id_usuario'];

        // ✅ Cerrar sesiones anteriores
        $conn->prepare("DELETE FROM sesiones_activas WHERE id_usuario = :id_usuario")
             ->execute([':id_usuario' => $id_usuario]);

        // ✅ Registrar la nueva sesión activa
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocido';
        $navegador = $_SERVER['HTTP_USER_AGENT'] ?? 'sin user-agent';
        $conn->prepare("INSERT INTO sesiones_activas (id_sesion, id_usuario, ip, navegador) 
                        VALUES (:id_sesion, :id_usuario, :ip, :nav)")
             ->execute([
                 ':id_sesion' => $id_sesion,
                 ':id_usuario' => $id_usuario,
                 ':ip' => $ip,
                 ':nav' => $navegador
             ]);

        // Datos de sesión
        $_SESSION['username']   = $usuario['username'];
        $_SESSION['apellidos']  = $usuario['apellidos_usuario'];
        $_SESSION['id_usuario'] = $id_usuario;

        // Registrar ingreso histórico
        $conn->prepare("INSERT INTO registro_ingresos (id_usuario, direccion_ip, navegador) 
                        VALUES (:id_usuario, :ip, :nav)")
             ->execute([
                 ':id_usuario' => $id_usuario,
                 ':ip' => $ip,
                 ':nav' => $navegador
             ]);

        header("Location: identidad_rubm.php");
        exit;
    } else {
        $_SESSION['login_error'] = "Correo o contraseña inválidos.";
        header("Location: loginpetbio.php");
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Iniciar Sesión - PETBIO</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            petbioazul: '#27445D',
            petbioazulclaro: '#72BCB3',
            petbioverde: '#497D74',
            petbiofondo: '#EFE9D5'
          },
          fontFamily: {
            bahn: ['Bahnschrift', 'Segoe UI', 'sans-serif']
          }
        }
      }
    }
  </script>
</head>
<body class="bg-petbiofondo text-petbioazul font-bahn">

  <header class="bg-white shadow-md p-4 flex justify-between items-center">
    <div>
      <h1 class="text-2xl font-bold text-petbioazul">🐾 PETBIO</h1>
      <p class="text-sm text-petbioverde">Registro Biométrico de Mascotas</p>
    </div>
    <div class="relative inline-block text-left">
      <button onclick="toggleMenu()" class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-petbioazulclaro text-white font-bold hover:bg-petbioverde">
        ☰ Menú
      </button>
      <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
        <a href="https://siac2025.com/" class="block px-4 py-2 text-sm text-petbioazul hover:bg-gray-100">🏠 Inicio</a>
        <a href="https://registro.siac2025.com/" class="block px-4 py-2 text-sm text-petbioazul hover:bg-gray-100">🔗 PETBIO – SIAC</a>
        <a href="https://registro.siac2025.com/2025/06/28/1041/" class="block px-4 py-2 text-sm text-petbioazul hover:bg-gray-100">🛡️ Política de Privacidad</a>
        <a href="https://registro.siac2025.com/2025/06/28/1039/" class="block px-4 py-2 text-sm text-petbioazul hover:bg-gray-100">⚖️ Términos y Condiciones</a>
        <a href="https://petbio11rubm2025.blogspot.com/" class="block px-4 py-2 text-sm text-petbioazul hover:bg-gray-100">📖 Blog PETBIO</a>
      </div>
    </div>
  </header>

  <main class="max-w-lg mx-auto mt-10 bg-white rounded-xl shadow-lg p-6">
    <?php if (isset($_SESSION['login_error'])): ?>
      <div class="bg-red-100 text-red-800 p-4 rounded mb-4 text-center">
        <?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?>
      </div>
    <?php endif; ?>

    <h2 class="text-2xl font-semibold mb-6 text-center text-petbioazul">🔐 Iniciar Sesión PETBIO RUBM</h2>

    <form id="loginForm" method="post" action="loginpetbio.php" class="space-y-4">
      <div>
        <label for="email" class="block font-medium">📧 Email:</label>
        <input type="email" id="email" name="email" required class="w-full rounded border border-gray-300 px-4 py-2">
      </div>
      <div>
        <label for="password" class="block font-medium">🔒 Contraseña:</label>
        <input type="password" id="password" name="password" required class="w-full rounded border border-gray-300 px-4 py-2">
      </div>
      <div class="flex items-center">
        <input type="checkbox" id="mostrarPassword" onclick="togglePassword()" class="mr-2">
        <label for="mostrarPassword" class="text-sm">Mostrar contraseña</label>
      </div>
      <div class="flex items-center">
        <input type="checkbox" id="terminos" name="terminos" required class="mr-2">
        <label for="terminos" class="text-sm">Acepto los <a href="https://site-mscdp54gx.godaddysites.com/t%C3%A9rminos-y-condiciones" target="_blank" class="underline text-petbioazul">términos y condiciones</a>.</label>
      </div>
      <button type="submit" class="w-full bg-petbioazulclaro hover:bg-petbioverde text-white font-bold py-2 px-4 rounded">Iniciar Sesión</button>
      <button type="button" onclick="limpiarLogin()" class="w-full bg-gray-500 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded">Limpiar Login</button>
      <button type="button" onclick="window.location.href='registropetbio.php'" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded">Ir a Registro</button>
    </form>
  </main>

  <section class="max-w-3xl mx-auto mt-10 mb-10 bg-white rounded-xl shadow-md p-6">
    <h2 class="text-xl font-bold text-petbioverde mb-4">📸 Guía para Captura Biométrica de la Trufa</h2>
    <ul class="list-disc list-inside space-y-2 text-petbioazul text-sm">
      <li>✔️ Mascota tranquila o dormida</li>
      <li>✔️ Luz blanca, preferiblemente LED de mínimo 9W</li>
      <li>✔️ Fondo blanco o claro</li>
      <li>✔️ Captura 3 fotos de la trufa:
        <ul class="list-disc list-inside ml-5">
          <li>🔹 Frontal (0°) a 10–17 cm</li>
          <li>🔹 Inclinada 45°, evitando sombra</li>
          <li>🔹 Vista lateral (90°)</li>
        </ul>
      </li>
      <li>✔️ Además, 4 fotos del rostro: perfil derecho, izquierdo, superior e inferior</li>
      <li>✔️ Sesión con buena luz y sin sombras</li>
      <li>✔️ Imágenes nítidas y enfocadas</li>
    </ul>
  </section>

  <footer class="text-center text-sm text-gray-500 py-6">
    © 2025 PETBIO | Identificación Biométrica de Mascotas
  </footer>

  <script>
    function togglePassword() {
      const passwordInput = document.getElementById("password");
      passwordInput.type = passwordInput.type === "password" ? "text" : "password";
    }

    function limpiarLogin() {
      const campos = document.querySelectorAll('#loginForm input[type="text"], #loginForm input[type="password"], #loginForm input[type="email"]');
      campos.forEach(campo => campo.value = '');
    }

    function toggleMenu() {
      const menu = document.getElementById('dropdownMenu');
      menu.classList.toggle('hidden');
    }
  </script>

  <?php if (isset($_SESSION['id_usuario']) && !isset($_GET['forzar_identidad'])): ?>
    <script>
      // Limpiar paso anterior del mockup
      localStorage.removeItem('paso_petbio');
      localStorage.removeItem('ingreso_validado');

      // Redirigir siempre al formulario de identidad
      window.location.href = 'identidad_rubm.php';
    </script>
  <?php endif; ?>

</body>
</html>