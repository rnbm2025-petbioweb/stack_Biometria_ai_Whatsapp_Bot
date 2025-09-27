// saludo_del_usuario.js
const menuInicio = require('./menu_inicio');
const fs = require('fs');
const path = require('path');
const utils = require('./utils_bot');
const { iniciarMenu } = require('./menu_luego_de_registro_de_usuario');

// Obtener saludo dinámico según hora
function obtenerSaludo() {
    const hora = new Date().getHours();
    if (hora < 12) return "☀️ Buenos días";
    if (hora < 19) return "🌤️ Buenas tardes";
    return "🌙 Buenas noches";
}

/**
 * Saludo inicial: controlado (no cansón), integrado al flujo de menús
 * @param {object} msg - Mensaje de WhatsApp
 * @param {string} sessionFile - Ruta del archivo de sesión
 * @param {object} session - Objeto de sesión del usuario
 * @param {string|number|null} usuarioId - ID del usuario (si ya está registrado)
 */
async function saludoDelUsuario(msg, sessionFile, session, usuarioId = null) {
    if (!session) session = {};

    const hoy = new Date().toISOString().slice(0, 10); // YYYY-MM-DD
    const semana = getSemana(hoy);

    // Determinar si toca saludo largo o corto
    let mostrarSaludoLargo = false;
    if (!session.lastGreeted || session.lastGreeted !== hoy) {
        // Solo 1 vez por semana damos el saludo largo
        mostrarSaludoLargo = (!session.lastWeek || session.lastWeek !== semana);
    }

    if (!session.lastGreeted || session.lastGreeted !== hoy) {
        let saludo;

        if (mostrarSaludoLargo) {
            saludo =
                `${obtenerSaludo()} 👋 Somos *PETBIO* 🐾\n\n` +
                "📌 Registro Único Biométrico de Mascotas (RUBM).\n" +
                "✅ Estamos aquí para ayudarte a registrar y cuidar la identidad biométrica de tu mascota.\n" +
                "💡 Accede a servicios como *Historia Clínica*, *Citas* y *Suscripciones* con beneficios digitales.\n";
        } else {
            saludo = `${obtenerSaludo()} 👋 Bienvenido de nuevo a *PETBIO* 🐾`;
        }

        try {
            await msg.reply(utils.justificarTexto(saludo, 40));
        } catch (err) {
            console.error("Error enviando saludo:", err);
        }

        // Guardar en sesión
        session.lastGreeted = hoy;
        session.lastWeek = semana;

        const sessionDir = path.dirname(sessionFile);
        if (!fs.existsSync(sessionDir)) {
            fs.mkdirSync(sessionDir, { recursive: true });
        }
        fs.writeFileSync(sessionFile, JSON.stringify(session, null, 2));
    }

    // Flujo según estado del usuario
    if (session.type === 'menu_inicio' || !session.type) {
        try {
            return await menuInicio(msg, sessionFile, session);
        } catch (err) {
            console.error("Error en menuInicio:", err);
        }
    }

    if (session.type === 'post_registro' && usuarioId) {
        try {
            iniciarMenu(usuarioId);
        } catch (err) {
            console.error("Error iniciando menú post-registro:", err);
        }
    }

    return null;
}

// Función auxiliar para identificar la semana del año
function getSemana(fechaStr) {
    const fecha = new Date(fechaStr);
    const inicioAno = new Date(fecha.getFullYear(), 0, 1);
    const diff = fecha - inicioAno + ((inicioAno.getTimezoneOffset() - fecha.getTimezoneOffset()) * 60000);
    return Math.floor(diff / (7 * 24 * 60 * 60 * 1000)); // semana #
}

module.exports = saludoDelUsuario;
