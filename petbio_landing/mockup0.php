<?php
session_start();
require_once 'sesion_global.php';

// ✅ Comprobar si hay sesión iniciada
if (!isset($_SESSION['id_usuario'])) {
    header('Location: https://petbio.siac2025.com/loginpetbio.php');
    exit;
}

// 🔐 Validar sesión única en la base de datos
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

// Validar que la sesión actual sea la activa
$id_sesion = session_id();
$id_usuario = $_SESSION['id_usuario'];

$stmt = $pdo->prepare("SELECT * FROM sesiones_activas WHERE id_sesion = :s AND id_usuario = :u");
$stmt->execute([':s' => $id_sesion, ':u' => $id_usuario]);

if ($stmt->rowCount() === 0) {
    // Sesión inválida o cerrada desde otro dispositivo
    session_destroy();
    header('Location: https://petbio.siac2025.com/loginpetbio.php?msg=sesion_cerrada');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Bienvenido a PETBIO</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #FFF8F0;
      /* Añadido padding general para que no quede pegado */
      padding: 0 1rem;
      margin: 0;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
    }
    main {
      max-width: 720px;
      width: 100%;
      padding-bottom: 4rem; /* para que no tape el combo box */
    }
    header {
      background: #FAF9F6;
      border-bottom: 4px solid #00B3A6;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      position: sticky;
      top: 0;
      z-index: 50;
      border-radius: 0 0 24px 24px;
      transition: background-color 0.3s ease;
      padding: 0 1rem; /* para mejor responsividad */
    }
    header:hover {
      background-color: #e6f7f7;
    }
    .logo-text:hover {
      color: #00B3A6;
      transition: color 0.3s ease;
    }
    #mobileMenu {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.4s ease;
    }
    #mobileMenu.open {
      max-height: 1000px;
      transition: max-height 0.6s ease;
    }
    button {
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    button:hover {
      transform: scale(1.05);
      box-shadow: 0 8px 15px rgba(0, 179, 166, 0.3);
    }
    button:focus {
      outline: 2px solid #00B3A6;
      outline-offset: 2px;
    }
    input[type="text"],
    select,
    input[type="file"] {
      transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }
    input[type="text"]:focus,
    select:focus,
    input[type="file"]:focus {
      border-color: #00B3A6;
      box-shadow: 0 0 5px #00B3A6;
      outline: none;
    }
    #info-clase {
      animation: fadeIn 0.5s ease forwards;
    }
    @keyframes fadeIn {
      from {opacity: 0;}
      to {opacity: 1;}
    }
    #preview_perfil img {
      border-radius: 1rem;
      box-shadow: 0 8px 16px rgba(0,0,0,0.15);
      transition: opacity 0.4s ease;
    }
    #preview_perfil img.fade-in {
      opacity: 0;
      animation: fadeIn 0.6s forwards;
    }

    /* Combo Box fijo esquina superior derecha */
    #comboBoxContainer {
      background: white;
      border: 1px solid #ccc;
      border-radius: 0.5rem;
      padding: 0.5rem 1rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      position: fixed;
      top: 1rem;
      right: 1rem;
      z-index: 60;
      transition: width 0.3s ease, padding 0.3s ease, border-radius 0.3s ease;
      max-width: 280px;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-weight: 600;
      font-size: 0.9rem;
      cursor: default;
    }
    #comboBoxContainer.minimizado {
      max-width: 44px;
      padding: 0.5rem;
      border-radius: 9999px;
      overflow: hidden;
      cursor: pointer;
      gap: 0;
      justify-content: center;
    }
    #comboBoxContainer.minimizado select,
    #comboBoxContainer.minimizado label {
      display: none;
    }
    #comboBoxContainer button.toggle-btn {
      position: absolute;
      top: -8px;
      right: -8px;
      width: 24px;
      height: 24px;
      background-color: #3B82F6;
      border-radius: 9999px;
      font-weight: bold;
      font-size: 1.2rem;
      line-height: 24px;
      color: white;
      box-shadow: 0 4px 6px rgba(59, 130, 246, 0.4);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      border: none;
      transition: background-color 0.3s ease;
      z-index: 61;
    }
    #comboBoxContainer button.toggle-btn:hover {
      background-color: #2563EB;
    }
    #comboBoxContainer select {
      border: 1px solid #9CA3AF; /* gray-400 */
      border-radius: 0.375rem;
      padding: 0.25rem 0.5rem;
      cursor: pointer;
      background-color: white;
      font-weight: 600;
      min-width: 160px;
      transition: border-color 0.3s ease;
    }
    #comboBoxContainer select:hover,
    #comboBoxContainer select:focus {
      border-color: #3B82F6;
      outline: none;
    }
    #comboBoxContainer label {
      user-select: none;
      color: #1E40AF; /* azul oscuro */
    }
  </style>
