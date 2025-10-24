<?php

// ✅ ¡Primero lo primero! — Inicia la sesión ANTES de cualquier salida HTML
session_start();

if (isset($_SESSION['id_usuario'])) {
  header("Location: https://petbio.siac2025.com/identidad_rubm.php");
  exit();
}
$usuarioLogeado = $_SESSION['nombre'] ?? null;
$apellidos = $_SESSION['apellidos'] ?? null;
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

<script>
  function toggleMenu() {
    const menu = document.getElementById('dropdownMenu');
    menu.classList.toggle('hidden');
  }
</script>
</div>
  </header>

  <main class="max-w-lg mx-auto mt-10 bg-white rounded-xl shadow-lg p-6">
  
<?php if (isset($_SESSION['login_error'])): ?>
  <div class="bg-red-100 text-red-800 p-4 rounded mb-4 text-center">
    <?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?>
  </div>
<?php endif; ?>


  <h2 class="text-2xl font-semibold mb-6 text-center text-petbioazul">🔐 Iniciar Sesión PETBIO RUBM</h2>

    <?php if ($usuarioLogeado): ?>
      <div class="bg-green-100 text-green-800 p-4 rounded mb-4 text-center">
        👤 Usuario logueado: <strong><?php echo htmlspecialchars($usuarioLogeado . ' ' . $apellidos); ?></strong>
      </div>
      <form method="post" action="logout.php" class="text-center">
        <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded">🔓 Cerrar sesión</button>
      </form>
    <?php else: ?>
      <form id="loginForm" method="post" action="loginAccesoaHellitasHTML.php" class="space-y-4">
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
        <button type="button" onclick="window.location.href='RegistroHuellitas27.html'" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded">Ir a Registro</button>
      </form>
    <?php endif; ?>
  </main>

  <!-- GUÍA DE FOTOS -->
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

  <footer class="text-center text-sm text-gray-500 py-6">
    © 2025 PETBIO | Identificación Biométrica de Mascotas
  </footer>
</body>
</html>
