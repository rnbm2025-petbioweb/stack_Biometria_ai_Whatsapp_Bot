<?php

require_once("no_existe.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ✅ Iniciar sesión UNA SOLA VEZ y configurar si es necesario
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_domain', '.siac2025.com');
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_samesite', 'None');
    session_start();
}


// ✅ Mostrar errores si hay fallos
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);


// 🔐 Sesión segura para todos los subdominios
//require_once 'sesion_global.php';
//require_once 'config/conexion_mockups_bd_produccion.php';
//require_once 'config/conexion_petbio_nueva.php';

// ✅ Comprobación de sesión activa
if (!isset($_SESSION['id_usuario'])) {
    header("Location: loginpetbio.php");
    exit;
}
require_once(__DIR__ . '/../dir_config/sesion_global.php');
require_once(__DIR__ . '/../dir_config/conexion_petbio_nueva.php');



define('BASE_PATH', dirname(__DIR__)); // define la raíz del proyecto
require_once BASE_PATH . '/dir_config/sesion_global.php';

//require_once(__DIR__ . '/config/conexion_mockups_bd_produccion.php');

// Obtener condiciones médicas desde la tabla condiciones_mascota
$condiciones_medicas = [];
$query = "SELECT id_condicion, descripcion FROM condiciones_mascota ORDER BY descripcion";
$resultado = mysqli_query($conexion, $query);
if ($resultado) {
  while ($row = mysqli_fetch_assoc($resultado)) {
    $condiciones_medicas[] = $row;
  }
}

// ✅ Obtener datos de sesión si se quieren mostrar (opcional)
$usuarioLogeado = $_SESSION['username'] ?? '';
$apellidos = $_SESSION['apellidos'] ?? '';
$idUsuario = $_SESSION['id_usuario'] ?? '';
?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Área protegida - HUELLIT@S PETBIO</title>
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
  <style>
    #registro-suscripcion input,
    #registro-suscripcion select,
    #registro-suscripcion textarea {
      color: black !important;
      background-color: white !important;
    }
    .label-clara {
      color: #EFE9D5;
      font-weight: 600;
      display: block;
      font-size: 0.875rem;
    }
  </style>
