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
  <title>Paso 2 - Datos Básicos</title>

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
    /* Utilidades personalizadas (para clases usadas en el HTML) */
    .text-petbioverde { color: var(--petbioverde); }
    .text-petbioazul { color: var(--petbioazul); }
    .text-petbioturquesa { color: var(--petbioturquesa); }
    .bg-petbiofondo { background-color: var(--petbiofondo); }
    .bg-petbioazul { background-color: var(--petbioazul); }
    .bg-petbioturquesa { background-color: var(--petbioturquesa); }
    .border-petbioverde { border-color: var(--petbioverde); }

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
    /* Combo box fixed (si decides usarlo más adelante) */
    #comboBoxContainer {
      position: fixed;
      top: 1rem;
      right: 1rem;
      z-index: 60;
      background: white;
      box-shadow: 0 4px 10px rgba(0,0,0,0.15);
      padding: 0.5rem 1rem 0.5rem 1rem;
      border-radius: 0.75rem;
      border: 1px solid #ccc;
      max-width: 300px;
      transition: max-width 0.3s ease, padding 0.3s ease;
    }
    #comboBoxContainer.minimizado {
      max-width: 44px;
      padding: 0.5rem 0.5rem;
      cursor: pointer;
    }
    #comboBoxContainer.minimizado select,
    #comboBoxContainer.minimizado label {
      display: none;
    }
    #comboBoxContainer button {
      position: absolute;
      top: -10px;
      right: -10px;
      background-color: #3B82F6;
      width: 24px;
      height: 24px;
      border-radius: 9999px;
      color: white;
      font-weight: bold;
      font-size: 1.2rem;
      line-height: 24px;
      border: none;
      box-shadow: 0 4px 6px rgba(59,130,246,0.4);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    #comboBoxContainer button:hover {
      background-color: #2563EB;
    }
  </style>
</head>

<body class="min-h-screen bg-gradient-to-b from-[#FFF8F0] to-[#E0F7FA] p-4">

<!-- HEADER -->
<header>
  <div class="max-w-screen-xl mx-auto px-6 py-4 flex justify-between items-center">

    <!-- LOGO IZQUIERDO -->
    <a href="/" class="flex items-center gap-3 text-2xl font-extrabold text-petbioverde hover:text-petbioturquesa transition duration-300 select-none" aria-label="Inicio SIAC2025">
      🐾 SIAC2025
    </a>

    <!-- LOGO IMAGEN -->
    <a href="/" class="flex items-center space-x-3 hover:scale-105 transition-transform duration-300 select-none" aria-label="Logo SIAC2025">
      <img src="./imagenes_guias_2025/logo_petbio_transparente.png" alt="Logo SIAC2025" class="h-12 w-auto drop-shadow-lg" />
      <span class="font-extrabold text-petbioazul text-xl tracking-wide drop-shadow-md select-none">SIAC 2025</span>
    </a>

    <!-- BOTON HAMBURGUESA MOVIL -->
    <button onclick="toggleMobileMenu()" class="md:hidden text-petbioverde focus:outline-none hover:text-petbioturquesa transition-colors duration-300" aria-label="Abrir menú móvil" aria-expanded="false" id="mobileMenuButton">
      <svg class="w-8 h-8 stroke-current" fill="none" stroke-width="3" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>

    <!-- MENU DESKTOP -->
    <nav class="hidden md:flex space-x-8 text-base font-semibold text-petbioazul select-none" aria-label="Menú principal">
      <a href="/" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Inicio</a>
      <a href="https://petbio.siac2025.com/registropetbio.php" target="_blank" rel="noopener noreferrer" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Registro PETBIO</a>
      <a href="https://registro.siac2025.com/" target="_blank" rel="noopener noreferrer" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">PETBIO</a>
      <a href="https://petbio.siac2025.com/loginpetbio.php" target="_blank" rel="noopener noreferrer" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Login PETBIO</a>
      <a href="https://petbio.siac2025.com/identidad_rubm.php" target="_blank" rel="noopener noreferrer" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Identidad RNBM</a>
      <a href="https://siac2025.com/contacto.html" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Contacto</a>
      <a href="https://petbio11rubm2025.blogspot.com/" target="_blank" rel="noopener noreferrer" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Blog PETBIO</a>
    </nav>

  </div>

  <!-- MENU MOVIL -->
  <div id="mobileMenu" class="md:hidden hidden px-6 pb-6 bg-petbiofondo text-petbioazul text-base font-semibold select-none border-t border-petbioverde rounded-b-xl" aria-label="Menú móvil" aria-hidden="true">
    <nav class="flex flex-col gap-4 pt-4">
      <a href="/" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Inicio</a>
      <a href="https://petbio.siac2025.com/registropetbio.php" target="_blank" rel="noopener noreferrer" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Registro PETBIO</a>
      <a href="https://registro.siac2025.com/" target="_blank" rel="noopener noreferrer" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">PETBIO</a>
      <a href="https://petbio.siac2025.com/loginpetbio.php" target="_blank" rel="noopener noreferrer" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Login PETBIO</a>
      <a href="https://petbio.siac2025.com/identidad_rubm.php" target="_blank" rel="noopener noreferrer" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Identidad RNBM</a>
      <a href="/contacto.html" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Contacto</a>
      <a href="https://petbio11rubm2025.blogspot.com/" target="_blank" rel="noopener noreferrer" class="hover:text-petbioturquesa hover:underline underline-offset-4 transition">Blog PETBIO</a>
    </nav>
  </div>
