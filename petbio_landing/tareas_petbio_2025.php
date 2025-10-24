<?php
session_start();

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $titulo = trim($_POST['titulo'] ?? '');
  $descripcion = trim($_POST['descripcion'] ?? '');
  $categoria = $_POST['categoria'] ?? '';
  $prioridad = $_POST['prioridad'] ?? 'Media';
  $fecha_limite = $_POST['fecha_limite'] ?? null;

  if (!$titulo || !$categoria) {
    echo "<script>alert('⚠️ Título y categoría son obligatorios.'); window.history.back();</script>";
    exit;
  }

  try {
    $stmt = $pdo->prepare("INSERT INTO tareas (titulo, descripcion, categoria, prioridad, fecha_limite) 
                           VALUES (:titulo, :descripcion, :categoria, :prioridad, :fecha_limite)");
    $stmt->execute([
      ':titulo' => $titulo,
      ':descripcion' => $descripcion,
      ':categoria' => $categoria,
      ':prioridad' => $prioridad,
      ':fecha_limite' => $fecha_limite
    ]);

    echo "<script>alert('✅ Tarea registrada con éxito'); window.location.href='tareas_petbio_2025.php';</script>";
    exit;
  } catch (PDOException $e) {
    echo "<script>alert('❌ Error al guardar la tarea: " . addslashes($e->getMessage()) . "');</script>";
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Tareas PETBIO 2025</title>
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
</head>
<body class="bg-petbiofondo text-petbioazul font-bahn">

<header class="bg-white shadow-md p-4 flex justify-between items-center">
  <h1 class="text-2xl font-bold text-petbioazul">🐾 PETBIO – Gestión de Tareas</h1>
</header>


<main class="max-w-lg mx-auto mt-10 bg-white rounded-xl shadow-lg p-6">
  <h2 class="text-2xl font-semibold mb-6 text-center">Registrar Nueva Tarea</h2>
  <form method="POST" class="space-y-4">
    <div>
      <label for="titulo" class="block font-medium">Título de la tarea:</label>
      <input type="text" id="titulo" name="titulo" required class="w-full rounded border px-4 py-2">
    </div>
    <div>
      <label for="descripcion" class="block font-medium">Descripción:</label>
      <textarea id="descripcion" name="descripcion" class="w-full rounded border px-4 py-2"></textarea>
    </div>
    <div>
      <label for="categoria" class="block font-medium">Categoría:</label>
      <select id="categoria" name="categoria" required class="w-full rounded border px-4 py-2">
        <option value="">-- Selecciona --</option>
        <option value="Base de Datos">Base de Datos</option>
        <option value="VSCode">VSCode</option>
        <option value="Redes">Redes</option>
        <option value="Seguridad">Seguridad</option>
        <option value="Tramitología">Tramitología</option>
        <option value="Formativa">Formativa</option>
        <option value="Diseño">Diseño</option>
        <option value="Publicitaria">Publicitaria</option>
        <option value="Depuración">Depuración</option>
        <option value="Documentación">Documentación</option>
        <option value="Testing">Testing</option>
        <option value="Deploy">Deploy</option>
      </select>
    </div>
    <div>
      <label for="prioridad" class="block font-medium">Prioridad:</label>
      <select id="prioridad" name="prioridad" class="w-full rounded border px-4 py-2">
        <option value="Alta">🔴 Alta</option>
        <option value="Media" selected>🟡 Media</option>
        <option value="Baja">🟢 Baja</option>
      </select>
    </div>
    <div>
      <label for="fecha_limite" class="block font-medium">Fecha Límite:</label>
      <input type="date" id="fecha_limite" name="fecha_limite" class="w-full rounded border px-4 py-2">
    </div>
    <button type="submit" class="w-full bg-petbioazulclaro hover:bg-petbioverde text-white font-bold py-2 px-4 rounded">Guardar Tarea</button>
  </form>

<div class="mt-4 text-center">
  <a href="https://petbio.siac2025.com/listar_tareas.php" 
     class="inline-block bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
    📋 Ir al Listado de Tareas
  </a>
</div>

</main>

</body>
</html>