</head>
<body>

<header>
  <div class="max-w-screen-xl mx-auto px-6 py-4 flex justify-between items-center">
    <a href="/" class="flex items-center gap-3 text-2xl font-extrabold text-[#0D4F48] font-bahn logo-text select-none" aria-label="Inicio SIAC2025">
      🐾 SIAC2025
    </a>

    <a href="/" class="flex items-center space-x-3 hover:scale-105 transition-transform duration-300 select-none" aria-label="Logo SIAC2025">
      <img src="./imagenes_guias_2025/logo_petbio_transparente.png" alt="Logo SIAC2025" class="h-12 w-auto drop-shadow-lg" />
      <span class="font-extrabold text-[#114358] text-xl font-bahn tracking-wide drop-shadow-md select-none">SIAC 2025</span>
    </a>

    <button 
      onclick="toggleMobileMenu()" 
      class="md:hidden text-[#0D4F48] focus:outline-none hover:text-[#00B3A6] transition-colors duration-300" 
      aria-label="Abrir menú móvil"
      aria-expanded="false"
      id="mobileMenuButton"
    >
      <svg class="w-8 h-8 stroke-current" fill="none" stroke-width="3" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>

    <nav class="hidden md:flex space-x-8 text-base font-semibold text-[#114358] font-bahn select-none" aria-label="Menú principal">
      <a href="/" class="hover:text-[#00B3A6] hover:underline underline-offset-4 transition">Inicio</a>
      <a href="https://petbio.siac2025.com/registropetbio.php" target="_blank" rel="noopener noreferrer" class="hover:text-[#00B3A6] hover:underline underline-offset-4 transition">Registro PETBIO</a>
      <a href="https://registro.siac2025.com/" target="_blank" rel="noopener noreferrer" class="hover:text-[#00B3A6] hover:underline underline-offset-4 transition">PETBIO</a>
      <a href="https://petbio.siac2025.com/loginpetbio.php" target="_blank" rel="noopener noreferrer" class="hover:text-[#00B3A6] hover:underline underline-offset-4 transition">Login PETBIO</a>
      <a href="https://petbio.siac2025.com/identidad_rubm.php" target="_blank" rel="noopener noreferrer" class="hover:text-[#00B3A6] hover:underline underline-offset-4 transition">Identidad RNBM</a>
      <a href="https://siac2025.com/contacto.html" class="hover:text-[#00B3A6] hover:underline underline-offset-4 transition">Contacto</a>
      <a href="https://petbio11rubm2025.blogspot.com/" target="_blank" rel="noopener noreferrer" class="hover:text-[#00B3A6] hover:underline underline-offset-4 transition">Blog PETBIO</a>
    </nav>
  </div>

  <div id="mobileMenu" class="md:hidden px-6 pb-6 bg-[#FAF9F6] text-[#114358] font-bahn text-base font-semibold select-none border-t border-[#00B3A6] rounded-b-xl max-h-0 overflow-hidden" aria-label="Menú móvil" aria-hidden="true">
    <nav class="flex flex-col gap-4 pt-4">
      <a href="/" class="hover:text-[#00B3A6] hover:underline underline-offset-4 transition">Inicio</a>
      <a href="https://petbio.siac2025.com/registropetbio.php" target="_blank" rel="noopener noreferrer" class="hover:text-[#00B3A6] hover:underline underline-offset-4 transition">Registro PETBIO</a>
      <a href="https://registro.siac2025.com/" target="_blank" rel="noopener noreferrer" class="hover:text-[#00B3A6] hover:underline underline-offset-4 transition">PETBIO</a>
      <a href="https://petbio.siac2025.com/loginpetbio.php" target="_blank" rel="noopener noreferrer" class="hover:text-[#00B3A6] hover:underline underline-offset-4 transition">Login PETBIO</a>
      <a href="https://petbio.siac2025.com/identidad_rubm.php" target="_blank" rel="noopener noreferrer" class="hover:text-[#00B3A6] hover:underline underline-offset-4 transition">Identidad RNBM</a>
      <a href="https://registro.siac2025.com/category/noticias-mas/" target="_blank" rel="noopener noreferrer" class="hover:text-[#00B3A6] hover:underline underline-offset-4 transition">Noticias</a>
      <a href="/contacto.html" class="hover:text-[#00B3A6] hover:underline underline-offset-4 transition">Contacto</a>
      <a href="https://petbio11rubm2025.blogspot.com/" target="_blank" rel="noopener noreferrer" class="hover:text-[#00B3A6] hover:underline underline-offset-4 transition">Blog PETBIO</a>
    </nav>
  </div>
