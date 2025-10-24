<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/conexion_mockups_bd_produccion.php');

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
  <meta charset="UTF-8">
  <title>Mockup 9 - Confirmación de Datos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#FFF8F0] min-h-screen flex flex-col items-center justify-center p-6">
  
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
  </div>

  <form id="form-final" action="identidad_petbio.php" method="POST" autocomplete="off">
    <input type="hidden" name="nombre" id="nombre">
    <input type="hidden" name="apellidos" id="apellidos">
    <input type="hidden" name="clase_mascota" id="clase_mascota">
    <input type="hidden" name="raza" id="raza">
    <input type="hidden" name="edad" id="edad">
    <input type="hidden" name="ciudad" id="ciudad">
    <input type="hidden" name="barrio" id="barrio">
    <input type="hidden" name="codigo_postal" id="codigo_postal">
    <input type="hidden" name="relacion" id="relacion">
    <input type="hidden" name="condicion_mascota" id="condicion_mascota">
    <input type="hidden" name="img_perfil" id="img_perfil">
    <input type="hidden" name="img_latiz" id="img_latiz">
    <input type="hidden" name="img_latdr" id="img_latdr">
    <input type="hidden" name="img_hf0" id="img_hf0">
    <input type="hidden" name="img_hf15" id="img_hf15">
    <input type="hidden" name="img_hf30" id="img_hf30">
    <input type="hidden" name="img_hfld15" id="img_hfld15">
    <input type="hidden" name="img_hfli15" id="img_hfli15">
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
      }, 2500);
    }

    window.onload = () => {
     
      localStorage.setItem('paso_petbio', 'mockup9'); // 👈 Agrega esta línea
    
      const faltantes = [...camposTexto, ...camposImagenes].filter(id => !localStorage.getItem(id));

      console.log("🧪 Verificando campos en localStorage:");
      [...camposTexto, ...camposImagenes].forEach(id => {
        console.log(id + ":", localStorage.getItem(id));
      });

      if (faltantes.length > 0) {
        alert("⚠️ Datos incompletos, redireccionando...");
        window.location.href = 'mockup7.php';
        return;
      }

      cargarDatosYForm();
      cargarImagenes();

      setTimeout(() => {
        const enviar = document.getElementById('btn-enviar');
        if (enviar && !enviar.disabled) {
          document.getElementById('form-final').submit();
        }
      }, 5000);
    };
  </script>
</body>
</html>
