<?php
session_start();
session_unset();      // Elimina todas las variables de sesión
session_destroy();    // Destruye la sesión actual

require_once 'config/conexion_petbio_nueva.php';

header("Location: loginpetbio.php");
exit;
