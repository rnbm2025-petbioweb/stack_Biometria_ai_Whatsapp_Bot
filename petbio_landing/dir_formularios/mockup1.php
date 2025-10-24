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
  <title>Paso 2 - Datos Básicos</title>
  <script src="https://cdn.tailwindcss.com"></script>

  <script>
    function mostrarInfoRaza() {
      const clase = localStorage.getItem('clase_mascota');
      const info = {
        'Caninos': "🐶 Los caninos son mamíferos domesticados, excelentes compañeros, con una gran variedad de razas que reflejan su diversidad.",
        'Felinos': "🐱 Los felinos son independientes y curiosos. Su agilidad y sentido del territorio los hace especiales como mascotas.",
        'Equinos': "🐴 Los equinos requieren espacio y cuidado específico. Son animales nobles y usados comúnmente en el campo.",
        'Porcinos': "🐷 Algunos porcinos son adoptados como mascotas. Son inteligentes y sociales.",
        'Ganado': "🐄 El ganado incluye bovinos y otros rumiantes. Generalmente viven en entornos rurales o productivos.",
        'Otros Mamiferos': "🦝 Incluye roedores, primates u otros. Cada uno tiene características únicas."
      };
      document.getElementById('infoRaza').textContent = info[clase] || '';
    }

    function guardarPaso1() {
      const raza = document.querySelector('input[name="raza"]').value.trim();
      const edad = document.querySelector('input[name="edad"]').value.trim();
      const ciudad = document.querySelector('input[name="ciudad"]').value.trim();
      const relacion = document.querySelector('select[name="relacion"]').value;
      const imgLatdr = document.getElementById('img_latdr').files[0];

      if (!raza || !edad || !ciudad || !relacion) {
        alert("Por favor completa todos los campos.");
        return;
      }

      if (!imgLatdr) {
        alert("Por favor sube la foto del cuerpo lateral derecho de tu mascota.");
        return;
      }

      localStorage.setItem('raza', raza);
      localStorage.setItem('edad', edad);
      localStorage.setItem('ciudad', ciudad);
      localStorage.setItem('relacion', relacion);

      const reader = new FileReader();
      reader.onload = function(e) {
        localStorage.setItem('img_latdr', e.target.result);
        window.location.href = 'mockup2.php';
      };
      reader.readAsDataURL(imgLatdr);
    }

    function mostrarPreview(inputId, previewId, savedKey = null) {
      const input = document.getElementById(inputId);
      const preview = document.getElementById(previewId);
      if (!input || !preview) return;

      const savedImg = savedKey ? localStorage.getItem(savedKey) : null;
      if (savedImg) {
        preview.innerHTML = `<img src="${savedImg}" class="w-32 mx-auto mt-2 rounded shadow" alt="Imagen previa" />`;
      }

      input.addEventListener('change', () => {
        if (input.files && input.files[0]) {
          const reader = new FileReader();
          reader.onload = e => {
            preview.innerHTML = `<img src="${e.target.result}" class="w-32 mx-auto mt-2 rounded shadow" alt="Preview imagen"/>`;
          };
          reader.readAsDataURL(input.files[0]);
        }
      });
    }

    document.addEventListener("DOMContentLoaded", () => {
      mostrarInfoRaza();

      // Restaurar campos del paso 1
      const raza = localStorage.getItem('raza');
      const edad = localStorage.getItem('edad');
      const ciudad = localStorage.getItem('ciudad');
      const relacion = localStorage.getItem('relacion');

      if (raza) document.querySelector('input[name="raza"]').value = raza;
      if (edad) document.querySelector('input[name="edad"]').value = edad;
      if (ciudad) document.querySelector('input[name="ciudad"]').value = ciudad;
      if (relacion) document.querySelector('select[name="relacion"]').value = relacion;

      mostrarPreview('img_latdr', 'preview_latdr', 'img_latdr');
    });
  </script>
</head>

<!-- <body class="min-h-screen bg-gradient-to-b from-[#FFF8F0] to-[#E0F7FA] flex items-center justify-center p-4"> -->
 <body class="min-h-screen bg-gradient-to-b from-[#FFF8F0] to-[#E0F7FA] p-4">
 

<div class="flex flex-col md:flex-row items-center justify-between mb-10 gap-6 px-4">
  <!-- Texto y botones a la izquierda -->
  <div class="flex-1 text-center md:text-left">
    <h1 class="text-2xl font-bold mb-4">
      🐾 Bienvenido a PETBIO "OJO dentro de dir" - Registro Nacional Biométrico
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
    <h2 class="text-2xl font-bold text-[#114358] text-center">Paso 2: Datos básicos de tu mascota</h2>

    <div id="infoRaza" class="bg-yellow-100 text-yellow-800 p-4 rounded-lg text-justify whitespace-pre-line"></div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <input type="text" placeholder="Raza" name="raza" class="bg-gray-100 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#114358]" required>
      <input type="number" placeholder="Edad (en años)" name="edad" min="0" class="bg-gray-100 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#114358]" required>
      <input type="text" placeholder="Ciudad" name="ciudad" class="bg-gray-100 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#114358]" required>

      <select name="relacion" class="bg-gray-100 rounded px-4 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#114358]" required>
        <option value="">Relación con la mascota</option>
        <option value="Amo">Cuidador Amo</option>
        <option value="Acompañante">Cuidador Acompañante</option>
        <option value="Médico Veterinario">Médico Veterinario</option>
        <option value="Fundacion Canina">Fundación Canina</option>
        <option value="Independientes">Independientes</option>
      </select>
    </div>

    <div class="text-center">
      <p class="text-sm text-gray-600 mb-2">📷 Foto del cuerpo; lateral derecho de tu mascota</p>
      <img src="imagenes_guias_2025/Abyssinian_156.jpg" alt="Ejemplo lateral derecho" class="w-48 mx-auto rounded shadow">
      <input type="file" name="img_latdr" id="img_latdr" accept="image/*" required class="mt-2 block w-full text-sm text-gray-600">
      <div id="preview_latdr" class="mt-2"></div>
    </div>

    <div class="flex justify-between">
      <a href="mockup0.php" class="text-[#114358] hover:underline">⬅️ Atrás</a>
      <button onclick="guardarPaso1()" class="bg-[#114358] text-white px-6 py-2 rounded-full shadow hover:bg-[#0d3d4d]">Siguiente ➡️</button>
    </div>
  </div>

  <script>
    window.addEventListener('DOMContentLoaded', () => {
      localStorage.setItem('paso_petbio', 'mockup1');
    });
  </script>

</body>
</html>
