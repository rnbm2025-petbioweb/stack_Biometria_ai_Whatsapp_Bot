<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    die("Error: Sesión no iniciada.");
}
require_once(__DIR__ . '/../dir_config/sesion_global.php');

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Historia Clínica - PETBIO</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#f5f5f5] min-h-screen flex flex-col items-center p-6">
  <div class="bg-white w-full max-w-3xl shadow-lg rounded-xl p-6">
    <h2 class="text-2xl font-bold text-[#114358] mb-4">🩺 Historia Clínica de la Mascota</h2>

    <?php
    $conn = new mysqli("mysql_petbio_secure", "root", 'R00t_Segura_2025!', "db__produccion_petbio_segura_2025", 3306);
    if ($conn->connect_error) die("Conexión fallida: " . $conn->connect_error);

    $id_usuario = $_SESSION['id_usuario'];

    // Obtener mascotas
    $mascotas = [];
    $stmt = $conn->prepare("SELECT id, nombre, apellidos, edad, raza, clase_mascota, ciudad, barrio FROM registro_mascotas WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
      $mascotas[] = $row;
    }
    $stmt->close();

    // Obtener condiciones clínicas
    $condiciones = $conn->query("SELECT id_condicion, descripcion FROM condiciones_mascota");
    ?>

    <!-- COMBO de mascotas -->
    <div class="mb-4">
      <label for="select-mascota" class="block mb-2 text-sm font-medium">🐾 Selecciona la Mascota</label>
      <select id="select-mascota" name="id_mascota" class="w-full border border-gray-300 p-2 rounded" required>
        <option value="">-- Seleccionar --</option>
        <?php foreach ($mascotas as $m): ?>
          <option value="<?= $m['id'] ?>" data-info='<?= json_encode($m) ?>'>
            <?= htmlspecialchars($m['nombre'] . " " . $m['apellidos'] . " - " . $m['clase_mascota']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Info dinámica de mascota seleccionada -->
    <div id="mascota-info" class="mb-6 text-gray-700 hidden">
      <div><strong>👤 Nombre:</strong> <span id="nombre"></span> <span id="apellidos"></span></div>
      <div><strong>🎂 Edad:</strong> <span id="edad"></span> años</div>
      <div><strong>🐶 Raza:</strong> <span id="raza"></span></div>
      <div><strong>🐾 Clase:</strong> <span id="clase"></span></div>
      <div><strong>📍 Ciudad/Barrio:</strong> <span id="ciudad"></span> - <span id="barrio"></span></div>
    </div>

    <!-- FORMULARIO -->
    <form action="guardar_historia_clinica.php" method="POST" enctype="multipart/form-data" class="space-y-6 mt-6">
      <!-- Este input se actualizará dinámicamente -->
      <input type="hidden" id="id_mascota" name="id_mascota" value="">

      <!-- Condición -->
      <div>
        <label class="block mb-2 text-sm font-medium">⚕️ Condición Clínica</label>
        <select name="id_condicion" class="w-full border border-gray-300 p-2 rounded" required>
          <option value="">Seleccionar condición</option>
          <?php while ($row = $condiciones->fetch_assoc()): ?>
            <option value="<?= $row['id_condicion'] ?>"><?= htmlspecialchars($row['descripcion']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- Campos médicos -->
      <div><label class="block mb-2">📝 Motivo</label><textarea name="motivo_consulta" class="w-full border rounded p-2" required></textarea></div>
      <div><label class="block mb-2">🧪 Diagnóstico</label><textarea name="diagnostico" class="w-full border rounded p-2" required></textarea></div>
      <div><label class="block mb-2">💊 Tratamiento</label><textarea name="tratamiento" class="w-full border rounded p-2"></textarea></div>
      <div><label class="block mb-2">📌 Observaciones</label><textarea name="observaciones" class="w-full border rounded p-2"></textarea></div>

      <!-- Archivos -->
      <div><label class="block mb-2">📄 PDFs</label><input type="file" name="pdfs[]" accept="application/pdf" multiple class="w-full border p-2 rounded"></div>
      <div><label class="block mb-2">📷 Imágenes</label><input type="file" name="imagenes[]" accept="image/*" multiple capture="environment" class="w-full border p-2 rounded"></div>

      <!-- Botones -->
      <div class="flex justify-between">
        <button type="submit" class="bg-[#114358] text-white px-6 py-2 rounded hover:bg-[#0f384d]">💾 Guardar Historia</button>
        <a href="crear_cita.php" class="bg-[#FFB200] text-white px-6 py-2 rounded hover:bg-[#ffaa00]">📅 Crear Cita</a>
      </div>
    </form>

    <?php $conn->close(); ?>
  </div>

  <!-- SCRIPT para mostrar info al seleccionar mascota -->
  <script>
    document.getElementById('select-mascota').addEventListener('change', function() {
      const selected = this.options[this.selectedIndex];
      const data = selected.getAttribute('data-info');
      if (data) {
        const info = JSON.parse(data);
        document.getElementById('nombre').textContent = info.nombre;
        document.getElementById('apellidos').textContent = info.apellidos;
        document.getElementById('edad').textContent = info.edad;
        document.getElementById('raza').textContent = info.raza;
        document.getElementById('clase').textContent = info.clase_mascota;
        document.getElementById('ciudad').textContent = info.ciudad;
        document.getElementById('barrio').textContent = info.barrio;
        document.getElementById('mascota-info').classList.remove('hidden');
        document.getElementById('id_mascota').value = info.id;
      } else {
        document.getElementById('mascota-info').classList.add('hidden');
        document.getElementById('id_mascota').value = '';
      }
    });
  </script>
</body>
</html>
