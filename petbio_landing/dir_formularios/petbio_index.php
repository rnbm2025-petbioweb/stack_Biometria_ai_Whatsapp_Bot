<?php
#session_start();
require_once(__DIR__ . '/../dir_config/sesion_global.php');
require_once(__DIR__ . '/../dir_config/conexion_petbio_nueva.php');
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Bienvenida a PETBIO</title>
<header class="bg-petbiofondo shadow-lg sticky top-0 z-50 border-4 border-petbioturquesa rounded-b-3xl">
  <div class="max-w-screen-xl mx-auto px-6 py-4 flex justify-between items-center">

    <!-- LOGO LADO IZQUIERDO -->
    <a href="/" class="flex items-center gap-3 text-2xl font-extrabold text-petbioverde font-bahn hover:text-petbioturquesa transition duration-300 select-none">
      🐾 SIAC2025
    </a>

    <!-- LOGO IMAGEN -->
    <a href="/" class="flex items-center space-x-3 hover:scale-105 transition-transform duration-300 select-none">
      <img src="./imagenes_guias_2025/logo_petbio_transparente.png" alt="Logo SIAC2025" class="h-12 w-auto drop-shadow-lg">
      <span class="font-extrabold text-petbioazul text-xl font-bahn tracking-wide drop-shadow-md select-none">SIAC 2025</span>
    </a>

    <!-- BOTÓN HAMBURGUESA (solo en móvil) -->
    <button onclick="toggleMobileMenu()" class="md:hidden text-petbioverde focus:outline-none hover:text-petbioturquesa transition-colors duration-300" aria-label="Abrir menú móvil">
      <svg class="w-8 h-8 stroke-current" fill="none" stroke-width="3" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
        <path d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>

    <!-- MENÚ DESKTOP -->
    <nav class="hidden md:flex space-x-8 text-base font-semibold text-petbioazul font-bahn select-none">
      <a href="/" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Inicio</a>
      <a href="https://petbio.siac2025.com/registropetbio.php" target="_blank" rel="noopener noreferrer" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Registro PETBIO</a>
      <a href="https://registro.siac2025.com/" target="_blank" rel="noopener noreferrer" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">PETBIO</a>
      <a href="https://petbio.siac2025.com/loginpetbio.php" target="_blank" rel="noopener noreferrer" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Login PETBIO</a>
      <a href="https://petbio.siac2025.com/identidad_rubm.php" target="_blank" rel="noopener noreferrer" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Identidad RNBM</a>
      <a href="https://registro.siac2025.com/category/noticias-mas/" target="_blank" rel="noopener noreferrer" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Noticias</a>
      <a href="/contacto.html" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Contacto</a>
      <a href="https://petbio11rubm2025.blogspot.com/" target="_blank" rel="noopener noreferrer" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Blog PETBIO</a>
    </nav>

  </div>

  <!-- MENÚ MÓVIL -->
  <div id="mobileMenu" class="md:hidden hidden px-6 pb-6 bg-petbiofondo text-petbioazul font-bahn text-base font-semibold select-none border-t border-petbioverde rounded-b-xl">
    <nav class="flex flex-col gap-4 pt-4">
      <a href="/" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Inicio</a>
      <a href="https://petbio.siac2025.com/registropetbio.php" target="_blank" rel="noopener noreferrer" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Registro PETBIO</a>
      <a href="https://registro.siac2025.com/" target="_blank" rel="noopener noreferrer" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">PETBIO</a>
      <a href="https://petbio.siac2025.com/loginpetbio.php" target="_blank" rel="noopener noreferrer" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Login PETBIO</a>
      <a href="https://petbio.siac2025.com/identidad_rubm.php" target="_blank" rel="noopener noreferrer" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Identidad RNBM</a>
      <a href="https://registro.siac2025.com/category/noticias-mas/" target="_blank" rel="noopener noreferrer" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Noticias</a>
      <a href="/contacto.html" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Contacto</a>
      <a href="https://petbio11rubm2025.blogspot.com/" target="_blank" rel="noopener noreferrer" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Blog PETBIO</a>
    </nav>
  </div>
</header>

<script>
  function toggleMobileMenu() {
    const menu = document.getElementById("mobileMenu");
    menu.classList.toggle("hidden");
  }
</script>

<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#FFF8F0] flex flex-col items-center justify-center min-h-screen text-center p-6">

  <div class="bg-white p-8 rounded-2xl shadow-2xl max-w-xl">
    <img src="imagenes_guias_2025/bienvenida_00011.png" alt="Logo PETBIO" class="w-40 mx-auto mb-6">

    <h1 class="text-3xl font-bold text-[#114358] mb-4">🐾 Bienvenido a PETBIO</h1>
    <p class="text-lg text-gray-700 mb-6" style="text-align:center;">
      Este es un entorno seguro para el <strong>Registro Nacional Biométrico de Mascotas</strong>. 
    </p>

    <div class="flex justify-center gap-4">
      <a href="mockup0.php" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-full">
        Iniciar Registro
      </a>
      <a href="https://petbio.siac2025.com/identidad_rubm.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-full">
        Ir a SIAC2025
      </a>
    </div>

    <section style="background-color:#fff8e1;padding:30px;border-radius:12px;text-align:center;">
      <h2 style="color:#e65100;">📸 Guía para Captura Biométrica de la Trufa (Nariz) de tu Mascota</h2>
      <ul style="font-size:18px;color:#333;line-height:1.6;display:inline-block;text-align:left;">
        <li>✔️ La mascota debe estar <strong>dormida o muy tranquila</strong>.</li>
        <li>✔️ Luz blanca, preferiblemente LED de mínimo 9W. Evita sombras.</li>
        <li>✔️ Fondo blanco o claro.</li>
        <li>✔️ Captura <strong>3 fotos de la trufa (nariz)</strong>:
          <ul>
            <li>🔹 Frontal (0°) a una distancia de 10 a 17 cm.</li>
            <li>🔹 Inclinada a 45°, cuidando que tu sombra no interfiera.</li>
            <li>🔹 Vista lateral (90°), con la misma distancia y luz.</li>
          </ul>
        </li>
        <li>✔️ Además, toma <strong>4 fotos del rostro</strong>: perfil derecho, perfil izquierdo, vista superior y vista inferior.</li>
        <li>✔️ Si es posible, realiza la sesión en el día o en un espacio bien iluminado.</li>
        <li>✔️ Las imágenes deben ser nítidas, enfocadas y claras.</li>
      </ul>
    </section>
    

    <!-- VIDEO YOUTUBE EMBEBIDO -->
<div class="mt-8">
  <h2 class="text-xl font-bold text-[#114358] mb-4">🎥 Video Explicativo</h2>
  <div class="relative w-full pb-[56.25%] h-0">
    <iframe class="absolute top-0 left-0 w-full h-full rounded-xl shadow-lg"
      src="https://www.youtube.com/embed/MDBaoeLTN2w?si=oxgbEEJrWuU3P915" 
      title="YouTube video player"
      frameborder="0" 
      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
      referrerpolicy="strict-origin-when-cross-origin" 
      allowfullscreen>
    </iframe>
  </div>
</div>

    
    </div>

  </div>

  <footer class="mt-6 text-sm text-gray-500">© 2025 PETBIO | SIAC2025.com</footer>
</body>
</html>
