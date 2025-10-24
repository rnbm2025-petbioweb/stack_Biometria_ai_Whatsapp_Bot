<?php
$host = 'mysql_petbio_secure';
$puerto = 3306;
$dbname = 'db_petbio_task';
$usuario = 'root';
$clave = 'R00t_Segura_2025!';

try {
  $pdo = new PDO("mysql:host=$host;port=$puerto;dbname=$dbname;charset=utf8", $usuario, $clave);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("❌ Error de conexión: " . $e->getMessage());
}

$filtro = $_GET['filtro'] ?? 'todas';

$sql = "SELECT * FROM tareas";
if ($filtro === 'alta') {
  $sql .= " WHERE prioridad='Alta'";
} elseif ($filtro === 'pendiente') {
  $sql .= " WHERE estado='Pendiente'";
}
$sql .= " ORDER BY fecha_limite ASC";

$stmt = $pdo->query($sql);
$tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Listado de Tareas PETBIO</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-petbiofondo text-petbioazul font-bahn">

<header class="bg-white shadow-md p-4 flex justify-between">
  <h1 class="text-2xl font-bold">🐾 PETBIO – Tareas</h1>
  <a href="tareas_petbio_2025.php" class="bg-petbioazulclaro text-white px-3 py-2 rounded">➕ Nueva Tarea</a>
</header>
<main class="max-w-4xl mx-auto mt-6 bg-white rounded-xl shadow-lg p-6">
  <h2 class="text-xl font-semibold mb-4">📋 Listado de Tareas</h2>

  <div class="mb-4">
    <a href="?filtro=todas" class="px-3 py-1 bg-gray-300 rounded">Todas</a>
    <a href="?filtro=alta" class="px-3 py-1 bg-red-400 text-white rounded">Alta</a>
    <a href="?filtro=pendiente" class="px-3 py-1 bg-yellow-400 text-white rounded">Pendientes</a>
  </div>

  <table class="w-full border">
    <thead>
      <tr class="bg-petbioazulclaro text-white">
        <th class="px-3 py-2">Título</th>
        <th class="px-3 py-2">Categoría</th>
        <th class="px-3 py-2">Prioridad</th>
        <th class="px-3 py-2">Estado</th>
        <th class="px-3 py-2">Fecha Límite</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($tareas as $t): ?>
      <tr class="border-b">
        <td class="px-3 py-2"><?= htmlspecialchars($t['titulo']) ?></td>
        <td class="px-3 py-2"><?= htmlspecialchars($t['categoria']) ?></td>
        <td class="px-3 py-2"><?= $t['prioridad'] ?></td>
        <td class="px-3 py-2"><?= $t['estado'] ?></td>
        <td class="px-3 py-2 fecha-limite" data-fecha="<?= $t['fecha_limite'] ?>">
          <?= $t['fecha_limite'] ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</main>

<script>
<?= file_get_contents("alertas.js") ?>
</script>

<script>
function revisarFechas() {
  const hoy = new Date();
  document.querySelectorAll(".fecha-limite").forEach(celda => {
    const fecha = new Date(celda.dataset.fecha);
    const dias = Math.floor((fecha - hoy) / (1000*60*60*24));
    
    if (dias < 0) {
      celda.innerHTML += " ⛔ <span class='text-red-600 font-bold'>(Vencida)</span>";
    } else if (dias <= 2) {
      celda.innerHTML += " ⚠️ <span class='text-orange-600'>(Quedan " + dias + " días)</span>";
    } else {
      celda.innerHTML += " ✅";
    }
  });
}
window.onload = revisarFechas;
</script>


</body>
</html>
