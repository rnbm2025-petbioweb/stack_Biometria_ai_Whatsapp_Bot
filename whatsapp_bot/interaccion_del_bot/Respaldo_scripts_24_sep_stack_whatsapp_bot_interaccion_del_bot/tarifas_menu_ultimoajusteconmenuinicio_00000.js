// tarifas_menu.js
const fs = require('fs');
const path = require('path');
const utils = require('./utils_bot');

const MENU_TARIFAS = `
💲 *Tarifas PETBIO*

📌 Trimestral → $25.000 para cuidadores.
📌 Semestral → desde 17.000 a 13.000 por mascota.
📌 Anual → desde 30.000 a 23.000 por mascota.

✅ Escoge el plan según el rango de mascotas (1 a 500).
✅ Precios incluyen beneficios de trazabilidad, QR y servicios digitales.

🌐 Consulta el simulador financiero aquí: https://petbio.siac2025.com/finanzas_suscripcion.php

📌 Suscriptores:
- 3 meses → 15% de descuento sobre tarifa base.
- 6 meses → 25% de descuento.
- 1 año → 35% de descuento.
`;

async function menuTarifas(msg, sessionFile) {
    // Mostrar menú principal
    await msg.reply(utils.justificarTexto(MENU_TARIFAS, 40));

    // Asegurar directorio de sesión
    const sessionDir = path.dirname(sessionFile);
    if (!fs.existsSync(sessionDir)) fs.mkdirSync(sessionDir, { recursive: true });

    // Función para manejar opciones (en este caso, solo informativo)
    const handleOption = async () => {
        await msg.reply(utils.justificarTexto(
            "📌 Escribe *menu* para volver al inicio.\n" +
            "💡 Si deseas suscribirte y aplicar descuento, responde con *suscripcion* seguido del período: 3, 6 o 12 meses.", 40
        ));
    };

    return handleOption;
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
