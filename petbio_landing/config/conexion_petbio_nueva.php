<?php

require_once 'dir_config/conexion_petbio_nueva.php';

$conexion = new mysqli("mysql_petbio_secure", "root", "R00t_Segura_2025!", "db__produccion_petbio_segura_2025");

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}
?>
