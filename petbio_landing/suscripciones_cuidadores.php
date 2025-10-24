<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: loginpetbio.php?error=no_sesion");
    exit;
}

$usuarioLogeado = $_SESSION['username'] ?? 'Invitado';
$apellidos      = $_SESSION['apellidos'] ?? '';
$idUsuario      = $_SESSION['id_usuario'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Suscripción Cuidadores - PETBIO</title>

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
    /* Inputs y selects ocupan 100% y fondo blanco */
    #subscription-screen input,
    #subscription-screen select,
    #subscription-screen textarea {
      color: black !important;
      background-color: white !important;
      width: 100% !important;
      max-width: 100%;
      box-sizing: border-box;
    }
  </style>
</head>
<body class="bg-petbiofondo font-bahn text-petbioazul min-h-screen flex flex-col">

  <!-- Saludo usuario y logout -->
  <div class="bg-gray-100 p-3 rounded-md mx-4 my-4 flex justify-between items-center max-w-lg mx-auto w-full">
    <div>
      <h2 class="text-lg font-semibold">Bienvenido, <?php echo htmlspecialchars($usuarioLogeado . ' ' . $apellidos); ?></h2>
      <p class="text-sm text-gray-700">ID de usuario: <?php echo htmlspecialchars($idUsuario); ?></p>
      <a href="logout.php" class="text-red-600 font-semibold hover:underline">Cerrar sesión</a>
    </div>
    <img src="imagenes_guias_2025/logo_petbio_transparente.png" alt="Logo PETBIO" class="h-14 ml-4" />
  </div>

  <!-- HEADER -->
  <header class="bg-gray-800 px-4 py-3 flex justify-between items-center shadow-md">
    <h1 class="text-lg font-bold text-white select-none">🐾 HUELLIT@S</h1>
    <button onclick="toggleMobileMenu()" class="md:hidden text-white text-2xl leading-none">☰</button>
    <nav class="hidden md:flex gap-6 text-white text-sm">
      <button onclick="window.location.href='identidad_rubm.php';" class="hover:text-yellow-400 transition">Inicio</button>
      <button onclick="window.location.href='identidad_rubm.php';" class="hover:text-yellow-400 transition">Registro</button>
      <button onclick="mostrarSuscripcion()" class="hover:text-yellow-400 transition">Suscripción</button>
    </nav>
  </header>

  <!-- MENÚ MÓVIL -->
  <div id="mobileMenu" class="md:hidden hidden bg-white px-4 py-4 space-y-3 shadow-lg rounded-b-2xl border-t border-gray-300 z-50 fixed top-16 left-0 right-0">
    <button onclick="showScreen('home-screen'); toggleMobileMenu();" class="block w-full text-left py-2 px-4 text-petbioazul bg-gray-100 rounded-lg hover:bg-petbioazulclaro hover:text-white transition">🏠 Inicio</button>
    <button onclick="window.location.href='identidad_rubm.php'; toggleMobileMenu();" class="block w-full text-left py-3 px-4 bg-yellow-400 text-black font-bold rounded-xl shadow-md hover:bg-yellow-500 hover:scale-105 transition transform duration-300 animate-bounce">📝 Registro de Mascotas</button>
    <button onclick="showScreen('search-screen'); toggleMobileMenu();" class="block w-full text-left py-2 px-4 text-petbioazul bg-gray-100 rounded-lg hover:bg-petbioazulclaro hover:text-white transition">🔍 Buscar Registro</button>
    <button onclick="showScreen('appointments-screen'); toggleMobileMenu();" class="block w-full text-left py-2 px-4 text-petbioazul bg-gray-100 rounded-lg hover:bg-petbioazulclaro hover:text-white transition">📅 Citas</button>
  </div>

  <!-- MENÚ INSTITUCIONAL -->
  <div class="relative mt-6 ml-4 max-w-lg mx-auto w-full">
    <button onclick="toggleDropdownMenu()" class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-petbioazulclaro text-white font-bold hover:bg-petbioverde">
      ☰ Menú institucional
    </button>
    <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-20">
      <a href="https://siac2025.com/" class="block px-4 py-2 text-sm text-petbioazul hover:bg-gray-100">🏠 Inicio</a>
      <a href="https://registro.siac2025.com/" class="block px-4 py-2 text-sm text-petbioazul hover:bg-gray-100">🔗 PETBIO – SIAC</a>
      <a href="https://registro.siac2025.com/2025/06/28/1041/" class="block px-4 py-2 text-sm text-petbioazul hover:bg-gray-100">🛡️ Política de Privacidad</a>
      <a href="https://registro.siac2025.com/2025/06/28/1039/" class="block px-4 py-2 text-sm text-petbioazul hover:bg-gray-100">⚖️ Términos y Condiciones</a>
      <a href="https://petbio11rubm2025.blogspot.com/" class="block px-4 py-2 text-sm text-petbioazul hover:bg-gray-100">📖 Blog PETBIO</a>
      <a href="#" onclick="mostrarSuscripcion(); toggleDropdownMenu();" class="block px-4 py-2 text-sm text-petbioazul hover:bg-gray-100">📝 Registro de Suscripción</a>
    </div>
  </div>

  <!-- FORMULARIO SUSCRIPCIÓN -->
  <section id="subscription-screen" class="p-6 max-w-lg md:max-w-2xl lg:max-w-4xl mx-auto bg-white rounded shadow mt-8 mb-12 w-full">

    <h1 class="text-2xl font-bold mb-6 text-gray-800">Formulario de Suscripción para Cuidadores</h1>

    <form id="form-suscripcion" method="POST" enctype="multipart/form-data" class="space-y-5">
      <input type="hidden" name="origen" value="<?php echo isset($_GET['origen']) && $_GET['origen'] === 'rubm' ? 'rubm' : 'suscripcion'; ?>">

      <div>
        <label for="nombres" class="block font-medium text-petbioazul mb-1">👤 Nombres:</label>
        <input type="text" name="nombres" id="nombres" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-petbioazulclaro" />
      </div>

      <div>
        <label for="apellidos" class="block font-medium text-petbioazul mb-1">🧾 Apellidos:</label>
        <input type="text" name="apellidos" id="apellidos" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-petbioazulclaro" />
      </div>

      <div>
        <label for="documento" class="block font-medium text-petbioazul mb-1">🆔 Documento:</label>
        <input type="text" name="documento" id="documento" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-petbioazulclaro" />
      </div>

      <div>
        <label for="telefono" class="block font-medium text-petbioazul mb-1">📞 Teléfono:</label>
        <input type="text" name="telefono" id="telefono" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-petbioazulclaro" />
      </div>

      <div>
        <label class="inline-flex items-center text-petbioazul font-medium mb-2">
          <input type="checkbox" name="whatsapp" class="form-checkbox text-green-500" />
          <span class="ml-2">¿Este número tiene WhatsApp?</span>
        </label>
      </div>

      <div>
        <label for="cantidad_exacta" class="block font-medium text-petbioazul mb-1">Cantidad exacta de mascotas:</label>
        <input type="number" name="cantidad_exacta" id="cantidad_exacta" min="1" max="500" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-petbioazulclaro" />
      </div>

      <div>
        <label for="cantidad" class="block font-medium text-petbioazul mb-1">🐾 Número de mascotas (opcional):</label>
        <select name="cantidad" id="cantidad" class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-100 text-gray-700 focus:outline-none focus:ring-2 focus:ring-petbioazulclaro">
          <option value="">Seleccione una opción</option>
          <option value="1">1-5</option>
          <option value="6">6-12</option>
          <option value="13">13-50</option>
          <option value="51">51-100</option>
          <option value="101">101-500</option>
        </select>
      </div>

      <div>
        <label for="periodo" class="block font-medium text-petbioazul mb-1">📅 Período de suscripción:</label>
        <select name="periodo" id="periodo" required class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-100 text-gray-700 focus:outline-none focus:ring-2 focus:ring-petbioazulclaro">
          <option value="">Seleccionar</option>
          <option value="trimestral">Trimestral</option>
          <option value="semestral">Semestral</option>
          <option value="anual">Anual</option>
        </select>
      </div>

      <div>
        <label for="total_pagado" class="block font-medium text-petbioazul mb-1">💰 Total a pagar:</label>
        <input type="text" name="total_pagado" id="total_pagado" readonly class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-100 text-gray-600" />
      </div>

      <div>
        <label for="evidencia_pago" class="block font-medium text-petbioazul mb-1">📎 Evidencia del pago (imagen):</label>
        <input type="file" name="evidencia_pago" id="evidencia_pago" accept="image/*" required class="w-full border border-gray-300 rounded px-3 py-2" />
      </div>

      <input type="hidden" name="cantidad_mascotas" id="cantidad_mascotas" />
      <input type="hidden" name="periodo_tarifa" id="periodo_tarifa" />

      <button type="submit" class="w-full bg-petbioazulclaro hover:bg-petbioverde text-white font-bold py-3 rounded transition-colors duration-300 text-lg">
        ✅ Enviar Suscripción
      </button>

      <div id="resultadoPrecio" class="mt-4 text-sm text-petbioazul font-semibold"></div>
    </form>
  </section>

  <div class="mb-4 p-4 bg-yellow-50 border border-yellow-300 rounded text-petbioazul font-semibold max-w-lg mx-auto w-full">
    <p>Por favor, realiza el pago de <strong id="mostrarTotalPago">$0</strong> a la siguiente cuenta:</p>

    <ul class="list-disc list-inside mt-2">
      <li>Nequi: 3043605255 (Nombre: Juan Osorno)</li>
      <li>Bancolombia: 002 - 000082 - 14 - Cuenta de ahorros (Nombre: Juan Osorno)</li>
    </ul>
    <p class="mt-2 italic text-sm">Luego sube la evidencia de pago en el formulario.</p>
  </div>

