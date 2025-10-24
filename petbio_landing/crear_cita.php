<?php
session_start();


//require_once('sesion_global.php');
//require_once 'config/conexion_petbio_nueva.php';
require_once '../dir_config/sesion_global.php';



if (!isset($_SESSION['id_usuario'])) {
    die("Error: Sesión no iniciada.");
}

$host = "mysql_petbio_secure";
$usuario = "root";
$clave = 'R00t_Segura_2025!';
$dbname = "db__produccion_petbio_segura_2025";
$puerto = 3306;

$conn = new mysqli($host, $usuario, $clave, $dbname, $puerto);
if ($conn->connect_error) die("Conexión fallida: " . $conn->connect_error);

$id_usuario = $_SESSION['id_usuario'];

// Obtener mascotas del usuario
$mascotas = [];
$sql = "SELECT id, nombre, clase_mascota, raza, edad, ciudad, barrio FROM registro_mascotas WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $mascotas[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Crear Cita Veterinaria</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#f5f5f5] min-h-screen flex flex-col items-center p-6">
  <div class="bg-white w-full max-w-3xl shadow-lg rounded-xl p-6">
    <h2 class="text-2xl font-bold text-[#114358] mb-6">📅 Crear Cita Veterinaria</h2>

    <?php if (empty($mascotas)) : ?>
      <div class="text-red-600">⚠️ No tienes mascotas registradas. Registra una mascota primero.</div>
    <?php else : ?>
    <form action="guardar_cita.php" method="POST" enctype="multipart/form-data" class="space-y-6">
      <!-- Selección de mascota -->
      <div>
        <label class="block mb-2 text-sm font-medium">🐾 Mascota</label>
        <select name="id_mascota" required class="w-full border rounded p-2">
          <option value="">Selecciona una mascota</option>
          <?php foreach ($mascotas as $m): ?>
            <option value="<?= $m['id'] ?>">
              <?= $m['nombre'] ?> - <?= $m['clase_mascota'] ?> - <?= $m['raza'] ?> (<?= $m['edad'] ?> años)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Datos del aliado -->
      <div>
        <label class="block mb-2 text-sm font-medium">🏥 Nombre del Aliado</label>
        <input type="text" name="aliado_nombre" required class="w-full border p-2 rounded" />
      </div>

      <div>
        <label class="block mb-2 text-sm font-medium">🏷️ Tipo de Aliado</label>
        <select name="aliado_tipo" required class="w-full border p-2 rounded">
          <option value="">Selecciona tipo</option>
          <option value="Veterinaria">Veterinaria</option>
          <option value="Fundación">Fundación</option>
          <option value="Brigada">Brigada</option>
        </select>
      </div>

      <!-- Fecha y hora -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block mb-2 text-sm font-medium">📆 Fecha de Cita</label>
          <input type="date" name="fecha_cita" required class="w-full border p-2 rounded" />
        </div>
        <div>
          <label class="block mb-2 text-sm font-medium">⏰ Hora</label>
          <input type="time" name="hora_cita" required class="w-full border p-2 rounded" />
        </div>
      </div>

      <!-- Modalidad -->
      <div>
        <label class="block mb-2 text-sm font-medium">💡 Modalidad</label>
        <select name="modalidad" required class="w-full border p-2 rounded">
          <option value="">Selecciona</option>
          <option value="Presencial">Presencial</option>
          <option value="Virtual">Virtual</option>
          <option value="Domicilio">Domicilio</option>
        </select>
      </div>

      <!-- Motivo -->
      <div>
        <label class="block mb-2 text-sm font-medium">✏️ Motivo</label>
        <textarea name="motivo" rows="3" required class="w-full border p-2 rounded" placeholder="Motivo de la cita..."></textarea>
      </div>

      <!-- Observaciones -->
      <div>
        <label class="block mb-2 text-sm font-medium">📋 Observaciones (opcional)</label>
        <textarea name="observaciones" rows="2" class="w-full border p-2 rounded"></textarea>
      </div>

      <!-- Cargar evidencia -->
      <div>
        <label class="block mb-2 text-sm font-medium">📎 Adjuntar evidencia PDF (opcional)</label>
        <input type="file" name="evidencia_pdf" accept="application/pdf" class="w-full border p-2 rounded" />
      </div>

      <!-- Botón -->
      <div class="flex justify-end">
        <button type="submit" class="bg-[#114358] text-white px-6 py-2 rounded hover:bg-[#0f384d]">💾 Agendar Cita</button>
      </div>
    </form>
    <?php endif; ?>
  </div>
</body>
</html>
