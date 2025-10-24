<?php
session_start();

// ✅ Parámetros de conexión a producción
$host = 'mysql_petbio_secure';
$puerto = 3306;
$dbname = 'db__produccion_petbio_segura_2025';
$usuario = 'root';
$clave = 'R00t_Segura_2025!';

try {
  $pdo = new PDO("mysql:host=$host;port=$puerto;dbname=$dbname;charset=utf8", $usuario, $clave);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("❌ Error de conexión: " . $e->getMessage());
}

// ✅ Procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $lastname = trim($_POST['lastname'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirmPassword = $_POST['confirmPassword'] ?? '';
  $documento = $_POST['Documento_identidad'] ?? '';
  $tipoPersona = $_POST['tipo_Persona'] ?? '';
  $tieneMascotas = $_POST['tiene_mascotas'] ?? '0';

  // ✅ Validaciones
  if (!$username || !$lastname || !$email || !$password || !$confirmPassword || !$documento) {
    echo "<script>alert('⚠️ Todos los campos son obligatorios.'); window.history.back();</script>";
    exit;
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('⚠️ Correo electrónico no válido.'); window.history.back();</script>";
    exit;
  }

  if ($password !== $confirmPassword) {
    echo "<script>alert('⚠️ Las contraseñas no coinciden.'); window.history.back();</script>";
    exit;
  }

  // ✅ Verificar duplicados
  $stmt = $pdo->prepare("SELECT id_usuario FROM registro_usuario WHERE email = :email OR Documento_identidad = :doc");
  $stmt->execute([':email' => $email, ':doc' => $documento]);

  if ($stmt->rowCount() > 0) {
    echo "<script>alert('⚠️ Este correo o documento ya está registrado.'); window.history.back();</script>";
    exit;
  }

  // ✅ Hashear contraseña
  $passwordHash = password_hash($password, PASSWORD_DEFAULT);

  // ✅ Insertar usuario
  $insert = $pdo->prepare("
    INSERT INTO registro_usuario (
      username, apellidos_usuario, email, password, Documento_identidad, tipo_persona, tiene_mascotas
    ) VALUES (
      :username, :apellidos, :email, :password, :documento, :tipo_persona, :tiene_mascotas
    )
  ");

  try {
    $insert->execute([
      ':username' => $username,
      ':apellidos' => $lastname,
      ':email' => $email,
      ':password' => $passwordHash,
      ':documento' => $documento,
      ':tipo_persona' => $tipoPersona,
      ':tiene_mascotas' => $tieneMascotas
    ]);

    $id_usuario = $pdo->lastInsertId();

    // ✅ Guardar sesión
    $_SESSION['id_usuario'] = $id_usuario;
    $_SESSION['nombre'] = $username;
    $_SESSION['apellidos'] = $lastname;

    // ✅ Log de ingreso
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
    $navegador = $_SERVER['HTTP_USER_AGENT'] ?? 'desconocido';

    $log = $pdo->prepare("INSERT INTO registro_ingresos (id_usuario, direccion_ip, navegador) VALUES (:id, :ip, :nav)");
    $log->execute([':id' => $id_usuario, ':ip' => $ip, ':nav' => $navegador]);

    // ✅ Redirigir
    echo "<script>alert('✅ Registro exitoso. Ahora continúa con la identidad biométrica.'); window.location.href = 'identidad_rubm.php';</script>";
    exit;

  } catch (PDOException $e) {
    echo "<script>alert('❌ Error al guardar: " . $e->getMessage() . "'); window.history.back();</script>";
    exit;
  }

} else {
  echo "<script>alert('⚠️ Método no permitido.'); window.history.back();</script>";
  exit;
}
?>
