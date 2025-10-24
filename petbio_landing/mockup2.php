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
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Paso 3 - Ubicación y Condición</title>

  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    /* Colores y estilos personalizados de PETBIO */
    :root {
      --petbioverde: #0D4F48;
      --petbioturquesa: #00B3A6;
      --petbioazul: #114358;
      --petbiofondo: #FAF9F6;
    }
    header {
      background-color: var(--petbiofondo);
      border-bottom: 4px solid var(--petbioturquesa);
      box-shadow: 0 4px 12px rgba(0,0,0,0.12);
      border-radius: 0 0 1.5rem 1.5rem;
      position: sticky;
      top: 0;
      z-index: 50;
    }
    nav a {
      transition: color 0.3s ease;
    }
    nav a:hover {
      color: var(--petbioturquesa);
      text-decoration: underline;
      text-underline-offset: 4px;
    }
    #mobileMenu {
      background-color: var(--petbiofondo);
      border-top: 2px solid var(--petbioverde);
      border-radius: 0 0 1rem 1rem;
      color: var(--petbioazul);
      font-weight: 600;
    }
    #mobileMenu a:hover {
      color: var(--petbioturquesa);
      text-decoration: underline;
      text-underline-offset: 4px;
    }
    button:focus-visible {
      outline: 2px solid var(--petbioturquesa);
      outline-offset: 2px;
    }
  </style>
</head>

<body class="min-h-screen bg-gradient-to-b from-[#FFF8F0] to-[#E0F7FA] p-4">

<!-- HEADER -->
<header>
  <div class="max-w-screen-xl mx-auto px-6 py-4 flex justify-between items-center">

    <!-- LOGO IZQUIERDO -->
    <a href="/" class="flex items-center gap-3 text-2xl font-extrabold text-petbioverde font-bahn hover:text-petbioturquesa transition duration-300 select-none" aria-label="Inicio SIAC2025">
      🐾 SIAC2025
    </a>

    <!-- LOGO IMAGEN -->
    <a href="/" class="flex items-center space-x-3 hover:scale-105 transition-transform duration-300 select-none" aria-label="Logo SIAC2025">
      <img src="./imagenes_guias_2025/logo_petbio_transparente.png" alt="Logo SIAC2025" class="h-12 w-auto drop-shadow-lg" />
      <span class="font-extrabold text-petbioazul text-xl font-bahn tracking-wide drop-shadow-md select-none">SIAC 2025</span>
    </a>

    <!-- BOTON HAMBURGUESA MOVIL -->
    <button onclick="toggleMobileMenu()" class="md:hidden text-petbioverde focus:outline-none hover:text-petbioturquesa transition-colors duration-300" aria-label="Abrir menú móvil" aria-expanded="false" id="mobileMenuButton">
      <svg class="w-8 h-8 stroke-current" fill="none" stroke-width="3" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>

    <!-- MENU DESKTOP -->
    <nav class="hidden md:flex space-x-8 text-base font-semibold text-petbioazul font-bahn select-none" aria-label="Menú principal">
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

  <!-- MENU MOVIL -->
  <div id="mobileMenu" class="md:hidden hidden px-6 pb-6 bg-petbiofondo text-petbioazul font-bahn text-base font-semibold select-none border-t border-petbioverde rounded-b-xl" aria-label="Menú móvil" aria-hidden="true">
    <nav class="flex flex-col gap-4 pt-4">
      <a href="/" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Inicio</a>
      <a href="https://petbio.siac2025.com/registropetbio.php" target="_blank" rel="noopener noreferrer" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Registro PETBIO</a>
      <a href="https://registro.siac2025.com/" target="_blank" rel="noopener noreferrer" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">PETBIO</a>
      <a href="https://petbio.siac2025.com/loginpetbio.php" target="_blank" rel="noopener noreferrer" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Login PETBIO</a>
      <a href="https://petbio.siac2025.com/identidad_rubm.php" target="_blank" rel="noopener noreferrer" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Identidad RNBM</a>
      <a href="https://registro.siac2025.com/category/noticias-mas/" target="_blank" rel="noopener noreferrer" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Noticias</a>
      <a href="https://siac2025.com/contacto.html" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Contacto</a>
      <a href="https://petbio11rubm2025.blogspot.com/" target="_blank" rel="noopener noreferrer" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Blog PETBIO</a>
    </nav>
  </div>
</header>