</header>

<main>
  <section class="flex flex-col items-center justify-center mb-10">
    <h1 class="text-3xl font-bold text-center mb-6 text-[#114358] select-none">🐾 Bienvenido a PETBIO - Registro Nacional Biométrico</h1>
    <img src="./imagenes_guias_2025/bienvenida_00011.png" alt="Bienvenida" class="w-64 rounded-xl shadow-lg mb-8 transition-transform duration-500 hover:scale-105" />
    <div class="flex gap-4">
      <button 
        onclick="validarYContinuar()" 
        class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded-full shadow-lg"
        aria-label="Continuar con registro"
      >✅ Continuar</button>
      <button 
        onclick="irASiac2025()" 
        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-full shadow-lg"
        aria-label="Ingresar a SIAC2025"
      >🌐 Ingresar a SIAC2025</button>
    </div>
  </section>

  <section class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-2xl p-8 w-full text-center">
    <form id="form-paso0" class="space-y-6" novalidate>
      <div>
        <label for="nombre" class="block text-left font-semibold text-[#114358] mb-1">¿Cómo se llama tu mascota?</label>
        <input name="nombre" id="nombre" type="text" required autocomplete="off" class="w-full rounded-full px-4 py-2 border border-gray-300 focus:border-[#00B3A6] focus:ring-2 focus:ring-[#00B3A6]" />
      </div>

      <div>
        <label for="apellidos" class="block text-left font-semibold text-[#114358] mb-1">¿Cuál será el apellido de tu mascota?</label>
        <input name="apellidos" id="apellidos" type="text" required autocomplete="off" class="w-full rounded-full px-4 py-2 border border-gray-300 focus:border-[#00B3A6] focus:ring-2 focus:ring-[#00B3A6]" />
      </div>

      <div>
        <label for="clase_mascota" class="block text-left font-semibold text-[#114358] mb-1">Clase de Mascota</label>
        <select name="clase_mascota" id="clase_mascota" onchange="mostrarInfoClase(this.value)" required class="w-full bg-gray-200 rounded-full px-4 py-2 border border-gray-300 focus:border-[#00B3A6] focus:ring-2 focus:ring-[#00B3A6]">
          <option value="">Selecciona una opción</option>
          <option value="Caninos">Caninos</option>
          <option value="Felinos">Felinos</option>
          <option value="Equinos">Equinos</option>
          <option value="Porcinos">Porcinos</option>
          <option value="Ganado">Ganado</option>
          <option value="Otros Mamiferos">Otros Mamíferos</option>
        </select>
      </div>

      <div id="info-clase" class="bg-yellow-100 rounded-xl p-4 shadow-md hidden text-justify text-[#664d03] transition-opacity duration-500"></div>

      <div>
        <label for="img_perfil" class="block text-left font-semibold text-[#114358] mb-1">📸 Foto de perfil de la mascota</label>
        <input type="file" accept="image/*" id="img_perfil" required class="w-full rounded-full px-4 py-2 border border-gray-300 bg-white cursor-pointer focus:border-[#00B3A6] focus:ring-2 focus:ring-[#00B3A6]" />
        <div id="preview_perfil" class="mt-4 flex justify-center"></div>
      </div>

      <div class="flex justify-end pt-4">
        <button type="button" onclick="validarYContinuar()" class="bg-[#114358] hover:bg-[#0d3d4d] text-white px-6 py-2 rounded-full shadow-lg transition-transform duration-200">Siguiente ➡️</button>
      </div>

      <div class="mt-8 text-center">
        <p class="text-sm text-gray-700 mb-2 select-none">Guía visual para capturar la imagen de perfil:</p>
        <div class="inline-block border border-gray-300 rounded-lg shadow-md p-2 bg-white">
          <img src="./imagenes_guias_2025/nieves.jpeg" alt="Guía para captura de imagen de perfil" class="w-48 rounded-lg shadow-sm select-none" />
        </div>
      </div>
    </form>
  </section>
</main>

