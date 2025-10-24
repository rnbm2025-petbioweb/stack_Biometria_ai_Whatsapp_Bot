<?php

require_once("no_existe.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();


echo "DIR: " . __DIR__ . "<br>";
echo "PATH: " . __DIR__ . '/../dir_config/sesion_global.php' . "<br>";
echo "FILE EXISTS: " . (file_exists(__DIR__ . '/../dir_config/sesion_global.php') ? 'YES' : 'NO') . "<br>";
exit;


//require_once 'config/conexion.php';

//require_once 'sesion_global.php';

require_once(__DIR__ . '/../dir_config/sesion_global.php');
//require_once(__DIR__ . '/../dir_config/conexion_petbio_nueva.php');


define('BASE_PATH', dirname(__DIR__)); // define la raíz del proyecto
require_once BASE_PATH . '/dir_config/sesion_global.php';


/*
define('BASE_PATH', dirname(__DIR__)); // define la raíz del proyecto
require_once BASE_PATH . '/dir_config/sesion_global.php';
*/

// Conexión segura a la base de datos
$host = 'mysql_petbio_secure';
$puerto = 3306;
$dbname = 'db__produccion_petbio_segura_2025';
$usuario = 'root';
$clave = 'R00t_Segura_2025!';

try {
  $pdo = new PDO("mysql:host=$host;port=$puerto;dbname=$dbname;charset=utf8", $usuario, $clave);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("❌ Error de conexión: " . $e->getMessage());
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $lastname = trim($_POST['lastname'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirmPassword = $_POST['confirmPassword'] ?? '';
  $documento = trim($_POST['Documento_identidad'] ?? '');
  $tipoPersona = $_POST['tipo_Persona'] ?? '';
  $tieneMascotas = $_POST['tiene_mascotas'] ?? '0';
  $terminosAceptados = isset($_POST['terminos']);

  // Validaciones
  if (!$username || !$lastname || !$email || !$password || !$confirmPassword || !$documento || !$tipoPersona || !$terminosAceptados) {
    echo "<script>alert('⚠️ Todos los campos son obligatorios y debes aceptar los términos.'); window.history.back();</script>";
    exit;
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('⚠️ Correo electrónico no válido.'); window.history.back();</script>";
    exit;
  }

  $dominiosPermitidos = ['gmail.com', 'hotmail.com', 'outlook.com', 'siac2025.com', 'petbio.com.co'];
  $prohibidos = ['example', 'test', 'demo', 'prueba', 'ejemplo'];

  foreach ($prohibidos as $prohibido) {
    if (stripos($email, $prohibido) !== false) {
      echo "<script>alert('⚠️ El correo no es válido. Usa uno real.'); window.history.back();</script>";
      exit;
    }
  }

  $partes = explode('@', $email);
  if (count($partes) !== 2 || !in_array(strtolower($partes[1]), $dominiosPermitidos)) {
    echo "<script>alert('⚠️ Dominio de correo no permitido. Usa Gmail, Outlook o institucional.'); window.history.back();</script>";
    exit;
  }

  if ($password !== $confirmPassword) {
    echo "<script>alert('⚠️ Las contraseñas no coinciden.'); window.history.back();</script>";
    exit;
  }

  // Verificar duplicados
  $stmt = $pdo->prepare("SELECT id_usuario FROM registro_usuario WHERE email = :email OR Documento_identidad = :doc");
  $stmt->execute([':email' => $email, ':doc' => $documento]);
  if ($stmt->rowCount() > 0) {
    echo "<script>alert('⚠️ Este usuario ya está registrado.'); window.history.back();</script>";
    exit;
  }

  try {
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $insert = $pdo->prepare("
      INSERT INTO registro_usuario (
        username, apellidos_usuario, email, password, Documento_identidad, tipo_persona, tiene_mascotas
      ) VALUES (
        :username, :apellidos, :email, :password, :documento, :tipo_persona, :tiene_mascotas
      )
    ");
    $insert->execute([
      ':username' => $username,
      ':apellidos' => $lastname,
      ':email' => $email,
      ':password' => $passwordHash,
      ':documento' => $documento,
      ':tipo_persona' => $tipoPersona,
      ':tiene_mascotas' => $tieneMascotas
    ]);

    $id_usuario = $pdo->lastInsertId();
    $_SESSION['id_usuario'] = $id_usuario;
    $_SESSION['nombre'] = $username;
    $_SESSION['apellidos'] = $lastname;

// 👇 Identidad Petbio
$esCuidador = ($tieneMascotas === '1') ? 1 : 0;
$rol = $esCuidador ? 'CUID' : 'USER';
$petbioID = sprintf("PETBIO-%s-%06d", $rol, $id_usuario);

$stmt = $pdo->prepare("
    INSERT INTO identidad_petbio (id_usuario, id_petbio, es_cuidador)
    VALUES (:id_usuario, :id_petbio, :es_cuidador)
");
$stmt->execute([
    ':id_usuario' => $id_usuario,
    ':id_petbio' => $petbioID,
    ':es_cuidador' => $esCuidador
]);

// Registro de actividad
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocido';
    $navegador = $_SERVER['HTTP_USER_AGENT'] ?? 'sin user-agent';
    $log = $pdo->prepare("INSERT INTO registro_ingresos (id_usuario, direccion_ip, navegador) VALUES (:id, :ip, :nav)");
    $log->execute([':id' => $id_usuario, ':ip' => $ip, ':nav' => $navegador]);


    //echo "<script>alert('✅ Registro exitoso. Ahora continúa con la identidad biométrica.'); window.location.href = 'identidad_rubm.php';</script>";
    echo "<script>alert('✅ Registro exitoso. Tu ID PETBIO es: $petbioID. Ahora continúa con la identidad biométrica.'); window.location.href = 'identidad_rubm.php';</script>";

    exit;
  } catch (PDOException $e) {
    echo "<script>alert('❌ Error al guardar: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Registro de Usuarios PETBIO</title>
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

<!-- Header con menú -->
<header class="bg-white shadow-md p-4 flex justify-between items-center">
  <div>
    <h1 class="text-2xl font-bold text-petbioazul">🐾 PETBIO</h1>
    <p class="text-sm text-petbioverde">Registro de Usuarios de Mascotas</p>
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

<script>
  function toggleMenu() {
    const menu = document.getElementById('dropdownMenu');
    menu.classList.toggle('hidden');
  }
</script>

<!-- Sección Guías -->
<section class="max-w-3xl mx-auto mt-10">
  <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
    <h2 class="text-xl font-semibold mb-4">📋 Guías Paso a Paso</h2>
    <button onclick="toggleAccordion('paso1')" class="w-full bg-petbioazulclaro hover:bg-petbioverde text-white font-bold py-2 px-4 rounded">📸 Captura Biométrica</button>
    <div id="paso1" class="hidden mt-4 text-sm text-petbioazul">
      <ul class="list-disc list-inside space-y-2">
        <li>Mascota tranquila o dormida</li>
        <li>Luz blanca, fondo claro</li>
        <li>3 fotos de la trufa (frontal, 45°, lateral)</li>
        <li>4 fotos del rostro (perfiles, arriba y abajo)</li>
        <li>Imágenes nítidas y sin sombras</li>
      </ul>
    </div>
  </div>

  <div class="bg-white rounded-xl shadow-lg p-6">
    <button onclick="toggleAccordion('paso2')" class="w-full bg-petbioverde hover:bg-petbioazul text-white font-bold py-2 px-4 rounded">🧾 Requisitos del Registro</button>
    <div id="paso2" class="hidden mt-4 text-sm text-petbioazul">
      <ul class="list-disc list-inside space-y-2">
        <li>Nombre y apellidos</li>
        <li>Correo electrónico válido</li>
        <li>Contraseña segura</li>
        <li>Documento de identidad</li>
        <li>Aceptar términos y condiciones</li>
      </ul>
    </div>
  </div>
</section>

<script>
  function toggleAccordion(id) {
    const section = document.getElementById(id);
    section.classList.toggle('hidden');
  }
</script>

<!-- Formulario de Registro -->
<main class="max-w-lg mx-auto mt-10 bg-white rounded-xl shadow-lg p-6">
  <h2 class="text-2xl font-semibold mb-6 text-center">Registro de Usuarios PETBIO RUBM</h2>
  <form method="POST" class="space-y-4">
    <div><label for="username" class="block font-medium">Nombres:</label><input type="text" id="username" name="username" required class="w-full rounded border border-gray-300 px-4 py-2"></div>
    <div><label for="lastname" class="block font-medium">Apellidos:</label><input type="text" id="lastname" name="lastname" required class="w-full rounded border border-gray-300 px-4 py-2"></div>
    <div><label for="email" class="block font-medium">Correo Electrónico:</label><input type="email" id="email" name="email" required class="w-full rounded border border-gray-300 px-4 py-2"></div>
    <div><label for="password" class="block font-medium">Contraseña:</label><input type="password" id="password" name="password" required class="w-full rounded border border-gray-300 px-4 py-2"></div>
    <div><label for="confirmPassword" class="block font-medium">Confirmar Contraseña:</label><input type="password" id="confirmPassword" name="confirmPassword" required class="w-full rounded border border-gray-300 px-4 py-2"></div>
    <div><label for="Documento_identidad" class="block font-medium">Documento de Identidad:</label><input type="number" id="Documento_identidad" name="Documento_identidad" required class="w-full rounded border border-gray-300 px-4 py-2"></div>
    <div>
      <label for="tipo_Persona" class="block font-medium">Tipo de Persona:</label>
      <select id="tipo_Persona" name="tipo_Persona" required class="w-full rounded border border-gray-300 px-4 py-2">
        <option value="Natural">Natural</option>
        <option value="Jurídica">Jurídica</option>
      </select>
    </div>
    <div>
      <label for="tiene_mascotas" class="block font-medium">¿Tiene Mascotas?</label>
      <select id="tiene_mascotas" name="tiene_mascotas" required class="w-full rounded border border-gray-300 px-4 py-2">
        <option value="1">Sí</option>
        <option value="0">No</option>
      </select>
    </div>
    <div class="flex items-center">
      <input type="checkbox" id="terminos" name="terminos" required class="mr-2">
      <label for="terminos" class="text-sm">
        Acepto los <a href="https://site-mscdp54gx.godaddysites.com/t%C3%A9rminos-y-condiciones" target="_blank" class="text-petbioazul underline">términos y condiciones</a>.
      </label>
    </div>
    <button type="submit" class="w-full bg-petbioazulclaro hover:bg-petbioverde text-white font-bold py-2 px-4 rounded">Registrar</button>
    <button type="button" onclick="window.location.href='loginpetbio.php'" class="w-full mt-2 bg-gray-600 text-white font-semibold py-2 px-4 rounded hover:bg-gray-700">Ir a Login</button>
  </form>
</main>

<footer class="text-center text-sm text-gray-500 mt-10 py-4">
  © 2025 PETBIO | Biometría Animal en Colombia 🇨🇴
</footer>

</body>
</html>