<!-- CONTENIDO PRINCIPAL -->
<main class="max-w-3xl mx-auto mt-8 px-4">

  <div class="flex flex-col md:flex-row items-center justify-between mb-10 gap-6">
    <!-- Texto y botones izquierda -->
    <div class="flex-1 text-center md:text-left">
      <h1 class="text-2xl font-bold mb-4 select-none">
        🐾 Bienvenido a PETBIO - Registro Nacional Biométrico
      </h1>
      <div class="flex justify-center md:justify-start gap-4 mt-4">
        <button onclick="guardarPaso2()" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded-full shadow focus:outline-none focus:ring-2 focus:ring-green-500" aria-label="Continuar">
          ✅ Continuar
        </button>
        <button onclick="irASiac2025()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-full shadow focus:outline-none focus:ring-2 focus:ring-blue-500" aria-label="Ingresar a SIAC2025">
          🌐 Ingresar a SIAC2025
        </button>
      </div>
    </div>

    <!-- Imagen derecha -->
    <div class="flex-shrink-0">
      <img src="./imagenes_guias_2025/bienvenida_00011.png" alt="Bienvenida" class="w-64 rounded-xl shadow-lg select-none" />
    </div>
  </div>

  <section class="bg-white rounded-xl shadow-lg p-6 max-w-2xl mx-auto space-y-6">

    <h2 class="text-2xl font-bold text-petbioazul text-center select-none">📍 Paso 3: Ubicación y Condición</h2>

    <!-- Formulario de datos -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <input type="text" name="barrio" placeholder="🏘️ Barrio"
             class="bg-gray-100 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-petbioazul" required aria-label="Barrio" />
      <input type="text" name="codigo_postal" placeholder="📮 Código Postal"
             class="bg-gray-100 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-petbioazul" required aria-label="Código Postal" />

      <select name="condicion_mascota"
              class="bg-gray-100 rounded px-4 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-petbioazul" required aria-label="Condición de la mascota">
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
    <div class="bg-blue-100 text-blue-900 p-4 rounded-lg text-sm leading-relaxed whitespace-pre-line select-none" id="infoLegal"></div>

    <!-- Subida de imagen lateral izquierda -->
    <div class="text-center">
      <p class="text-sm text-gray-600 mb-2 select-none">📷 Foto del cuerpo lateral izquierdo de tu mascota</p>
      <img src="imagenes_guias_2025/tayson.jpeg" alt="Ejemplo lateral izquierdo" class="w-48 mx-auto rounded shadow border select-none" />
      <input type="file" name="img_latiz" id="img_latiz" accept="image/*" required class="mt-3 block w-full text-sm text-gray-700 border rounded p-2" aria-label="Foto lateral izquierdo de la mascota" />
      <div id="preview_latiz" class="mt-2"></div>
    </div>

    <!-- Navegación -->
    <div class="flex justify-between pt-4">
      <a href="mockup1.php" class="text-petbioazul hover:underline select-none" aria-label="Volver al paso anterior">⬅️ Atrás</a>
      <button onclick="guardarPaso2()" class="bg-petbioazul text-white px-6 py-2 rounded-full shadow hover:bg-[#0d3d4d] focus:outline-none focus:ring-2 focus:ring-petbioturquesa" aria-label="Siguiente paso">Siguiente ➡️</button>
    </div>

  </section>
</main>

