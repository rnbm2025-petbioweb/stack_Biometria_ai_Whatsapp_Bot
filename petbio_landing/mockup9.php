<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conexión
require_once(__DIR__ . '/config/conexion_mockups_bd_produccion.php');

// Sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    require_once 'sesion_global.php';
}

$usuarioLogeado = $_SESSION['username'] ?? null;
$apellidos = $_SESSION['apellidos'] ?? '';
$idUsuario = $_SESSION['id_usuario'] ?? null;

if (!$usuarioLogeado || !$idUsuario) {
    header("Location: loginpetbio.php?error=no_sesion");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Mockup 9 - Confirmación de Datos PETBIO</title>

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
      <a href="/contacto.html" class="transition-colors hover:text-white hover:bg-blue-600 px-3 py-2 rounded">Contacto</a>
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
<body class="bg-[#FFF8F0] min-h-screen flex flex-col items-center justify-center p-6">
<p> </p>
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
      </div>
    </div>

    <div class="flex-shrink-0">
      <img src="./imagenes_guias_2025/bienvenida_00011.png" alt="Bienvenida" class="w-64 rounded-xl shadow-lg">
    </div>
  </div>

  <div class="bg-white rounded-xl shadow-xl p-6 max-w-4xl w-full">
    <h2 class="text-3xl font-bold text-[#114358] mb-6 text-center">Paso Final: Confirmación de Datos</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-800 text-lg mb-6">
      <p><strong>🐶 Nombre:</strong> <span id="nombre_mostrar"></span></p>
      <p><strong>👤 Apellido (cuidador):</strong> <span id="apellidos_mostrar"></span></p>
      <p><strong>🐾 Clase:</strong> <span id="clase_mascota_mostrar"></span></p>
      <p><strong>📘 Raza:</strong> <span id="raza_mostrar"></span></p>
      <p><strong>🎂 Edad:</strong> <span id="edad_mostrar"></span></p>
      <p><strong>🌇 Ciudad:</strong> <span id="ciudad_mostrar"></span></p>
      <p><strong>🏡 Barrio:</strong> <span id="barrio_mostrar"></span></p>
      <p><strong>📬 Código Postal:</strong> <span id="codigo_postal_mostrar"></span></p>
      <p><strong>🧑‍🧑 Relación:</strong> <span id="relacion_mostrar"></span></p>
      <p><strong>🦥 Condición:</strong> <span id="condicion_mascota_mostrar"></span></p>
    </div>

    <h3 class="text-2xl font-bold text-[#114358] mt-8 mb-4 text-center">Imágenes cargadas</h3>
    <div id="preview-imagenes" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mt-6"></div>

    <div class="flex justify-center gap-4 mt-8">
      <button id="btn-corregir" onclick="marcarCorreccion()" class="bg-gray-300 text-[#114358] px-6 py-2 rounded-full hover:bg-gray-400">
        🔄 Corregir datos
      </button>
      <button id="btn-enviar" onclick="validarYEnviar(event)" class="bg-[#114358] text-white px-8 py-3 rounded-full shadow hover:bg-[#0d3d4d] text-lg">
        ✅ Registrar ahora
      </button>
    </div>

    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded mt-6 text-sm text-yellow-800">
      <h3 class="font-bold text-base mb-2 flex items-center gap-2">
        ⚖️ Consideraciones legales e informativas
      </h3>
      <p>
        Asegúrate de que la imagen esté bien enfocada, sin sombras ni desenfoques. Solo se aceptan archivos .jpg o .png menores a 10MB.
      </p>
    </div>
  </div>

  <form id="form-final" action="identidad_petbio_mockups.php" method="POST">
    <?php
    // Generar los campos ocultos del formulario (automático)
    $campos = [
      "nombre", "apellidos", "clase_mascota", "raza", "edad", "ciudad", "barrio",
      "codigo_postal", "relacion", "condicion_mascota",
      "img_perfil", "img_latiz", "img_latdr", "img_hf0", "img_hf15", "img_hf30", "img_hfld15", "img_hfli15"
    ];

    foreach ($campos as $campo) {
      echo "<input type='hidden' name='$campo' id='$campo'>\n";
    }
    ?>
  </form>

  <script>
    const camposTexto = [
      'nombre', 'apellidos', 'clase_mascota', 'raza', 'edad',
      'ciudad', 'barrio', 'codigo_postal', 'relacion', 'condicion_mascota'
    ];

    const camposImagenes = [
      'img_perfil', 'img_latiz', 'img_latdr',
      'img_hf0', 'img_hf15', 'img_hf30',
      'img_hfld15', 'img_hfli15'
    ];

    function cargarDatosYForm() {
      camposTexto.forEach(id => {
        const valor = localStorage.getItem(id) || '—';
        document.getElementById(`${id}_mostrar`).textContent = valor;
        const input = document.getElementById(id);
        if (input) input.value = valor;
      });

      camposImagenes.forEach(id => {
        const base64 = localStorage.getItem(id);
        const input = document.getElementById(id);
        if (input) input.value = base64 || '';
      });
    }

    function cargarImagenes() {
      const etiquetas = {
        img_perfil: "📸 Foto de perfil",
        img_latdr: "📷 Lateral derecho del cuerpo",
        img_latiz: "📷 Lateral izquierdo del cuerpo",
        img_hf0: "🦴 Trufa frontal 0°",
        img_hf15: "🦴 Trufa 15°",
        img_hf30: "🦴 Trufa 30°",
        img_hfld15: "🦴 Trufa lateral derecha 15°",
        img_hfli15: "🦴 Trufa lateral izquierda 15°"
      };

      const contenedor = document.getElementById('preview-imagenes');
      contenedor.innerHTML = "";

      for (const key of camposImagenes) {
        const base64 = localStorage.getItem(key);
        if (base64) {
          const div = document.createElement('div');
          div.className = "text-center mb-4";

          const label = document.createElement('p');
          label.textContent = etiquetas[key] || key;
          label.className = "text-gray-700 text-sm mb-2";

          const img = document.createElement('img');
          img.src = base64;
          img.alt = key;
          img.className = "w-40 h-auto mx-auto rounded shadow hover:scale-105 transition-transform cursor-pointer";
          img.onclick = () => window.open(base64, '_blank');

          div.appendChild(label);
          div.appendChild(img);
          contenedor.appendChild(div);
        }
      }
    }

    function marcarCorreccion() {
      localStorage.setItem('correccion_realizada', 'true');
      window.location.href = 'mockup7.php';
    }

    function validarYEnviar(event) {
      event.preventDefault();
      const btn = event.target;
      btn.disabled = true;

      for (const id of [...camposTexto, ...camposImagenes]) {
        const valor = localStorage.getItem(id);
        if (!valor || valor.trim() === '') {
          alert(`⚠️ Falta el campo o imagen: ${id.replace(/_/g, ' ')}`);
          btn.disabled = false;
          return;
        }
      }

      btn.textContent = "⏳ Enviando...";
      setTimeout(() => {
        document.getElementById('form-final').submit();
      }, 1500);
    }
/*
    window.onload = () => {
      localStorage.setItem('paso_petbio', 'mockup9');
      const faltantes = [...camposTexto, ...camposImagenes].filter(id => !localStorage.getItem(id));
      if (faltantes.length > 0) {
        alert("⚠️ Datos incompletos, redireccionando...");
        window.location.href = 'mockup7.php';
        return;
      }  */

        window.onload = () => {
  localStorage.setItem('paso_petbio', 'mockup9');

  // Cargar datos y mostrar imágenes SIEMPRE
  cargarDatosYForm();
  cargarImagenes();

  // Luego validamos si falta algo
  const faltantes = [...camposTexto, ...camposImagenes].filter(id => !localStorage.getItem(id));

  if (faltantes.length > 0) {
    console.warn("⚠️ Datos faltantes:", faltantes);
    alert(`⚠️ Hay datos faltantes:\n\n${faltantes.map(f => '• ' + f).join('\n')}\n\nPor favor, corrige los campos faltantes.`);
    document.getElementById("btn-enviar").disabled = true;
  }
};

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