<footer class="fixed bottom-0 left-0 w-full bg-gray-100 border-t border-gray-400 p-3 flex flex-col sm:flex-row items-center justify-center sm:space-x-3 z-50" role="contentinfo">
  <label for="mockupSelectorFooter" class="text-sm font-semibold select-none mb-1 sm:mb-0">Ir a paso:</label>
  <select id="mockupSelectorFooter" class="border border-gray-400 p-2 rounded w-full sm:w-auto" onchange="irAMockup(this.value)" aria-label="Selector de paso">
    <option value="">Seleccionar</option>
    <option value="mockup0.php" selected> Paso 1 – Nombre mascota / Foto de Perfil</option>
    <option value="mockup1.php">Paso 2 – Clase / Raza / Edad / Imagen Cuerpo lado lateral derecha</option>
    <option value="mockup2.php">Paso 3 – Imagen Cuerpo lado lateral izquierdo</option>
    <option value="mockup3.php">Paso 4 – Imagen frontal lateral derecha 10° a 15°</option>
    <option value="mockup4.php">Paso 5 – Imagen frontal lateral izquierda 10° a 15°</option>
    <option value="mockup5.php">Paso 6 – Imagen nariz 15°</option>
    <option value="mockup6.php">Paso 7 – Imagen nariz 30°</option>
    <option value="mockup7.php">Paso 8 – Imagen frontal nariz 0°</option>
    <option value="mockup8.php">Paso 9 – Imagen adicional / Complementaria</option>
    <option value="mockup9.php">Paso 10 – Confirmación</option>
  </select>
</footer>

<script>
  function irAMockup(url) {
    if (url) {
      window.location.href = url;
    }
  }
</script>

<script>
  // Toggle menú móvil
  function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    const btn = document.getElementById('mobileMenuButton');
    if (menu.classList.contains('open')) {
      menu.classList.remove('open');
      menu.setAttribute('aria-hidden', 'true');
      btn.setAttribute('aria-expanded', 'false');
    } else {
      menu.classList.add('open');
      menu.setAttribute('aria-hidden', 'false');
      btn.setAttribute('aria-expanded', 'true');
    }
  }

  // Mostrar info según clase mascota
  function mostrarInfoClase(valor) {
    const infoDiv = document.getElementById('info-clase');
    const infoText = {
      'Caninos': 'Los caninos son mascotas leales y protectoras, ideales para familias activas.',
      'Felinos': 'Los felinos son independientes y cariñosos, excelentes compañeros para espacios pequeños.',
      'Equinos': 'Los equinos requieren cuidados especiales y espacio amplio para su bienestar.',
      'Porcinos': 'Los porcinos son inteligentes y sociables, necesitan atención constante.',
      'Ganado': 'El ganado es fundamental para la producción y requiere manejo adecuado para su salud.',
      'Otros Mamiferos': 'Otros mamíferos incluyen diversas especies que pueden ser mascotas exóticas o de granja.'
    };
    if (valor && infoText[valor]) {
      infoDiv.textContent = infoText[valor];
      infoDiv.classList.remove('hidden');
    } else {
      infoDiv.textContent = '';
      infoDiv.classList.add('hidden');
    }
  }

  // Previsualizar imagen de perfil
  const inputImg = document.getElementById('img_perfil');
  const previewDiv = document.getElementById('preview_perfil');

  inputImg.addEventListener('change', function(event) {
    previewDiv.innerHTML = '';
    const file = event.target.files[0];
    if (file && file.type.startsWith('image/')) {
      const img = document.createElement('img');
      img.classList.add('max-w-xs', 'fade-in');
      img.alt = 'Foto de perfil seleccionada';
      const reader = new FileReader();
      reader.onload = function(e) {
        img.src = e.target.result;
        previewDiv.appendChild(img);
      };
      reader.readAsDataURL(file);
    }
  });

  // Validar campos y guardar en localStorage con base64
  function validarYContinuar() {
    const nombre = document.getElementById('nombre').value.trim();
    const apellidos = document.getElementById('apellidos').value.trim();
    const clase = document.getElementById('clase_mascota').value;
    const imgFile = document.getElementById('img_perfil').files[0];

    if (!nombre) {
      alert('Por favor, ingresa el nombre de tu mascota.');
      return;
    }
    if (!apellidos) {
      alert('Por favor, ingresa el apellido de tu mascota.');
      return;
    }
    if (!clase) {
      alert('Por favor, selecciona la clase de tu mascota.');
      return;
    }
    if (!imgFile) {
      alert('Por favor, selecciona una foto de perfil para tu mascota.');
      return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
      const mascotaData = {
        nombre: nombre,
        apellidos: apellidos,
        clase: clase,
        imgBase64: e.target.result
      };

      localStorage.setItem('datosMascota', JSON.stringify(mascotaData));
      alert('¡Formulario válido! Datos guardados en localStorage.');

      // Redirigir a la siguiente página del flujo, por ejemplo mockup1.php
      window.location.href = 'mockup1.php'; 
    };
    reader.readAsDataURL(imgFile);
  }

  // Abrir sitio SIAC2025 en nueva pestaña
  function irASiac2025() {
    window.open('https://siac2025.com/', '_blank', 'noopener');
  }

 
</script>
</body>
</html>
