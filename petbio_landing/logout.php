<?php
session_start();
require_once 'sesion_global.php';
$id_sesion = session_id();
$id_usuario = $_SESSION['id_usuario'] ?? null;

if ($id_usuario) {
    $pdo->prepare("DELETE FROM sesiones_activas WHERE id_sesion = :s AND id_usuario = :u")
        ->execute([':s'=>$id_sesion, ':u'=>$id_usuario]);
}

session_destroy();
header("Location: loginpetbio.php");
exit;

