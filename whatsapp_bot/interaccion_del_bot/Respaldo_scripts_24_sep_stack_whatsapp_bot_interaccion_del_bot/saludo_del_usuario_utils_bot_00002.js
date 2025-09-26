// saludo_del_usuario.js
const menuInicio = require('./menu_inicio');
const fs = require('fs');
const utils = require('./utils_bot');

/**
 * Saludo inicial: solo una vez al día por usuario
 * @param {object} msg - Mensaje de WhatsApp
 * @param {string} sessionFile - Ruta del archivo de sesión
 * @param {object} session - Objeto de sesión del usuario
 */
async function saludoDelUsuario(msg, sessionFile, session) {
    const hoy = new Date().toISOString().slice(0, 10); // YYYY-MM-DD

    // Si no hay registro de último saludo o es de otro día → saludamos
    if (!session.lastGreeted || session.lastGreeted !== hoy) {
        const saludo =
            "👋 ¡Hola! Somos *PETBIO* 🐾\n" +
            "📌 Registro Único Biométrico de Mascotas (RUBM).\n\n" +
            "✅ Estamos aquí para ayudarte a registrar y cuidar la identidad biométrica de tu mascota.\n\n";

        await msg.reply(utils.justificarTexto(saludo, 40));

        // Guardamos fecha de último saludo en la sesión
        session.lastGreeted = hoy;
        fs.writeFileSync(sessionFile, JSON.stringify(session));
    }

    // Mostrar menú principal siempre
    const handleOption = await menuInicio(msg, sessionFile);

    return handleOption;
}

module.exports = saludoDelUsuario;