<script>
  // Toggle menú móvil
  function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    const btn = document.getElementById('mobileMenuButton');
    const expanded = btn.getAttribute('aria-expanded') === 'true';
    if (expanded) {
      menu.classList.add('hidden');
      btn.setAttribute('aria-expanded', 'false');
      menu.setAttribute('aria-hidden', 'true');
    } else {
      menu.classList.remove('hidden');
      btn.setAttribute('aria-expanded', 'true');
      menu.setAttribute('aria-hidden', 'false');
    }
  }

  // Mostrar información legal
  function mostrarInfoCondicion() {
    const info = `🐾 En Colombia, la Ley 1774 de 2016 reconoce a los animales como seres sintientes.  
Por eso es fundamental registrar y proteger a tu mascota mediante el Registro Nacional Biométrico.  
Este registro ayuda a garantizar el bienestar animal y facilita su protección legal.  
Por favor, registra la condición actual de tu mascota con responsabilidad.`;
    document.getElementById('infoLegal').textContent = info;
  }

  // Guardar datos en localStorage y avanzar
  function guardarPaso2() {
    const barrio = document.querySelector('input[name="barrio"]').value.trim();
    const codigoPostal = document.querySelector('input[name="codigo_postal"]').value.trim();
    const condicion = document.querySelector('select[name="condicion_mascota"]').value;

    if (!barrio || !codigoPostal || !condicion) {
      alert("Por favor completa todos los campos.");
      return;
    }

    localStorage.setItem('barrio', barrio);
    localStorage.setItem('codigo_postal', codigoPostal);
    localStorage.setItem('condicion_mascota', condicion);

    // Manejo de imagen lateral izquierdo
    const imgLatiz = document.getElementById('img_latiz').files[0];
    if (!imgLatiz) {
      alert("Por favor sube la foto del cuerpo lateral izquierdo de tu mascota.");
      return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
      localStorage.setItem('img_latiz', e.target.result);
      // Avanzar a siguiente paso
      window.location.href = 'mockup3.php';
    };
    reader.readAsDataURL(imgLatiz);
  }

  // Mostrar preview de imagen cargada o guardada
  function mostrarPreview(inputId, previewId, savedKey = null) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    if (!input || !preview) return;

    if (savedKey) {
      const savedImg = localStorage.getItem(savedKey);
      if (savedImg) {
        preview.innerHTML = `<img src="${savedImg}" class="w-32 mx-auto mt-2 rounded shadow" alt="Imagen previa" />`;
      }
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

  // Ir a sitio SIAC2025
  function irASiac2025() {
    window.location.href = 'identidad_rubm.php?forzar_identidad=1';
  }

  // Al cargar la página
  document.addEventListener("DOMContentLoaded", () => {
    mostrarInfoCondicion();

    // Restaurar campos
    const barrio = localStorage.getItem('barrio');
    const codigoPostal = localStorage.getItem('codigo_postal');
    const condicion = localStorage.getItem('condicion_mascota');

    if (barrio) document.querySelector('input[name="barrio"]').value = barrio;
    if (codigoPostal) document.querySelector('input[name="codigo_postal"]').value = codigoPostal;
    if (condicion) document.querySelector('select[name="condicion_mascota"]').value = condicion;

    mostrarPreview('img_latiz', 'preview_latiz', 'img_latiz');

    // Guardar paso actual (opcional)
    localStorage.setItem('paso_petbio', 'mockup2');
  });
</script>
<footer role="contentinfo" aria-label="Navegación entre pasos" 
        style="position: fixed; bottom: 1rem; right: 1rem; z-index: 60; 
               background: white; box-shadow: 0 4px 10px rgba(0,0,0,0.15); 
               padding: 0.5rem 1rem; border-radius: 0.75rem; border: 1px solid #ccc; 
               max-width: 300px; transition: max-width 0.3s ease, padding 0.3s ease;"
        id="comboBoxContainer">
  <button onclick="toggleComboBox()" aria-expanded="true" aria-controls="mockupSelector" title="Mostrar/Ocultar menú" 
          style="position: absolute; top: -10px; right: -10px; background-color: #3B82F6; 
                 width: 24px; height: 24px; border-radius: 9999px; color: white; font-weight: bold; 
                 font-size: 1.2rem; line-height: 24px; border: none; box-shadow: 0 4px 6px rgba(59,130,246,0.4); 
                 display: flex; align-items: center; justify-content: center; cursor: pointer; 
                 transition: background-color 0.3s ease;">
    <span id="toggleIcon">−</span>
  </button>

  <label for="mockupSelector" class="text-sm font-semibold select-none">Ir a paso:</label>
  <select id="mockupSelector" class="ml-2 border border-gray-400 p-1 rounded" onchange="irAMockup(this.value)" aria-label="Selector de paso">
    <option value="">Seleccionar</option>
    <option value="mockup0.php">Paso 1 – Nombre mascota / Foto de Perfil</option>
    <option value="mockup1.php">Paso 2 – Clase / Raza / Edad / Imagen Cuerpo lado lateral derecha</option>
    <option value="mockup2.php" selected>Paso 3 – Ubicación y Condición / Imagen Cuerpo lado lateral izquierda</option>
    <option value="mockup3.php">Paso 4 – Imagen frontal lateral derecha 10° a 15°</option>
    <option value="mockup4.php">Paso 5 – Imagen frontal lateral izquierda 10° a 15°</option>
    <option value="mockup5.php">Paso 6 – Imagen nariz 15°</option>
    <option value="mockup6.php">Paso 7 – Imagen nariz 30°</option>
    <option value="mockup7.php">Paso 8 – Imagen frontal nariz 0°</option>
    <option value="mockup9.php">Paso 9 – Confirmación</option>
  </select>
</footer>

<script>
  // Alternar combo box minimizar / maximizar
  function toggleComboBox() {
    const container = document.getElementById('comboBoxContainer');
    const icon = document.getElementById('toggleIcon');
    container.classList.toggle('minimizado');
    if(container.classList.contains('minimizado')) {
      icon.textContent = '+';
      // Ajustar estilos para minimizar
      container.style.maxWidth = '44px';
      container.style.padding = '0.5rem 0.5rem';
      container.style.cursor = 'pointer';
      // Ocultar select y label
      container.querySelector('select').style.display = 'none';
      container.querySelector('label').style.display = 'none';
    } else {
      icon.textContent = '−';
      // Restaurar estilos
      container.style.maxWidth = '300px';
      container.style.padding = '0.5rem 1rem';
      container.style.cursor = 'default';
      // Mostrar select y label
      container.querySelector('select').style.display = '';
      container.querySelector('label').style.display = '';
    }
  }

  // Navegar a mockup seleccionado
  function irAMockup(url) {
    if(url) window.location.href = url;
  }
</script>


</body>
</html>
