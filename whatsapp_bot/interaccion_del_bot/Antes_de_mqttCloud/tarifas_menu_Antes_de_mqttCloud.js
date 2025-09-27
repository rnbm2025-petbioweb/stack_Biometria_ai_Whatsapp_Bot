// tarifas_menu.js
const fs = require('fs');
const path = require('path');

// Función local para "justificar" texto (opcional, simple wrap)
function justificarTexto(texto, ancho = 40) {
    // Divide el texto en líneas de longitud <= ancho
    const palabras = texto.split(' ');
    const lineas = [];
    let linea = '';

    for (const palabra of palabras) {
        if ((linea + palabra).length > ancho) {
            lineas.push(linea.trim());
            linea = palabra + ' ';
        } else {
            linea += palabra + ' ';
        }
    }
    if (linea) lineas.push(linea.trim());
    return lineas.join('\n');
}

const MENU_TARIFAS = `
💲 *Tarifas PETBIO*

📌 Trimestral → $25.000 para cuidadores.
📌 Semestral → desde 17.000 a 13.000 por mascota.
📌 Anual → desde 30.000 a 23.000 por mascota.

✅ Escoge el plan según el rango de mascotas (1 a 500).
✅ Precios incluyen beneficios de trazabilidad, QR y servicios digitales.

🌐 Consulta el simulador financiero aquí:
https://petbio.siac2025.com/finanzas_suscripcion.php

📌 Suscriptores:
- 3 meses → 15% de descuento sobre tarifa base.
- 6 meses → 25% de descuento.
- 1 año → 35% de descuento.
`;

async function menuTarifas(msg, sessionFile, session) {
    // Mostrar menú principal de tarifas
    await msg.reply(justificarTexto(MENU_TARIFAS, 40));

    // Actualizar sesión (opcional)
    session.type = 'menu_tarifas';
    fs.writeFileSync(sessionFile, JSON.stringify(session, null, 2));

    // Mensaje adicional de instrucciones
    await msg.reply(justificarTexto(
        "📌 Escribe *menu* para volver al inicio.\n" +
        "💡 Para suscribirte y aplicar descuento, responde con *suscripcion* seguido del período: 3, 6 o 12 meses.", 40
    ));
}

// Función para calcular tarifa con descuento según período
function calcularDescuento(precioBase, meses) {
    let descuento = 0;
    if (meses === 3) descuento = 0.15;
    else if (meses === 6) descuento = 0.25;
    else if (meses === 12) descuento = 0.35;

    return precioBase - (precioBase * descuento);
}

module.exports = { menuTarifas, calcularDescuento };