<script>
  // Tarifas por rango y periodo
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
  const mostrarTotalPago = document.getElementById('mostrarTotalPago');

  function calcularYMostrarPrecio() {
    const cantidad = parseInt(cantidadInput.value);
    const periodo = periodoSelect.value;

    if (!cantidad || !periodo) {
      resultadoPrecio.innerHTML = '';
      inputTotal.value = '';
      inputMascotas.value = '';
      inputPeriodo.value = '';
      mostrarTotalPago.textContent = `$0`;
      return;
    }

    const tarifa = tarifas.find(t => cantidad >= t.min && cantidad <= t.max);
    if (!tarifa) {
      resultadoPrecio.innerHTML = 'Cantidad fuera de rango (1-500)';
      inputTotal.value = '';
      inputMascotas.value = '';
      inputPeriodo.value = '';
      mostrarTotalPago.textContent = `$0`;
      return;
    }

    const precioUnitario = tarifa[periodo];
    const total = precioUnitario * cantidad;

    let mensaje = `
      💰 <strong>Precio por mascota:</strong> $${precioUnitario.toLocaleString()}<br>
      🧾 <strong>Total a pagar:</strong> $${total.toLocaleString()}
    `;

    if (periodo === 'semestral') {
      const ahorro = (tarifa.trimestral * cantidad * 2) - total;
      if (ahorro > 0) mensaje += `<br>🎁 Ahorro: $${ahorro.toLocaleString()}`;
    } else if (periodo === 'anual') {
      const ahorro = (tarifa.trimestral * cantidad * 4) - total;
      if (ahorro > 0) mensaje += `<br>🎁 Ahorro: $${ahorro.toLocaleString()}`;
    }

    resultadoPrecio.innerHTML = mensaje;
    inputMascotas.value = cantidad;
    inputPeriodo.value = periodo;
    inputTotal.value = total;

    mostrarTotalPago.textContent = `$${total.toLocaleString()}`;
  }

  cantidadInput.addEventListener('input', calcularYMostrarPrecio);
  periodoSelect.addEventListener('change', calcularYMostrarPrecio);

  document.getElementById('form-suscripcion').addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('guardar_suscripcion.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.text())
    .then(msg => {
      alert(msg);
      if (msg.includes("✅")) {
        this.reset();
        resultadoPrecio.innerHTML = '';
        inputTotal.value = '';
        mostrarTotalPago.textContent = `$0`;
      }
    })
    .catch(() => alert("❌ Error de conexión."));
  });

  // Funciones para menú responsive
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
</script>

</body>
</html>