</head>
<body class="bg-petbiofondo font-bahn text-petbioazul">

  <!-- ENCABEZADO -->
  <header class="bg-gray-800 px-4 py-3 flex justify-between items-center shadow">

    <h1 class="text-lg font-bold text-white">🐾 HUELLIT@S</h1>
    <button onclick="toggleMobileMenu()" class="md:hidden text-white">☰</button>
    <nav class="hidden md:flex gap-4 text-sm text-white">
      <button onclick="showScreen('home-screen')" class="hover:text-yellow-400">Inicio</button>
      <button onclick="showScreen('register-screen')" class="hover:text-yellow-400">Registro</button>
      <button onclick="mostrarSuscripcion()" class="hover:text-yellow-400">Suscripción</button>
    </nav>
  </header>

  <!-- MENSAJE Y DATOS DEL USUARIO -->
  <div class="max-w-3xl mx-auto mt-10 p-6 bg-white rounded shadow-md">
    <?php if (isset($_GET['error']) && $_GET['error'] === 'no_sesion'): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-center">
        ⚠ Debes iniciar sesión para continuar.
      </div>
    <?php endif; ?>

    <h1 class="text-2xl font-bold mb-4">Bienvenido, <?= htmlspecialchars($usuarioLogeado) ?> <?= htmlspecialchars($apellidos) ?></h1>
    <p class="mb-2">Este es un contenido protegido al que solo puedes acceder si estás autenticado.</p>
    <p class="text-sm text-gray-600">ID de usuario: <?= htmlspecialchars($idUsuario) ?></p>

    <div class="mt-6">
      <a href="logout.php" class="inline-block bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">Cerrar sesión</a>
    </div>
  </div>

  <!-- MENÚ MÓVIL -->
  <div id="mobileMenu" class="md:hidden hidden bg-white px-4 py-4 space-y-3 shadow-lg rounded-b-2xl border-t border-gray-300 z-50">
    <button onclick="showScreen('home-screen')" class="block w-full text-left py-2 px-4 text-petbioazul bg-gray-100 rounded-lg hover:bg-petbioazulclaro hover:text-white transition duration-200">
      🏠 Inicio
    </button>
    <button onclick="showScreen('register-screen')" class="block w-full text-left py-2 px-4 text-petbioazul bg-gray-100 rounded-lg hover:bg-petbioazulclaro hover:text-white transition duration-200">
      📝 Registro de Mascotas
    </button>
    <button onclick="showScreen('search-screen')" class="block w-full text-left py-2 px-4 text-petbioazul bg-gray-100 rounded-lg hover:bg-petbioazulclaro hover:text-white transition duration-200">
      🔍 Buscar Registro
    </button>
    <button onclick="showScreen('appointments-screen')" class="block w-full text-left py-2 px-4 text-petbioazul bg-gray-100 rounded-lg hover:bg-petbioazulclaro hover:text-white transition duration-200">
      📅 Citas
    </button>
    <button onclick="mostrarSuscripcion(); toggleDropdownMenu();" class="block w-full text-left py-3 px-4 bg-yellow-400 text-black font-bold rounded-xl shadow-md hover:bg-yellow-500 hover:scale-105 transition transform duration-300 animate-bounce">
      💳 ¡Suscríbete Ahora!
    </button>
  </div>

  <!-- MENÚ EXTERNO -->
  <div class="relative mt-4 ml-4">
    <button onclick="toggleDropdownMenu()" class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-petbioazulclaro text-white font-bold hover:bg-petbioverde">
      ☰ Menú institucional
    </button>
    <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-20">
      <a href="https://siac2025.com/" class="block px-4 py-2 text-sm text-petbioazul hover:bg-gray-100">🏠 Inicio</a>
      <a href="https://registro.siac2025.com/" class="block px-4 py-2 text-sm text-petbioazul hover:bg-gray-100">🔗 PETBIO – SIAC</a>
      <a href="https://registro.siac2025.com/2025/06/28/1041/" class="block px-4 py-2 text-sm text-petbioazul hover:bg-gray-100">🛡️ Política de Privacidad</a>
      <a href="https://registro.siac2025.com/2025/06/28/1039/" class="block px-4 py-2 text-sm text-petbioazul hover:bg-gray-100">⚖️ Términos y Condiciones</a>
      <a href="https://petbio11rubm2025.blogspot.com/" class="block px-4 py-2 text-sm text-petbioazul hover:bg-gray-100">📖 Blog PETBIO</a>
      <a href="#" onclick="mostrarSuscripcion(); toggleDropdownMenu();" class="block px-4 py-2 text-sm text-petbioazul hover:bg-gray-100">
        📝 Registro de Suscripción
      </a>
    </div>
  </div>

  <!-- SCRIPTS FUNCIONALES -->
  <script>
    function toggleMobileMenu() {
      const menu = document.getElementById('mobileMenu');
      menu.classList.toggle('hidden');
    }

    function toggleDropdownMenu() {
      const menu = document.getElementById('dropdownMenu');
      menu.classList.toggle('hidden');
    }

      function mostrarSuscripcion() {
    showScreen('subscription-screen');
  }
    
    function showScreen(id) {
      const sections = document.querySelectorAll('section');
      sections.forEach(s => s.classList.add('hidden'));
      const target = document.getElementById(id);
      if (target) target.classList.remove('hidden');
    }

    function showInfo() {
      const info = document.getElementById('info-screen');
      if (info.style.display === 'none') {
        info.style.display = 'block';
      } else {
        info.style.display = 'none';
      }
    }

  </script>


  <!-- PANTALLA INICIO -->
  <section id="home-screen" class="p-6 max-w-4xl mx-auto space-y-4">
    <h2 class="text-xl font-semibold">Sistema de Biometría en Mascotas HUELLIT@S</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <button onclick="showScreen('register-screen')" class="bg-yellow-500 hover:bg-yellow-600 text-black font-bold py-2 px-4 rounded">Registrar Nueva Mascota</button>
      

<button onclick="showScreen('search-screen')" class="bg-yellow-500 hover:bg-yellow-600 text-black font-bold py-2 px-4 rounded">
  Buscar por Huella Digital
</button>
<div class="container hidden" id="search-screen">
  <h1 class="text-xl font-bold mb-4">Buscar Mascota</h1>

  <form method="POST" action="buscar_mascota.php" class="space-y-4">
    <label for="numero_documento" class="block text-sm">Ingrese el Número de Documento:</label>
    <input type="text" id="numero_documento" name="numero_documento" placeholder="Últimos 6 dígitos"
      required class="w-full bg-gray-700 text-white rounded px-3 py-2" />

    <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-black font-bold py-2 px-4 rounded">
      Buscar
    </button>
  </form>

  <button type="button" onclick="showScreen('home-screen')"
    class="mt-4 bg-gray-100 hover:bg-yellow-400 text-black font-semibold py-2 px-4 rounded shadow-md transition duration-200 ease-in-out">
    Volver
  </button>
</div>


<button onclick="showScreen('appointments-screen')" class="bg-yellow-500 hover:bg-yellow-600 text-black font-bold py-2 px-4 rounded">
  Control de Citas
</button>
<div class="container hidden" id="appointments-screen">
  <h1 class="text-xl font-bold mb-4">Control de Citas</h1>

  <label class="block text-sm">Fecha de la Cita:</label>
  <input type="date" id="fecha" class="w-full bg-gray-700 text-white rounded px-3 py-2 mb-4" />

  <label class="block text-sm">Motivo:</label>
  <input type="text" id="motivo" class="w-full bg-gray-700 text-white rounded px-3 py-2 mb-4" />

  <button onclick="alert('Cita guardada con éxito!')"
    class="bg-yellow-500 hover:bg-yellow-600 text-black font-bold py-2 px-4 rounded">
    Guardar Cita
  </button>

  <button type="button" onclick="showScreen('home-screen')"
    class="mt-4 bg-gray-100 hover:bg-yellow-400 text-black font-semibold py-2 px-4 rounded shadow-md transition duration-200 ease-in-out">
    Volver
  </button>