</header>

<!-- CONTENIDO PRINCIPAL -->
<main class="max-w-3xl mx-auto mt-8 px-4">

  <div class="flex flex-col md:flex-row items-center justify-between mb-10 gap-6">
    <div class="flex-1 text-center md:text-left">
      <h1 class="text-2xl font-bold mb-4 select-none">🐾 Bienvenido a PETBIO - Registro Nacional Biométrico</h1>
      <div class="flex justify-center md:justify-start gap-4 mt-4">
        <button onclick="guardarPaso1()" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded-full shadow focus:outline-none focus:ring-2 focus:ring-green-500">
          ✅ Continuar
        </button>
        <button onclick="irASiac2025()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-full shadow focus:outline-none focus:ring-2 focus:ring-blue-500">
          🌐 Ingresar a SIAC2025
        </button>
      </div>
    </div>
    <div class="flex-shrink-0">
      <img src="./imagenes_guias_2025/bienvenida_00011.png" alt="Bienvenida" class="w-64 rounded-xl shadow-lg select-none" />
    </div>
  </div>

  <section class="bg-white rounded-xl shadow-lg p-6 space-y-6">
    <h2 class="text-2xl font-bold text-petbioazul text-center select-none">Paso 2: Datos básicos de tu mascota</h2>

    <div id="infoRaza" class="bg-yellow-100 text-yellow-800 p-4 rounded-lg text-justify whitespace-pre-line select-none"></div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <input type="text" placeholder="Raza" name="raza" class="bg-gray-100 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-petbioazul" required aria-label="Raza de la mascota" />
      <input type="number" placeholder="Edad (en años)" name="edad" min="0" max="40" class="bg-gray-100 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-petbioazul" required aria-label="Edad de la mascota en años" />
      <input type="text" placeholder="Ciudad" name="ciudad" class="bg-gray-100 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-petbioazul" required aria-label="Ciudad" />
      <select name="relacion" class="bg-gray-100 rounded px-4 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-petbioazul" required aria-label="Relación con la mascota">
        <option value="">Relación con la mascota</option>
        <option value="Amo">Cuidador Amo</option>
        <option value="Acompañante">Cuidador Acompañante</option>
        <option value="Médico Veterinario">Médico Veterinario</option>
        <option value="Fundacion Canina">Fundación Canina</option>
        <option value="Independientes">Independientes</option>
      </select>
    </div>

    <div class="text-center">
      <p class="text-sm text-gray-600 mb-2 select-none">📷 Foto del cuerpo; lateral derecho de tu mascota</p>
      <img src="imagenes_guias_2025/american_bulldog_43 copy.jpg" alt="Ejemplo lateral derecho" class="w-48 mx-auto rounded shadow select-none" />
      <input 
        type="file" 
        name="img_latdr" 
        id="img_latdr" 
        accept="image/jpeg,image/png,image/webp,image/jpg" 
        required 
        class="mt-2 block w-full text-sm text-gray-600" 
        aria-label="Foto lateral derecho de la mascota" 
      />
      <div id="preview_latdr" class="mt-2"></div>
    </div>

    <div class="flex justify-between mt-6">
      <a href="mockup0.php" class="text-petbioazul hover:underline select-none" aria-label="Volver al paso anterior">⬅️ Atrás</a>
      <button onclick="guardarPaso1()" class="bg-petbioazul text-white px-6 py-2 rounded-full shadow hover:bg-[#0d3d4d] focus:outline-none focus:ring-2 focus:ring-petbioturquesa" aria-label="Guardar y continuar">
        Siguiente ➡️
      </button>
    </div>
  </section>
