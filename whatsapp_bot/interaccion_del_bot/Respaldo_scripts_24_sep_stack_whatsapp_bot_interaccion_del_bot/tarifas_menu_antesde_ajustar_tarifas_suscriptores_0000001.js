// tarifas_menu.js
const fs = require('fs');
const path = require('path');
const utils = require('./utils_bot');

const MENU_TARIFAS = `
💲 *Tarifas PETBIO*

1️⃣ Registro usuario: $10
2️⃣ Registro mascota: $15
3️⃣ Certificado biométrico: $20
4️⃣ Volver al menú principal

👉 Responde con el número de la opción que deseas consultar.
`;

async function menuTarifas(msg, sessionFile) {
    await msg.reply(utils.justificarTexto(MENU_TARIFAS, 40));

    // Asegurar que el directorio de sesión exista
    const sessionDir = path.dirname(sessionFile);
    if (!fs.existsSync(sessionDir)) {
        fs.mkdirSync(sessionDir, { recursive: true });
    }

    const opciones = {
        '1': async () => msg.reply(utils.justificarTexto("💰 Registro usuario: $10")),
        '2': async () => msg.reply(utils.justificarTexto("💰 Registro mascota: $15")),
        '3': async () => msg.reply(utils.justificarTexto("💰 Certificado biométrico: $20")),
        '4': async () => msg.reply(utils.justificarTexto("🔙 Volviendo al menú principal...")),
    };

    const handleOption = async (option) => {
        if (opciones[option]) {
            await opciones[option]();
        } else {
            await msg.reply(utils.justificarTexto("⚠️ Opción inválida. Escribe un número del menú.", 40));
        }
    };

    return handleOption;
}

module.exports = menuTarifas;