</div>




      <button onclick="showScreen('grooming-screen')" class="bg-gray-700 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">Peluquería</button>
      <button onclick="showScreen('vitals-screen')" class="bg-gray-700 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">Signos Vitales</button>
      <button onclick="showScreen('feeding-screen')" class="bg-gray-700 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">Alimentación</button>
   
<!--   <a href="historia_clinica.php" class="inline-flex items-center justify-center gap-2 bg-petbioazulclaro hover:bg-petbioverde text-white font-semibold px-5 py-3 rounded-xl shadow-md transition-all duration-200">
  🩺 Historia Clínica
</a> --> 

<a href="historia_clinica.php" class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-petbioazulclaro to-petbioverde text-white text-lg font-bold rounded-2xl shadow-lg hover:scale-105 hover:shadow-xl transition-transform duration-200">
  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 4h10a2 2 0 012 2v11a2 2 0 01-2 2H7a2 2 0 01-2-2V9a2 2 0 012-2z" />
  </svg>
  Historia Clínica
</a>



      <!--  <button onclick="showScreen('medical-history-screen')" class="bg-gray-700 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">Historia Clínica</button> -->
      <!-- Botón con estilo -->
<button onclick="showInfo()" class="bg-gray-700 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
  Ver Información del Sistema
</button>

<!-- Contenedor oculto -->
<div class="info-container" id="info-screen" style="display: none;">
  <h2>Información del Sistema</h2>
  <p>Sistema de Biometría en Mascotas Version v 1.0.0.1 2025</p>
  <p>Desarrollado para la identificación y registro de Animales de Compañia mediante su huella digital.</p>
  <p>Por Juan Carlos Osorno Londoño.</p>
  <p>Estudiante de ingeniería de software de la I.U Pascual Bravo</p>
  <button onclick="showInfo()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-1 px-3 rounded">Cerrar</button>
</div>

    </div>

    </div>
  </section>

  <!-- PANTALLA REGISTRO -->
 <section id="register-screen" class="hidden p-6 max-w-4xl mx-auto bg-gray-800 rounded-lg shadow-md">
  <h2 class="text-xl font-bold mb-4 text-white">Registrar Nueva Mascota</h2>

  <a href="https://petbio.siac2025.com/index.php"
   class="inline-flex items-center gap-2 px-6 py-3 bg-petbioverde text-white font-semibold rounded-full shadow hover:bg-petbioazulclaro hover:scale-105 transition-all duration-300">
  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
       viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M12 4v16m8-8H4" />
  </svg>
  Registro Guiado
</a>
<p> 

</p> 

  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Registro Tradicional PETBIO</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Bahnschrift&display=swap');
    body {
      font-family: 'Bahnschrift', sans-serif;
    }
    .label-clara {
      color: #ffffff;
      font-weight: bold;
    }
  </style>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      // Función que se dispara al tocar un campo
      window.verificarIngresoBienvenida = function(event) {
        console.log('Campo tocado:', event.target.name);
      };

      // Función para mostrar campos adicionales si se tiene documento
      window.mostrarCampos = function () {
        const select = document.getElementById('con_documento');
        const campos = document.getElementById('camposDocumento');
        campos.style.display = (select.value === 'Sí') ? 'block' : 'none';
      };
    });
  </script>
</head>

<body class="bg-petbiofondo font-bahn text-petbioazul">

  <!-- ENCABEZADO -->
  <header class="bg-gray-800 px-4 py-3 flex justify-between items-center shadow">
    <h1 class="text-white text-xl font-bold">🐾 Registro PETBIO</h1>
    <a href="/" class="text-white underline">Volver al inicio</a>
  </header>

  <!-- FORMULARIO -->
  <main class="max-w-4xl mx-auto bg-gray-900 rounded-xl shadow-md mt-10 p-6 text-white">
    <form action="identidad_petbio.php" method="POST" enctype="multipart/form-data" class="space-y-6">
      <h2 class="text-xl font-bold text-center text-white">Registro tradicional</h2>

      <div>
        <label class="label-clara">Nombre de la Mascota:</label>
        <input type="text" name="nombre" required 
          class="w-full bg-gray-700 text-white rounded px-3 py-2"
          onclick="verificarIngresoBienvenida(event)" />
      </div>

      <div>
        <label class="label-clara">Apellidos del Cuidador:</label>
        <input type="text" name="apellidos" required 
          class="w-full bg-gray-700 text-white rounded px-3 py-2"
          onclick="verificarIngresoBienvenida(event)" />
      </div>

      <div>
        <label class="label-clara">Clase de Mascota:</label>
        <select name="clase_mascota" required class="w-full bg-gray-700 text-white rounded px-3 py-2">
          <option value="Caninos">Caninos</option>
          <option value="Felinos">Felinos</option>
          <option value="Equinos">Equinos</option>
          <option value="Porcinos">Porcinos</option>
          <option value="Ganado">Ganado</option>
          <option value="Otros Mamiferos">Otros Mamíferos</option>
        </select>
      </div>

      <div>
        <label class="label-clara">Condición institucional/social:</label>
        <select name="condicion_mascota" required class="w-full bg-gray-700 text-white rounded px-3 py-2">
          <option value="">Selecciona una opción</option>
          <option value="hogar_familiar">Hogar familiar</option>
          <option value="hogar_veterinario">Hogar veterinario</option>
          <option value="hogar_paso">Hogar de paso</option>
          <option value="centro_regulador">Centro regulador</option>
          <option value="abandono">Condición de abandono</option>
          <option value="otros">Otros</option>
        </select>
      </div>

  <!--    <div>
        <label class="label-clara">Condición médica:</label>
        <select name="condicion_medica" required class="w-full bg-gray-700 text-white rounded px-3 py-2">
          <option value="">Selecciona una condición médica</option>
          <!- Aquí debes generar dinámicamente las opciones desde PHP ->
        </select>
      </div>  -->

      <select name="condicion_medica" required class="w-full bg-gray-700 text-white rounded px-3 py-2">
  <option value="">Selecciona una condición médica</option>
  <?php foreach ($condiciones_medicas as $condicion): ?>
    <option value="<?= htmlspecialchars($condicion['descripcion']) ?>">
      <?= htmlspecialchars($condicion['descripcion']) ?>
    </option>
  <?php endforeach; ?>
