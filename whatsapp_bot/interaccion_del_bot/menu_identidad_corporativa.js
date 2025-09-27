// menu_identidad_corporativa.js
const { mqttCloud } = require('./config.js');
const fs = require('fs');
const path = require('path');

/**
 * Menú de Identidad Corporativa PETBIO11
 * @param {object} msg - Mensaje de WhatsApp
 * @param {string} [sessionFile] - Archivo de sesión opcional
 */
async function menuIdentidadCorporativa(msg, sessionFile = null) {
    const identidad =
        "📖 *Manual de Identidad Corporativa PETBIO11 RNBM*\n\n" +

        "✨ *Introducción*\n" +
        "Este Manual establece las bases visuales y comunicativas que definen la identidad de *PETBIO11*. " +
        "Garantiza coherencia, profesionalismo y reconocimiento de la marca en todos los canales, reflejando " +
        "nuestros valores y diferenciándonos de los demás.\n\n" +

        "🐾 *Quiénes somos*\n" +
        "PETBIO11 RNBM es una iniciativa colombiana que busca transformar la gestión del bienestar animal " +
        "mediante el *Registro Único Biométrico de Mascotas (RUBM)*. Centraliza y digitaliza la información " +
        "de las mascotas en Colombia, promoviendo la tenencia responsable y facilitando el acceso a servicios " +
        "de salud y protección animal.\n\n" +

        "🎯 *Misión*\n" +
        "Desarrollar soluciones tecnológicas innovadoras que mejoren la calidad de vida de las mascotas y " +
        "fortalezcan la relación entre animales y humanos, mediante un sistema de identificación biométrica " +
        "único, seguro y accesible.\n\n" +

        "👁️ *Visión*\n" +
        "Ser el referente nacional en gestión digital del bienestar animal, integrando a entidades públicas, " +
        "privadas y sociales en un ecosistema colaborativo que garantice la protección, trazabilidad y cuidado " +
        "de las mascotas en Colombia.\n\n" +

        "💡 *Valores de marca*\n" +
        "• 🐕 Tenencia responsable\n" +
        "• 🛡️ Seguridad y trazabilidad\n" +
        "• 🤝 Colaboración institucional\n" +
        "• ⚙️ Innovación tecnológica\n" +
        "• ❤️ Bienestar animal\n\n" +
        "Cada valor refuerza nuestro compromiso con el bienestar físico, emocional y social de los animales.\n\n" +

        "🗣️ *Tono de comunicación*\n" +
        "Tecnológico, empático y confiable. Nos comunicamos con un tono claro, educativo y accesible, " +
        "adaptado a propietarios comunes, entidades públicas y privadas. Buscamos generar conciencia " +
        "sobre la tenencia responsable y el bienestar animal.\n\n" +

        "✅ *Fin del documento*";

    await msg.reply(identidad);

    // Publicar evento en CloudMQTT
    if (mqttCloud && mqttCloud.connected) {
        mqttCloud.publish(
            'petbio/menu_interaccion',
            JSON.stringify({
                usuario: msg.from,
                descripcion: 'Accedió a Identidad Corporativa',
                fecha: new Date().toISOString()
            }),
            { qos: 1 }
        );
        console.log(`[${new Date().toLocaleTimeString()}] 🔹 MQTT publicado: Identidad Corporativa -> ${msg.from}`);
    }

    // Guardar última interacción en sesión (opcional)
    if (sessionFile) {
        let sessionData = {};
        if (fs.existsSync(sessionFile)) {
            try {
                sessionData = JSON.parse(fs.readFileSync(sessionFile));
            } catch {}
        }
        sessionData.lastActive = Date.now();
        sessionData.lastMenu = 'Identidad Corporativa';
        const sessionDir = path.dirname(sessionFile);
        if (!fs.existsSync(sessionDir)) fs.mkdirSync(sessionDir, { recursive: true });
        fs.writeFileSync(sessionFile, JSON.stringify(sessionData, null, 2));
    }
}

module.exports = menuIdentidadCorporativa;

