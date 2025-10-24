<?php
date_default_timezone_set("America/Bogota"); // Ajusta según tu zona horaria

try {
    $pdo = new PDO(
        "mysql:host=mysql_petbio_secure;dbname=db_petbio_task;charset=utf8",
        "root",
        "R00t_Segura_2025!"
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}

$hoy = date("Y-m-d");

$sql = "SELECT * FROM tareas 
        WHERE fecha_limite IS NOT NULL 
        AND fecha_limite <= DATE_ADD(:hoy, INTERVAL 2 DAY)
        AND estado != 'Completada'";
$stmt = $pdo->prepare($sql);
$stmt->execute([':hoy' => $hoy]);
$tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$tareas) {
    echo "✅ No hay tareas próximas a vencer ($hoy)\n";
    exit;
}

foreach ($tareas as $t) {
    $mensaje = "⚠️ Recordatorio: La tarea '{$t['titulo']}' vence el {$t['fecha_limite']}";
    echo $mensaje . "\n";
}