</select>


      <div>
        <label class="label-clara">Raza:</label>
        <input type="text" name="raza" required class="w-full bg-gray-700 text-white rounded px-3 py-2" />
      </div>

      <div>
        <label class="label-clara">Edad:</label>
        <input type="number" name="edad" required class="w-full bg-gray-700 text-white rounded px-3 py-2" />
      </div>

      <div>
        <label class="label-clara">Ciudad:</label>
        <input type="text" name="ciudad" required class="w-full bg-gray-700 text-white rounded px-3 py-2" />
      </div>

      <div>
        <label class="label-clara">Barrio:</label>
        <input type="text" name="barrio" required class="w-full bg-gray-700 text-white rounded px-3 py-2" />
      </div>

      <div>
        <label class="label-clara">Código Postal:</label>
        <input type="text" name="codigo_postal" required class="w-full bg-gray-700 text-white rounded px-3 py-2" />
      </div>

      <div>
        <label class="label-clara">Relación con la Mascota:</label>
        <select name="relacion" required class="w-full bg-gray-700 text-white rounded px-3 py-2">
          <option value="Amo">Cuidador Amo</option>
          <option value="Acompañante">Cuidador Acompañante</option>
          <option value="Médico Veterinario">Médico Veterinario</option>
          <option value="Fundacion Canina">Fundación Canina</option>
          <option value="Independientes">Independientes</option>
        </select>
      </div>

   
      <div>
        <label class="label-clara">¿Tiene documento de identidad?</label>
        <select name="con_documento" id="con_documento" onchange="mostrarCampos()" required class="w-full bg-gray-700 text-white rounded px-3 py-2">
          <option value="No">No</option>
          <option value="Sí">Sí</option>
        </select>

        <div id="camposDocumento" style="display:none">
          <label class="label-clara mt-2">Tipo de Documento:</label>
          <select name="tipoDocumento" class="w-full bg-gray-700 text-white rounded px-3 py-2">
            <option value="digital">Digital</option>
            <option value="fisico">Físico</option>
          </select>

          <label class="label-clara mt-2">Descripción del Documento:</label>
          <select name="descripcionDocumento" class="w-full bg-gray-700 text-white rounded px-3 py-2">
            <option value="pasaporte">Pasaporte</option>
            <option value="carnet_vacunacion">Carnet de Vacunación</option>
            <option value="entidad_salud">Entidad de Salud</option>
            <option value="rnbm">Registro Nacional Biométrico (RNBM)</option>
          </select>

          <label class="label-clara mt-2">Entidad Expedidora:</label>
          <input type="text" name="entidadExpedidora" class="w-full bg-gray-700 text-white rounded px-3 py-2" />

          <label class="label-clara mt-2">Número del Documento:</label>
          <input type="text" name="numeroDocumento" class="w-full bg-gray-700 text-white rounded px-3 py-2" />
        </div>

        <p>   <label class="label-clara mt-2"> </label>      </p>

        <div class="mb-6 w-full max-w-md mx-auto">
  <img 
    src="imagenes_guias_2025/f_0_enfoque.jpeg" 
    alt="Instrucciones" 
    class="w-full h-auto rounded-xl shadow"
  />
  <p class="text-sm mt-2 text-gray-600 text-center">
    Captura la trufa en ángulo 0°, enfocando de cerca con buena luz.
  </p>
</div>

      </div>
   

     <!-- BIOMETRÍA VISUAL -->
