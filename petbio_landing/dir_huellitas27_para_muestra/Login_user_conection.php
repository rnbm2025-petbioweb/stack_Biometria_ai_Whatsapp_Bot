<?php
session_start(); // Iniciar sesión

require_once 'config/conexion_petbio_nueva.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ✅ Datos de conexión al contenedor MySQL seguro
    $host     = "mysql_petbio_secure";
    $puerto   = 3310;
    $dbname   = "db_produccion_petbio_segura_2025";
    $usuario  = "petbio11_root";
    $clave    = "P%tbio11_Root_mysql_vm8916$";

    // 🔌 Conexión a la base de datos
    $conn = new mysqli($host, $usuario, $clave, $dbname, $puerto);
    if ($conn->connect_error) {
        die("❌ Error de conexión: " . $conn->connect_error);
    }

    // 📥 Captura y validación básica de datos
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "⚠ Por favor ingresa tu correo y contraseña.";
        header("Location: loginpetbio.php");
        exit();
    }

    // 🔐 Verificación del usuario
    $stmt = $conn->prepare("SELECT id_usuario, username, apellidos_usuario, password FROM registro_usuario WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id_usuario, $username, $apellidos, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            // ✅ Login correcto
            $_SESSION['id_usuario'] = $id_usuario;
            $_SESSION['nombre'] = $username;
            $_SESSION['apellidos'] = $apellidos;

            header("Location: identidad_rubm.php"); // Cambia aquí si quieres ir a otra pantalla
            exit();
        } else {
            $_SESSION['login_error'] = "⚠ Contraseña incorrecta.";
        }
    } else {
        $_SESSION['login_error'] = "⚠ Email no registrado.";
    }

    $stmt->close();
    $conn->close();

    header("Location: loginpetbio.php");
    exit();
}
?>

<!-- HTML del formulario -->
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Iniciar Sesión - HUELLIT@S - PETBIO</title>
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
<body class="bg-petbiofondo text-petbioazul font-bahn flex items-center justify-center min-h-screen">
  <div class="bg-white shadow-lg rounded-xl p-8 w-full max-w-md">
    <h2 class="text-2xl font-bold mb-6 text-center">🐾 Iniciar Sesión - PETBIO</h2>

    <?php if (isset($_SESSION['login_error'])): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?= $_SESSION['login_error']; unset($_SESSION['login_error']); ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="loginpetbio.php" class="space-y-4">
      <div>
        <label for="email" class="block font-medium">📧 Correo Electrónico:</label>
        <input type="email" id="email" name="email" required class="w-full border border-gray-300 rounded px-4 py-2">
      </div>
      <div>
        <label for="password" class="block font-medium">🔑 Contraseña:</label>
        <input type="password" id="password" name="password" required class="w-full border border-gray-300 rounded px-4 py-2">
      </div>
      <button type="submit" class="w-full bg-petbioazulclaro hover:bg-petbioverde text-white font-bold py-2 rounded">Entrar</button>
      <a href="registropetbio.php" class="block text-center text-sm mt-4 text-petbioazul underline hover:text-petbioverde">¿No tienes cuenta? Regístrate aquí</a>
    </form>
  </div>
</body>
</html>
