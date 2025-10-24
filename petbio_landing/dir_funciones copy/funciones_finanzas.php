<?php
function guardarFinanzaYRegistroPago($mysqli, $id_usuario, $nombres, $apellidos, $documento, $cantidad_mascotas, $periodo, $telefono, $whatsapp, $evidencia_ruta) {
    require_once "calcularPrecio.php"; // asegúrate de que esta función esté accesible

    $resultado = calcularPrecio($cantidad_mascotas, $periodo);
    if (isset($resultado['error'])) {
        return ['error' => 'Error al calcular precio'];
    }

    // Insertar en finanzas_suscripciones
    $stmt1 = $mysqli->prepare("INSERT INTO finanzas_suscripciones (
        id_usuario, cantidad_mascotas, periodo, precio_unitario, total_con_iva,
        precio_sin_iva, total_sin_iva, utilidad_unitaria, utilidad_total
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt1->bind_param("iissiiiii",
        $id_usuario, $cantidad_mascotas, $periodo,
        $resultado['precio_unitario_con_iva'],
        $resultado['total_con_iva'],
        $resultado['precio_unitario_sin_iva'],
        $resultado['total_sin_iva'],
        $resultado['utilidad_por_mascota'],
        $resultado['utilidad_neta_total']
    );
    $stmt1->execute();
    $stmt1->close();

    // Insertar en pago_suscripcion
    $stmt2 = $mysqli->prepare("INSERT INTO pago_suscripcion (
        id_usuario, nombres, apellidos, documento, cantidad_exacta,
        telefono, whatsapp, evidencia_pago_ruta, cantidad_mascotas,
        periodo, total_pagado
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt2->bind_param("isssisssssi",
        $id_usuario, $nombres, $apellidos, $documento, $cantidad_mascotas,
        $telefono, $whatsapp, $evidencia_ruta, $cantidad_mascotas,
        $periodo, $resultado['total_con_iva']
    );
    $stmt2->execute();
    $stmt2->close();

    return ['ok' => 'Registro exitoso'];
}
?>