<div class="biometric-capture space-y-10 max-w-4xl mx-auto px-4">
  <p class="text-gray-700 text-base text-justify leading-relaxed">
    Utiliza la cámara para capturar la <strong>huella nasal de la mascota</strong> desde distintos ángulos. Asegúrate de buena iluminación y enfoque.
  </p>

  <!-- BLOQUES DE CAPTURA -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    
    <!-- Captura individual -->
    <div class="flex flex-col items-center space-y-2">
      <label class="label-clara text-center">Frontal 0°</label>
      <img src="imagenes_guias_2025/frontal_0.jpeg" class="w-40 rounded shadow" alt="Frontal 0°">
      <input type="file" name="img_hf0" accept="image/*" onchange="vistaPrevia(this, 'preview_hf0')" class="mt-2">
      <img id="preview_hf0" class="w-32 h-auto mt-1 rounded border hidden">
      <button type="button" onclick="limpiarInput('img_hf0', 'preview_hf0')" class="text-xs text-blue-600 hover:underline">🧼 Limpiar imagen</button>
    </div>

    <div class="flex flex-col items-center space-y-2">
      <label class="label-clara text-center">Frontal 15°</label>
      <img src="imagenes_guias_2025/frontal_admitida.jpeg" class="w-40 rounded shadow" alt="Frontal 15°">
      <input type="file" name="img_hf15" accept="image/*" onchange="vistaPrevia(this, 'preview_hf15')">
      <img id="preview_hf15" class="w-32 h-auto mt-1 rounded border hidden">
      <button type="button" onclick="limpiarInput('img_hf15', 'preview_hf15')" class="text-xs text-blue-600 hover:underline">🧼 Limpiar imagen</button>
    </div>

    <div class="flex flex-col items-center space-y-2">
      <label class="label-clara text-center">Frontal 10°–30°</label>
      <img src="imagenes_guias_2025/frontal_15_30.jpeg" class="w-40 rounded shadow" alt="Frontal 10-30°">
      <input type="file" name="img_hf30" accept="image/*" onchange="vistaPrevia(this, 'preview_hf30')">
      <img id="preview_hf30" class="w-32 h-auto mt-1 rounded border hidden">
      <button type="button" onclick="limpiarInput('img_hf30', 'preview_hf30')" class="text-xs text-blue-600 hover:underline">🧼 Limpiar imagen</button>
    </div>

    <div class="flex flex-col items-center space-y-2">
      <label class="label-clara text-center">Frontal lateral derecha 15°</label>
      <img src="imagenes_guias_2025/fld_15.jpeg" class="w-40 rounded shadow" alt="Lateral derecha 15°">
      <input type="file" name="img_hfld15" accept="image/*" onchange="vistaPrevia(this, 'preview_hfld15')">
      <img id="preview_hfld15" class="w-32 h-auto mt-1 rounded border hidden">
      <button type="button" onclick="limpiarInput('img_hfld15', 'preview_hfld15')" class="text-xs text-blue-600 hover:underline">🧼 Limpiar imagen</button>
    </div>

    <div class="flex flex-col items-center space-y-2">
      <label class="label-clara text-center">Frontal lateral izquierda 15°</label>
      <img src="imagenes_guias_2025/fld_10.jpeg" class="w-40 rounded shadow" alt="Lateral izquierda 15°">
      <input type="file" name="img_hfli15" accept="image/*" onchange="vistaPrevia(this, 'preview_hfli15')">
      <img id="preview_hfli15" class="w-32 h-auto mt-1 rounded border hidden">
      <button type="button" onclick="limpiarInput('img_hfli15', 'preview_hfli15')" class="text-xs text-blue-600 hover:underline">🧼 Limpiar imagen</button>
    </div>

    <div class="flex flex-col items-center space-y-2">
      <label class="label-clara text-center">Perfil</label>
      <img src="imagenes_guias_2025/american_bulldog_51.jpg" class="w-40 rounded shadow" alt="Perfil">
      <input type="file" name="img_perfil" accept="image/*" onchange="vistaPrevia(this, 'preview_perfil')">
      <img id="preview_perfil" class="w-32 h-auto mt-1 rounded border hidden">
      <button type="button" onclick="limpiarInput('img_perfil', 'preview_perfil')" class="text-xs text-blue-600 hover:underline">🧼 Limpiar imagen</button>
    </div>

    <div class="flex flex-col items-center space-y-2">
      <label class="label-clara text-center">Lateral cuerpo derecho</label>
      <img src="imagenes_guias_2025/american_bulldog_43 copy.jpg" class="w-40 rounded shadow" alt="Lateral derecho">
      <input type="file" name="img_latdr" accept="image/*" onchange="vistaPrevia(this, 'preview_latdr')">
      <img id="preview_latdr" class="w-32 h-auto mt-1 rounded border hidden">
      <button type="button" onclick="limpiarInput('img_latdr', 'preview_latdr')" class="text-xs text-blue-600 hover:underline">🧼 Limpiar imagen</button>
    </div>

    <div class="flex flex-col items-center space-y-2">
      <label class="label-clara text-center">Lateral cuerpo izquierdo</label>
      <img src="imagenes_guias_2025/tayson.jpeg" class="w-40 rounded shadow" alt="Lateral izquierdo">
      <input type="file" name="img_latiz" accept="image/*" onchange="vistaPrevia(this, 'preview_latiz')">
      <img id="preview_latiz" class="w-32 h-auto mt-1 rounded border hidden">
      <button type="button" onclick="limpiarInput('img_latiz', 'preview_latiz')" class="text-xs text-blue-600 hover:underline">🧼 Limpiar imagen</button>
    </div>

  </div>
