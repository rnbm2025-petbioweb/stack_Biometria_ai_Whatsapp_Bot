// saludo_del_usuario.js
const menuInicio = require('./menu_inicio');
const fs = require('fs');
const path = require('path');
const utils = require('./utils_bot');
const { iniciarMenu } = require('./menu_luego_de_registro_de_usuario'); // menú MQTT post-registro

/**
 * Saludo inicial: solo una vez al día por usuario
 * @param {object} msg - Mensaje de WhatsApp
 * @param {string} sessionFile - Ruta del archivo de sesión
 * @param {object} session - Objeto de sesión del usuario
 */
async function saludoDelUsuario(msg, sessionFile, session, usuarioId = null) {
    // Si session viene undefined, inicializamos
    if (!session) session = {};

    const hoy = new Date().toISOString().slice(0, 10); // YYYY-MM-DD

    // Si no hay registro de último saludo o es de otro día → saludamos
    if (!session.lastGreeted || session.lastGreeted !== hoy) {
        const saludo =
            "👋 ¡Hola! Somos *PETBIO* 🐾\n" +
            "📌 Registro Único Biométrico de Mascotas (RUBM).\n\n" +
            "✅ Estamos aquí para ayudarte a registrar y cuidar la identidad biométrica de tu mascota.\n\n";

        try {
            await msg.reply(utils.justificarTexto(saludo, 40));
        } catch (err) {
            console.error("Error enviando saludo:", err);
        }

        // Guardamos fecha de último saludo en la sesión
        session.lastGreeted = hoy;

        // Asegurarse de que la carpeta de sesiones exista
        const sessionDir = path.dirname(sessionFile);
        if (!fs.existsSync(sessionDir)) {
            fs.mkdirSync(sessionDir, { recursive: true });
        }

        fs.writeFileSync(sessionFile, JSON.stringify(session, null, 2));
    }

    // Si es menú inicial de WhatsApp
    if (session.type === 'menu_inicio' || !session.type) {
        try {
            const handleOption = await menuInicio(msg, sessionFile, session);
            return handleOption;
        } catch (err) {
            console.error("Error en menuInicio:", err);
        }
    }

    // Si el usuario acaba de registrarse y tenemos usuarioId, iniciar menú MQTT post-registro
    if (session.type === 'post_registro' && usuarioId) {
        try {
            iniciarMenu(usuarioId);
        } catch (err) {
            console.error("Error iniciando menú MQTT post-registro:", err);
        }
    }

    return null;
}

module.exports = saludoDelUsuario;
