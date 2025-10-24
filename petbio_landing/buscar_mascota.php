<?php
// ⚙️ Conexión segura
$host = "mysql_petbio_secure";
$usuario = "root";
$clave = 'R00t_Segura_2025!';
$dbname = "db__produccion_petbio_segura_2025";
$port = 3306;

// Crear la conexión
$conn = new mysqli($host, $usuario, $clave, $dbname, $port);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Recibir el número de documento desde el formulario
$numero_documento = $_POST['numero_documento'] ?? '';

if (!empty($numero_documento)) {
    // Consulta SQL para buscar la mascota
    $sql = "SELECT * FROM registro_mascotas WHERE RIGHT(numero_documento, 6) = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error en la preparación de la consulta: " . $conn->error);
    }

    $stmt->bind_param("s", $numero_documento);
    $stmt->execute();
    $result = $stmt->get_result();

    // Mostrar los resultados
    if ($result->num_rows > 0) {
        echo "<h1>Información de la Mascota</h1>";
        while ($row = $result->fetch_assoc()) {
            echo "<p><strong>Nombre:</strong> " . htmlspecialchars($row['nombre']) . "</p>";
            echo "<p><strong>Raza:</strong> " . htmlspecialchars($row['raza']) . "</p>";
            echo "<p><strong>Edad:</strong> " . htmlspecialchars($row['edad']) . "</p>";
            echo "<p><strong>Relación:</strong> " . htmlspecialchars($row['relacion']) . "</p>";
            echo "<p><strong>Ciudad y Barrio:</strong> " . htmlspecialchars($row['ciudad_y_barrio']) . "</p>";
            echo "<p><strong>Clase de Mascota:</strong> " . htmlspecialchars($row['clase_mascota']) . "</p>";
            echo "<p><strong>Condición:</strong> " . htmlspecialchars($row['condicion_mascota']) . "</p>";

            // Construir URLs absolutas para las imágenes
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $base_url = $protocol . "://" . $host;

            if (!empty($row['huella_nasal_ruta'])) {
                $huella_url = $base_url . $row['huella_nasal_ruta'];
                echo '<p><strong>Huella Nasal:</strong><br><img src="' . $huella_url . '" alt="Huella Nasal" style="max-width:200px;"></p>';
            }

            if (!empty($row['imagen_frontal_ruta'])) {
                $frontal_url = $base_url . $row['imagen_frontal_ruta'];
                echo '<p><strong>Imagen Frontal:</strong><br><img src="' . $frontal_url . '" alt="Imagen Frontal" style="max-width:200px;"></p>';
            }
        }
    } else {
        echo "<p>⚠️ No se encontró ninguna mascota con ese número de documento.</p>";
    }

    $stmt->close();
} else {
    echo "<p>⚠️ Por favor, ingrese un número de documento válido.</p>";
}

$conn->close();
?>