</div>

      <!-- BOTONES -->
      <div class="flex flex-wrap gap-4 justify-center mt-6">
        <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-black font-bold py-2 px-4 rounded">
          Registrar Mascota
        </button>

        <button type="button" onclick="showScreen('home-screen')" class="bg-gray-100 hover:bg-yellow-400 text-black font-semibold py-2 px-4 rounded shadow-md">
          Volver
        </button>

        <button onclick="window.location.href='logout.php'" class="bg-[#114358] text-white px-6 py-2 rounded-full shadow-lg hover:bg-[#0d3d4d] hover:scale-105 transition transform duration-300 ease-in-out flex items-center gap-2">
          <span>🚪</span>
          <span>Cerrar Sesión</span>
        </button>
      </div>
    </form>
  </main>

  <script>
  function vistaPrevia(input, previewId) {
    const file = input.files[0];
    const preview = document.getElementById(previewId);
    if (file) {
      const reader = new FileReader();
      reader.onload = () => {
        preview.src = reader.result;
        preview.classList.remove('hidden');
      };
      reader.readAsDataURL(file);
    }
  }

  function limpiarInput(inputName, previewId) {
    const input = document.getElementsByName(inputName)[0];
    const preview = document.getElementById(previewId);
    input.value = '';
    preview.src = '';
    preview.classList.add('hidden');
  }
</script>

  <script>
  // Variable para asegurarnos de preguntar solo una vez
  let decisionTomada = false;

  function preguntarDireccion(event) {
    if (decisionTomada) return;

    const continuar = confirm("¿Deseas hacer el Registro Guiado en petbio.siac2025.com?\n\nPresiona ACEPTAR para ir al Registro Guiado\nPresiona CANCELAR para continuar aquí.");

    if (continuar) {
      window.location.href = 'https://petbio.siac2025.com';
    } else {
      // continúa en identidad_rubm.php (ya estás aquí)
      decisionTomada = true;
    }
  }

  // Asignar el evento a todos los inputs visibles
  window.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('input, select, textarea').forEach(input => {
      input.addEventListener('focus', preguntarDireccion, { once: true });
    });
  });
</script>

<section id="subscription-screen" class="hidden p-6 max-w-4xl mx-auto bg-white rounded shadow">
  <!-- Aquí va el formulario que ya compartiste para pago_suscripcion.php -->

 <!--  <form action="guardar_suscripcion.php" method="POST" enctype="multipart/form-data" class="max-w-xl mx-auto p-4 bg-white rounded shadow"> -->
<form id="form-suscripcion" action="guardar_suscripcion.php" method="POST" ...>

  <!-- Nombres -->
  <div class="mb-4">
    <label for="nombres" class="text-gray-800 font-medium">👤 Nombres:</label>
    <input type="text" name="nombres" id="nombres" required class="w-full border rounded px-3 py-2">
  </div>

  <!-- Apellidos -->
  <div class="mb-4">
    <label for="apellidos" class="text-gray-800 font-medium">🧾 Apellidos:</label>
    <input type="text" name="apellidos" id="apellidos" required class="w-full border rounded px-3 py-2">
  </div>

  <!-- Documento -->
  <div class="mb-4">
    <label for="documento" class="text-gray-800 font-medium">🆔 Documento:</label>
    <input type="text" name="documento" id="documento" required class="w-full border rounded px-3 py-2">
  </div>

  <!-- Teléfono -->
  <div class="mb-4">
    <label for="telefono" class="text-gray-800 font-medium">📞 Teléfono:</label>
    <input type="text" name="telefono" id="telefono" required class="w-full border rounded px-3 py-2">
  </div>

  <!-- WhatsApp -->
  <div class="mb-4">
    <label class="inline-flex items-center">
      <input type="checkbox" name="whatsapp" class="form-checkbox text-green-500">
      <span class="ml-2 text-gray-700">¿Este número tiene WhatsApp?</span>
    </label>
  </div>

  <!-- Cantidad exacta -->
  <div class="mb-4">
    <label for="cantidad_exacta" class="text-gray-800 font-medium">Cantidad exacta de mascotas:</label>
    <input type="number" name="cantidad_exacta" id="cantidad_exacta" min="1" max="500" required class="w-full border rounded px-3 py-2">
  </div>

  <!-- ComboBox sugerido -->
  <div class="mb-4">
    <label for="cantidad" class="text-gray-800 font-medium">🐾 Número de mascotas (opcional):</label>
    <select name="cantidad" id="cantidad" class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700">
      <option value="">Seleccione una opción</option>
      <option value="1">1-5</option>
      <option value="6">6-12</option>
      <option value="13">13-50</option>
      <option value="51">51-100</option>
      <option value="101">101-500</option>
    </select>
  </div>

  <!-- Periodo -->
  <div class="mb-4">
    <label for="periodo" class="text-gray-800 font-medium">📅 Período de suscripción:</label>
    <select name="periodo" id="periodo" required class="...">
  <option value="">Seleccionar</option>
  <option value="trimestral">Trimestral</option>
  <option value="semestral">Semestral</option>
  <option value="anual">Anual</option>
