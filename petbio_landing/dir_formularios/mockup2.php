<?php
session_start();


require_once 'sesion_global.php';

if (!isset($_SESSION['id_usuario'])) {
  echo "<script>
    alert('⚠️ Debes iniciar sesión primero en PETBIO.');
    window.location.href = 'https://petbio.siac2025.com/loginpetbio.php';
  </script>";
  exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Paso 3 - Ubicación y Condición</title>
  <script src="https://cdn.tailwindcss.com"></script>

  <script>
    function mostrarInfoCondicion() {
      const info = `🐾 En Colombia, la Ley 1774 de 2016 reconoce a los animales como seres sintientes, no cosas.
⚖️ El maltrato animal es penalizado, y existen normas que garantizan su protección.
🌎 A nivel mundial, la Declaración Universal de los Derechos del Animal (UNESCO, 1978) establece principios de respeto y bienestar animal.`;
      document.getElementById('infoLegal').textContent = info;
    }

    function guardarPaso2() {
      const barrio = document.querySelector('input[name="barrio"]').value.trim();
      const codigoPostal = document.querySelector('input[name="codigo_postal"]').value.trim();
      const condicion = document.querySelector('select[name="condicion_mascota"]').value;
      const imgLatiz = document.getElementById('img_latiz').files[0];

      if (!barrio || !codigoPostal || !condicion) {
        alert("Por favor completa todos los campos.");
        return;
      }

      if (!imgLatiz) {
        alert("Por favor sube la foto del cuerpo lateral izquierdo de tu mascota.");
        return;
      }

      localStorage.setItem('barrio', barrio);
      localStorage.setItem('codigo_postal', codigoPostal);
      localStorage.setItem('condicion_mascota', condicion);

      if (imgLatiz && imgLatiz.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
          localStorage.setItem('img_latiz', e.target.result);
          window.location.href = 'mockup3.php';
        };
        reader.readAsDataURL(imgLatiz);
      } else {
        alert("⚠️ El archivo seleccionado no es una imagen válida.");
      }
    }

    function mostrarPreview(inputId, previewId, savedKey = null) {
      const input = document.getElementById(inputId);
      const preview = document.getElementById(previewId);
      if (!input || !preview) return;

      const savedImg = savedKey ? localStorage.getItem(savedKey) : null;
      if (savedImg) {
        preview.innerHTML = `<img src="${savedImg}" class="w-32 mx-auto mt-2 rounded shadow border" alt="Imagen previa" />`;
      }

      input.addEventListener('change', () => {
        if (input.files && input.files[0]) {
          const reader = new FileReader();
          reader.onload = e => {
            preview.innerHTML = `<img src="${e.target.result}" class="w-32 mx-auto mt-2 rounded shadow border" alt="Preview imagen"/>`;
          };
          reader.readAsDataURL(input.files[0]);
        }
      });
    }

    document.addEventListener("DOMContentLoaded", () => {
      mostrarInfoCondicion();

      const barrio = localStorage.getItem('barrio');
      const codigo = localStorage.getItem('codigo_postal');
      const condicion = localStorage.getItem('condicion_mascota');

      if (barrio) document.querySelector('input[name="barrio"]').value = barrio;
      if (codigo) document.querySelector('input[name="codigo_postal"]').value = codigo;
      if (condicion) document.querySelector('select[name="condicion_mascota"]').value = condicion;

      mostrarPreview('img_latiz', 'preview_latiz', 'img_latiz');
    });
  </script>
</head>

<!-- <body class="min-h-screen bg-gradient-to-b from-[#FFF8F0] to-[#E0F7FA] flex items-center justify-center p-4"> -->

 <body class="min-h-screen bg-gradient-to-b from-[#FFF8F0] to-[#E0F7FA] p-4">
 

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

<script>
  function irASiac2025() {
    window.location.href = 'identidad_rubm.php?forzar_identidad=1';
  }
</script>

    </div>
  </div>

  <!-- Imagen a la derecha -->
  <div class="flex-shrink-0">
    <img src="./imagenes_guias_2025/bienvenida_00011.png" alt="Bienvenida" class="w-64 rounded-xl shadow-lg">
  </div>
</div>


  <div class="bg-white rounded-xl shadow-lg p-6 max-w-2xl w-full space-y-6">
    <h2 class="text-2xl font-bold text-[#114358] text-center">📍 Paso 3: Ubicación y Condición</h2>

    <!-- Formulario de datos -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <input type="text" name="barrio" placeholder="🏘️ Barrio"
             class="bg-gray-100 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#114358]" required>
      <input type="text" name="codigo_postal" placeholder="📮 Código Postal"
             class="bg-gray-100 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#114358]" required>

      <select name="condicion_mascota"
              class="bg-gray-100 rounded px-4 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#114358]" required>
        <option value="">🐶 Condición de la mascota</option>
        <option value="hogar_familiar">Hogar Familiar</option>
        <option value="hogar_veterinario">Hogar Veterinario</option>
        <option value="hogar_de_paso">Hogar de Paso</option>
        <option value="centro_regulador">Centro Regulador</option>
        <option value="condicion_abandono">Condición de Abandono</option>
        <option value="Otros">Otros</option>
      </select>
    </div>

    <!-- Información legal -->
    <div class="bg-blue-100 text-blue-900 p-4 rounded-lg text-sm leading-relaxed whitespace-pre-line" id="infoLegal"></div>

    <!-- Subida de imagen lateral izquierda -->
    <div class="text-center">
      <p class="text-sm text-gray-600 mb-2">📷 Foto del cuerpo lateral izquierdo de tu mascota</p>
      <img src="imagenes_guias_2025/tayson.jpeg" alt="Ejemplo lateral izquierdo" class="w-48 mx-auto rounded shadow border">
      <input type="file" name="img_latiz" id="img_latiz" accept="image/*" required
             class="mt-3 block w-full text-sm text-gray-700 border rounded p-2">
      <div id="preview_latiz" class="mt-2"></div>
    </div>

    <!-- Navegación -->
    <div class="flex justify-between pt-4">
      <a href="mockup1.php" class="text-[#114358] hover:underline">⬅️ Atrás</a>
      <button onclick="guardarPaso2()"
              class="bg-[#114358] text-white px-6 py-2 rounded-full shadow hover:bg-[#0d3d4d]">
        Siguiente ➡️
      </button>
    </div>
  </div>

  <script>
    window.addEventListener('DOMContentLoaded', () => {
      localStorage.setItem('paso_petbio', 'mockup2');
    });
  </script>

</body>
</html>
