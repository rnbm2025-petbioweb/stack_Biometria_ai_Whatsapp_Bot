<?php

require_once("no_existe.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();



require_once 'sesion_global.php';

if (!isset($_SESSION['id_usuario'])) {
  header('Location: https://petbio.siac2025.com/loginpetbio.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Bienvenido a PETBIO</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
  </style>
</head>
<body class="bg-[#FFF8F0] min-h-screen flex flex-col justify-center items-center p-6">

  <div class="flex flex-col items-center justify-center mb-10">
    <h1 class="text-2xl font-bold text-center mb-6">🐾 Bienvenido a PETBIO - Registro Nacional Biométrico</h1>
    <img src="./imagenes_guias_2025/bienvenida_00011.png" alt="Bienvenida" class="w-64 rounded-xl shadow-lg mb-8">

    <div class="flex gap-4">
      <button onclick="validarYContinuar()" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded-full shadow">
        ✅ Continuar
      </button>
     <!-- <button onclick="irASiac2025()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-full shadow">
        🌐 Ingresar al menu principal
      </button>   --> 

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

  <div class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-2xl p-8 max-w-2xl text-center">
    <h1 class="text-4xl font-bold text-[#114358] mb-4">🐾 Bienvenido a PETBIO</h1>
    <p class="text-lg text-gray-700 mb-6 text-justify">
      Queridos visitantes, es un placer presentarles el MVP SIAC2025 - PETBIO: un proyecto de vida, para la vida y el respeto por los animales.
    </p>

    <form id="form-paso0" class="space-y-6">
      <div>
        <label class="block text-left font-semibold text-[#114358]">¿Cómo se llama tu mascota?</label>
        <input name="nombre" id="nombre" type="text" required placeholder="Ej: Max, Pelusa..." class="w-full rounded-full px-4 py-2 border border-gray-300" />
      </div>

      <div>
        <label class="block text-left font-semibold text-[#114358]">¿Cuál será el apellido de tu mascota?</label>
        <input name="apellidos" id="apellidos" type="text" required placeholder="Ej: Osorno" class="w-full rounded-full px-4 py-2 border border-gray-300" />
      </div>

      <div>
        <label class="block text-left font-semibold text-[#114358]">Clase de Mascota</label>
        <select name="clase_mascota" id="clase_mascota" onchange="mostrarInfoClase(this.value)" required class="w-full bg-gray-200 rounded-full px-4 py-2">
          <option value="">Selecciona una opción</option>
          <option value="Caninos">Caninos</option>
          <option value="Felinos">Felinos</option>
          <option value="Equinos">Equinos</option>
          <option value="Porcinos">Porcinos</option>
          <option value="Ganado">Ganado</option>
          <option value="Otros Mamiferos">Otros Mamíferos</option>
        </select>
      </div>

      <div id="info-clase" class="bg-yellow-100 rounded-xl p-4 shadow-md hidden text-justify"></div>

      <div>
        <label class="block text-left font-semibold text-[#114358]">📸 Foto de perfil de la mascota</label>
        <input type="file" accept="image/*" id="img_perfil" required class="w-full rounded-full px-4 py-2 border border-gray-300 bg-white">
        <div id="preview_perfil" class="mt-2"></div>
      </div>

      <div class="flex justify-between pt-4">
        <button type="button" onclick="validarYContinuar()" class="bg-[#114358] hover:bg-[#0d3d4d] text-white px-6 py-2 rounded-full">
          Siguiente ➡️
        </button>
      </div>

      <div class="mt-8 text-center">
        <p class="text-sm text-gray-700 mb-2">Guía visual para capturar la imagen de perfil:</p>
        <div class="inline-block border border-gray-300 rounded-lg shadow-md p-2 bg-white">
          <img src="./imagenes_guias_2025/nieves.jpeg" alt="Guía imagen perfil" class="w-64 h-auto rounded" />
        </div>
      </div>
    </form>
  </div>

  <script>
    function mostrarInfoClase(clase) {
      const info = {
        "Caninos": "Los caninos son leales, fieles y grandes compañeros.",
        "Felinos": "Los felinos son independientes y ágiles.",
        "Equinos": "Los equinos son nobles y fuertes.",
        "Porcinos": "Los porcinos son inteligentes y sociales.",
        "Ganado": "El ganado es fundamental en la vida rural.",
        "Otros Mamiferos": "Incluye conejos, erizos y otros mamíferos domésticos."
      };
      const div = document.getElementById("info-clase");
      if (info[clase]) {
        div.innerText = info[clase];
        div.classList.remove("hidden");
      } else {
        div.innerText = "";
        div.classList.add("hidden");
      }
    }

    function validarYContinuar() {
      const nombre = document.getElementById('nombre').value.trim();
      const apellidos = document.getElementById('apellidos').value.trim();
      const clase = document.getElementById('clase_mascota').value;
      const imgPerfil = document.getElementById('img_perfil').files[0];

      if (!nombre || !apellidos || !clase || !imgPerfil) {
        alert("Completa todos los campos y sube una imagen.");
        return;
      }

      const reader = new FileReader();
      reader.onload = function(e) {
        localStorage.setItem('nombre', nombre);
        localStorage.setItem('apellidos', apellidos);
        localStorage.setItem('clase_mascota', clase);
        localStorage.setItem('img_perfil', e.target.result);
        localStorage.setItem('paso_petbio', 'mockup0');
        window.location.href = 'mockup1.php';
      };
      reader.readAsDataURL(imgPerfil);
    }

    function irASiac2025() {
      localStorage.setItem('ingreso_validado', 'false');
      window.location.href = 'identidad_rubm.php';
    }

    document.addEventListener("DOMContentLoaded", () => {
      const input = document.getElementById('img_perfil');
      const preview = document.getElementById('preview_perfil');
      const savedImg = localStorage.getItem('img_perfil');

      if (savedImg) {
        preview.innerHTML = `<img src="${savedImg}" alt="Foto previa" class="w-40 h-auto rounded shadow mt-2 mx-auto">`;
      }

      input.addEventListener('change', function (event) {
        const file = event.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function (e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Nueva foto" class="w-40 h-auto rounded shadow mt-2 mx-auto">`;
            localStorage.setItem('img_perfil', e.target.result);
          };
          reader.readAsDataURL(file);
        }
      });
    });
  </script>

</body>
</html>