</select>

  </div>

  <!-- Total (calculado) -->
  <div class="mb-4">
    <label for="total_pagado" class="text-gray-800 font-medium">💰 Total a pagar:</label>
    <input type="text" name="total_pagado" id="total_pagado" readonly class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-600">
  </div>

  <!-- Evidencia -->
  <div class="mb-4">
    <label for="evidencia_pago" class="text-gray-800 font-medium">📎 Evidencia del pago (imagen):</label>
    <input type="file" name="evidencia_pago" id="evidencia_pago" accept="image/*" required class="w-full border rounded px-3 py-2">
  </div>

  <!-- Ocultos -->
  <input type="hidden" name="cantidad_mascotas" id="cantidad_mascotas">
  <input type="hidden" name="periodo_tarifa" id="periodo_tarifa">

  <!-- Botón -->
  <button type="submit" class="w-full bg-petbioazul text-white font-bold py-2 rounded hover:bg-petbioverde">
    ✅ Enviar Suscripción
  </button>

  <!-- Resultado -->
  <div id="resultadoPrecio" class="mt-4 text-sm text-gray-700 font-semibold"></div>
</form>

<script>
  const form = document.getElementById('form-suscripcion');
  form.addEventListener('submit', function (event) {
    event.preventDefault(); // Previene envío automático

    const formData = new FormData(form);

    fetch('guardar_suscripcion.php', {
      method: 'POST',
      body: formData
    })
    .then(res => {
      if (res.ok) {
        window.location.href = 'index.php';
      } else {
        alert('❌ Hubo un error al guardar la suscripción.');
      }
    })
    .catch(err => {
      alert('❌ Error de conexión.');
    });
  });
</script>

<!-- Script para calcular tarifa -->
<script>
  const tarifas = [
  { min: 1, max: 5, trimestral: 17000, semestral: 30000, anual: 55000 },
  { min: 6, max: 12, trimestral: 16000, semestral: 29000, anual: 54000 },
  { min: 13, max: 50, trimestral: 15000, semestral: 27000, anual: 52000 },
  { min: 51, max: 100, trimestral: 14000, semestral: 25000, anual: 50000 },
  { min: 101, max: 500, trimestral: 13000, semestral: 23000, anual: 48000 }
];


  const cantidadInput = document.getElementById('cantidad_exacta');
  const periodoSelect = document.getElementById('periodo');
  const resultadoPrecio = document.getElementById('resultadoPrecio');
  const inputMascotas = document.getElementById('cantidad_mascotas');
  const inputPeriodo = document.getElementById('periodo_tarifa');
  const inputTotal = document.getElementById('total_pagado');

  function calcularYMostrarPrecio() {
    const cantidad = parseInt(cantidadInput.value);
    const periodo = periodoSelect.value;

    if (!cantidad || !periodo) {
      resultadoPrecio.innerText = '';
      return;
    }

    const tarifa = tarifas.find(t => cantidad >= t.min && cantidad <= t.max);
    if (!tarifa) {
      resultadoPrecio.innerText = 'Cantidad fuera de rango (1-500)';
      inputMascotas.value = '';
      inputPeriodo.value = '';
      inputTotal.value = '';
      return;
    }

    const precioUnitario = tarifa[periodo];
    const total = precioUnitario * cantidad;

    let mensaje = 
  💰 <strong>Precio por mascota:</strong> $${precioUnitario.toLocaleString()}<br>
  🧾 <strong>Total a pagar:</strong> $${total.toLocaleString()}
;

// Comparador para ahorro, tomando el plan trimestral como base
if (periodo === 'semestral') {
  const trimestralUnitario = tarifa.trimestral;
  const totalTrimestral = trimestralUnitario * cantidad * 2; // 2 trimestres = 6 meses
  const ahorro = totalTrimestral - total;
  if (ahorro > 0) {
    mensaje += <br>🎁 <strong>Ahorro en plan semestral:</strong> $${ahorro.toLocaleString()} comparado con pagar 2 trimestres;
  }
} else if (periodo === 'anual') {
  const trimestralUnitario = tarifa.trimestral;
  const totalTrimestral = trimestralUnitario * cantidad * 4; // 4 trimestres = 12 meses
  const ahorro = totalTrimestral - total;
  if (ahorro > 0) {
    mensaje += <br>🎁 <strong>Ahorro en plan anual:</strong> $${ahorro.toLocaleString()} comparado con pagar 4 trimestres;
  }
}

resultadoPrecio.innerHTML = mensaje;

    
    inputMascotas.value = cantidad;
    inputPeriodo.value = periodo;
    inputTotal.value = total;
  }

  cantidadInput.addEventListener('input', calcularYMostrarPrecio);
  periodoSelect.addEventListener('change', calcularYMostrarPrecio);

  const comboBox = document.getElementById("comboBox");
  if (comboBox) {
    comboBox.addEventListener("click", function (event) {
      if (event.target && event.target.matches(".combo-option")) {
        const value = event.target.dataset.value;
        document.getElementById("cantidad").value = value;
        cantidadInput.value = value;
        calcularYMostrarPrecio();
      }
    });
  }