</main>

<!-- FOOTER -->
<footer class="fixed bottom-0 left-0 w-full bg-gray-100 border-t border-gray-400 p-3 flex flex-col sm:flex-row items-center justify-center sm:space-x-3 z-50" role="contentinfo">
  <label for="mockupSelectorFooter" class="text-sm font-semibold select-none mb-1 sm:mb-0">Ir a paso:</label>
  <select id="mockupSelectorFooter" class="border border-gray-400 p-2 rounded w-full sm:w-auto" onchange="irAMockup(this.value)" aria-label="Selector de paso">
    <option value="">Seleccionar</option>
    <option value="mockup0.php">Paso 1 – Nombre mascota / Foto de Perfil</option>
    <option value="mockup1.php" selected>Paso 2 – Clase / Raza / Edad / Imagen Cuerpo lado lateral derecha</option>
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
  let navegando = false;

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

  // Mostrar info según clase mascota guardada (del paso anterior)
  function mostrarInfoRaza() {
    const datos = JSON.parse(localStorage.getItem('datosMascota') || '{}');
    const clase = datos.clase || localStorage.getItem('clase_mascota');
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

  // Guardar datos y avanzar a Paso 3 (mockup2.php)
  function guardarPaso1() {
    if (navegando) return;

    const raza = document.querySelector('input[name="raza"]').value.trim();
    const edad = document.querySelector('input[name="edad"]').value.trim();
    const ciudad = document.querySelector('input[name="ciudad"]').value.trim();
    const relacion = document.querySelector('select[name="relacion"]').value;
    const imgLatdrInput = document.getElementById('img_latdr');
    const imgLatdr = imgLatdrInput && imgLatdrInput.files ? imgLatdrInput.files[0] : null;

    if (!raza || !edad || !ciudad || !relacion) {
      alert("Por favor completa todos los campos.");
      return;
    }

    const edadNum = parseInt(edad, 10);
    if (!Number.isFinite(edadNum) || edadNum < 0 || edadNum > 40) {
      alert("La edad debe ser un número entre 0 y 40.");
      return;
    }

    if (!imgLatdr) {
      alert("Por favor sube la foto del cuerpo lateral derecho de tu mascota.");
      return;
    }

    const tiposPermitidos = ["image/jpeg", "image/png", "image/webp", "image/jpg"];
    if (!tiposPermitidos.includes(imgLatdr.type)) {
      alert("Formato de imagen no permitido. Usa JPG, PNG o WEBP.");
      return;
    }
    if (imgLatdr.size > 8 * 1024 * 1024) {
      alert("La imagen no puede superar 8MB.");
      return;
    }

    // Guardar en localStorage (compatibilidad con claves sueltas y objeto consolidado)
    localStorage.setItem('raza', raza);
    localStorage.setItem('edad', String(edadNum));
    localStorage.setItem('ciudad', ciudad);
    localStorage.setItem('relacion', relacion);

    const datosMascota = JSON.parse(localStorage.getItem('datosMascota') || '{}');
    datosMascota.raza = raza;
    datosMascota.edad = edadNum;
    datosMascota.ciudad = ciudad;
    datosMascota.relacion = relacion;
    localStorage.setItem('datosMascota', JSON.stringify(datosMascota));

    navegando = true;
    const reader = new FileReader();
    reader.onload = function(e) {
      localStorage.setItem('img_latdr', e.target.result);
      window.location.href = 'mockup2.php'; // ➡️ Paso 3
    };
    reader.onerror = function() {
      navegando = false;
      alert("No se pudo leer la imagen. Intenta nuevamente.");
    };
    reader.readAsDataURL(imgLatdr);
  }

  // Alias por consistencia
  function validarYContinuar() { guardarPaso1(); }

  // Mostrar previsualización de imagen guardada o cargada
  function mostrarPreview(inputId, previewId, savedKey = null) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    if (!input || !preview) return;

    // Mostrar imagen guardada previamente
    if (savedKey) {
      const savedImg = localStorage.getItem(savedKey);
      if (savedImg) {
        preview.innerHTML = `<img src="${savedImg}" class="w-32 mx-auto mt-2 rounded shadow" alt="Imagen previa" />`;
      }
    }

    // Mostrar imagen al seleccionar archivo nuevo
    input.addEventListener('change', () => {
      if (input.files && input.files[0]) {
        const file = input.files[0];
        const tiposPermitidos = ["image/jpeg", "image/png", "image/webp", "image/jpg"];
        if (!tiposPermitidos.includes(file.type)) {
          alert("Formato de imagen no permitido. Usa JPG, PNG o WEBP.");
          input.value = "";
          return;
        }
        if (file.size > 8 * 1024 * 1024) {
          alert("La imagen no puede superar 8MB.");
          input.value = "";
          return;
        }
        const reader = new FileReader();
        reader.onload = e => {
          preview.innerHTML = `<img src="${e.target.result}" class="w-32 mx-auto mt-2 rounded shadow" alt="Preview imagen"/>`;
        };
        reader.readAsDataURL(file);
      }
    });
  }

  // Redirigir a sitio SIAC2025
  function irASiac2025() {
    window.location.href = 'identidad_rubm.php?forzar_identidad=1';
  }

  // Navegar a mockup seleccionado en footer
  function irAMockup(url) {
    if (url) window.location.href = url;
  }

  // Alternar combo box minimizar / maximizar (si llegas a usar #comboBoxContainer)
  function toggleComboBox() {
    const container = document.getElementById('comboBoxContainer');
    const icon = document.getElementById('toggleIcon');
    if (!container || !icon) return;
    container.classList.toggle('minimizado');
    icon.textContent = container.classList.contains('minimizado') ? '+' : '−';
  }

  // Al cargar la página
  document.addEventListener("DOMContentLoaded", () => {
    mostrarInfoRaza();

    // Restaurar campos guardados
    const raza = localStorage.getItem('raza');
    const edad = localStorage.getItem('edad');
    const ciudad = localStorage.getItem('ciudad');
    const relacion = localStorage.getItem('relacion');

    if (raza) document.querySelector('input[name="raza"]').value = raza;
    if (edad) document.querySelector('input[name="edad"]').value = edad;
    if (ciudad) document.querySelector('input[name="ciudad"]').value = ciudad;
    if (relacion) document.querySelector('select[name="relacion"]').value = relacion;

    mostrarPreview('img_latdr', 'preview_latdr', 'img_latdr');

    // Guardar paso actual en localStorage (opcional)
    localStorage.setItem('paso_petbio', 'mockup1');
  });
</script>

</body>
</html>
