// servicios_menu.js
const utils = require('./utils_bot');

const MENU_SERVICIOS = `
📦 *Servicios PETBIO*

1️⃣ Consulta veterinaria
2️⃣ Consultoría de alimentación
3️⃣ Registro biométrico adicional
4️⃣ Volver al menú principal

👉 Responde con el número de la opción que deseas consultar.
`;

async function menuServicios(msg, sessionFile) {
    await msg.reply(utils.justificarTexto(MENU_SERVICIOS, 40));

    const opciones = {
        '1': async () => msg.reply(utils.justificarTexto("🐾 Consulta veterinaria disponible.")),
        '2': async () => msg.reply(utils.justificarTexto("🥗 Consultoría de alimentación personalizada.")),
        '3': async () => msg.reply(utils.justificarTexto("📋 Registro biométrico adicional disponible.")),
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

module.exports = menuServicios;