</script>
</section>

<!-- Aquí puedes tener contenido HTML -->

<!-- 👇 Inserta justo antes del cierre del body -->

<script>
  function mostrarCampos() {
    const select = document.getElementById('con_documento');
    const campos = document.getElementById('camposDocumento');
    campos.style.display = (select.value === 'Sí') ? 'block' : 'none';
  }
</script>

<!--    FUNCION ´PARA EL MODAL
<script>
  function verificarIngresoBienvenida(event) {
    const pasoCorrecto = localStorage.getItem('ingreso_validado') === 'true';

    if (!pasoCorrecto) {
      event.preventDefault();
      event.target.blur(); // evita que el input quede enfocado
      alert("👋 Para comenzar el registro, primero debes pasar por la pantalla de bienvenida.");
      window.location.href = '/index.php';
    }
  }
</script>  -->


<script>
  function verificarIngresoBienvenida(event) {
    const pasoCorrecto = localStorage.getItem('ingreso_validado') === 'true';
    const permitidoContinuar = sessionStorage.getItem('permiso_actual') === 'true';

    if (!pasoCorrecto && !permitidoContinuar) {
      event.preventDefault();
      event.target.blur();

      // Mostrar modal
      const modal = document.getElementById('modalGuia');
      modal.classList.remove('hidden');
    }
  }

  function irAModoGuiado() {
    // Redirigir al inicio del flujo guiado
    window.location.href = '/index.php';
  }

  function continuarAqui() {
    // Permitir continuar durante esta sesión
    sessionStorage.setItem('permiso_actual', 'true');
    // Ocultar modal
    document.getElementById('modalGuia').classList.add('hidden');
  }
</script>


<script>
document.addEventListener("DOMContentLoaded", () => {
  if (localStorage.getItem('paso_confirmado') === 'true') {
    // Mostrar la pantalla de registro
    showScreen('register-screen');

    // Lista de campos que se van a rellenar automáticamente
    const campos = [
      'nombre', 'apellidos', 'clase_mascota', 'raza',
      'edad', 'ciudad', 'barrio', 'codigo_postal',
      'relacion', 'condicion_mascota'
    ];

    // Rellenar campos desde localStorage
    campos.forEach(campo => {
      const valor = localStorage.getItem(form_${campo});
      const input = document.querySelector([name="${campo}"], [name="${campo}_mascota"], [name="${campo}mascota"]);
      if (input && valor) {
        input.value = valor;
      }
    });

    // Agregar advertencia y botón "Confirmar Registro"
    const form = document.querySelector('#register-screen form');
    if (form && !document.getElementById('advertencia-registro')) {
      const advertencia = document.createElement('div');
      advertencia.id = 'advertencia-registro';
      advertencia.className = 'bg-yellow-100 text-yellow-800 p-3 rounded mt-4 shadow-md';
      advertencia.innerHTML = 
        ⚠ Verifica que hayas subido las imágenes de la mascota antes de enviar el formulario.<br>
        <strong>Cuando estés listo, haz clic en "✅ Confirmar Registro".</strong>
      ;
      form.appendChild(advertencia);

      const botonConfirmar = document.createElement('button');
      botonConfirmar.type = 'submit';
      botonConfirmar.innerText = '✅ Confirmar Registro';
      botonConfirmar.className = 'mt-4 bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded';
      form.appendChild(botonConfirmar);
    }
  }

  const form = document.querySelector('#register-screen form');
  if (form) {
    form.addEventListener('submit', () => {
      localStorage.removeItem('paso_confirmado');
      const campos = [
        'nombre', 'apellidos', 'clase_mascota', 'raza',
        'edad', 'ciudad', 'barrio', 'codigo_postal',
        'relacion', 'condicion_mascota'
      ];
      campos.forEach(campo => localStorage.removeItem(form_${campo}));
    });
  }
});
</script>
<script>
  const campos = ['nombre', 'apellidos', 'clase_mascota', 'raza', 'edad', 'ciudad', 'barrio', 'codigo_postal'];
  campos.forEach(id => {
    const input = document.getElementById(id);
    const valor = localStorage.getItem(id);
    if (input && valor) input.value = valor;
  });
</script>
<script>
  document.getElementById('input_img_perfil').addEventListener('change', function (e) {
    const archivo = e.target.files[0];
    if (!archivo) return;

    const lector = new FileReader();
    lector.onload = function (event) {
      const base64 = event.target.result;
      document.getElementById('img_perfil').value = base64;
    };
    lector.readAsDataURL(archivo);
  });
</script>


<!-- Modal de opción guiada -->
<div id="modalGuia" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
  <div class="bg-white rounded-lg p-6 shadow-xl max-w-md w-full text-center">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">¿Cómo deseas continuar?</h2>
    <p class="text-gray-600 mb-6">👋 Para comenzar el registro debes pasar por la pantalla de bienvenida.</p>
    <div class="flex justify-around">
      <button onclick="irAModoGuiado()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        🧭 Modo Guiado
      </button>
      <button onclick="continuarAqui()" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">
        📍 Continuar acá
      </button>
    </div>
  </div>
</div>


</body>
</html>