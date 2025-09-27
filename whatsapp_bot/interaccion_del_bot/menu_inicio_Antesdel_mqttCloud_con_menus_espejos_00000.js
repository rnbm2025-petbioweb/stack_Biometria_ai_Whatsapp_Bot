// menu_inicio.js
const fs = require('fs');
const path = require('path');
const utils = require('./utils_bot');

// Importar módulos de menú existentes
const menuIdentidadCorporativa = require('./menu_identidad_corporativa');
const menuODS = require('./ODS_NUMERAL_6_menu_inicio');
const { menuTarifas } = require('./tarifas_menu');
const menuServicios = require('./servicios_menu');
const { suscripcionesCuidadores, procesarSuscripcion } = require('./suscripciones_cuidadores_bot');

// ✅ Importar capa MQTT para manejar servicios post-registro
const { iniciarMenu: iniciarMenuMqtt } = require('./menu_luego_de_registro_de_usuario');

// ==============================
// 📌 Texto principal del menú
// ==============================
const MENU_TEXT = `
📋 *Menú Principal PETBIO* 🐾

1️⃣ Identidad Corporativa
2️⃣ Políticas de Tratamiento de Datos
3️⃣ Registro de Usuario
4️⃣ Registro de Mascotas
5️⃣ Redes Sociales
6️⃣ ODS PETBIO 🌍
7️⃣ Tarifas 💲
8️⃣ Servicios 📦
9️⃣ Suscripciones Cuidadores
🔟 Conexión MQTT Cloud 🌐

👉 Responde con el número de la opción que deseas consultar.
✏️ Escribe *menu* en cualquier momento para volver aquí.
`;

/**
 * 🎯 Menú principal PETBIO
 * @param {object} msg - Mensaje entrante de WhatsApp
 * @param {string} sessionFile - Ruta del archivo de sesión
 * @param {object} session - Objeto de sesión
 */
async function menuInicio(msg, sessionFile, session) {
    // Asegurar estructura mínima de session
    session.type = session.type || 'menu_inicio';
    session.step = session.step || null;
    session.data = session.data || {};
    session.lastActive = Date.now();
    session.lastGreeted = session.lastGreeted || false;

    // Guardar sesión actualizada
    fs.writeFileSync(sessionFile, JSON.stringify(session, null, 2));

    // Mostrar menú principal
    await msg.reply(utils.justificarTexto(MENU_TEXT, 42));

    // Directorio de sesión
    const sessionDir = path.dirname(sessionFile);
    if (!fs.existsSync(sessionDir)) fs.mkdirSync(sessionDir, { recursive: true });

    // ==============================
    // 📌 Opciones disponibles
    // ==============================
    const opciones = {
        '1': async () => menuIdentidadCorporativa(msg),
        '2': async () => msg.reply(utils.justificarTexto(
            "🔒 Estás a punto de abrir el enlace de Políticas de Tratamiento de Datos:\n" +
            "📜 https://siac2025.com/"
        )),
        '3': async () => {
            session.type = 'registro_usuario';
            session.step = 'username';
            session.data = {};
            session.lastActive = Date.now();
            fs.writeFileSync(sessionFile, JSON.stringify(session, null, 2));
            await msg.reply(utils.justificarTexto(
                "👤 Registro de Usuario — escribe tu nombre:\n" +
                "(Escribe *cancelar* para abortar)"
            ));
        },
        '4': async () => {
            session.type = 'registro_mascota';
            session.step = 'nombre';
            session.data = {};
            session.lastActive = Date.now();
            fs.writeFileSync(sessionFile, JSON.stringify(session, null, 2));
            await msg.reply(utils.justificarTexto(
                "🐶 Registro de Mascota — escribe el nombre de la mascota:\n" +
                "(Escribe *cancelar* para abortar)"
            ));
        },
        '5': async () => msg.reply(utils.justificarTexto(
            "🌐 Redes Sociales PETBIO:\n" +
            "🔗 https://registro.siac2025.com/2025/02/11/48/"
        )),
        '6': async () => {
            const handleODS = await menuODS(msg, sessionFile);
            await handleODS('6', msg, sessionFile);
        },
        '7': async () => menuTarifas(msg, sessionFile, session),
        '8': async () => menuServicios(msg, sessionFile),
        '9': async () => suscripcionesCuidadores(msg, sessionFile, session),
        '10': async () => {
            await msg.reply(utils.justificarTexto(
                "🌐 Conectando con *MQTT Cloud PETBIO*... 🚀\n" +
                "📡 Preparando menú post-registro."
            ));
            try {
                iniciarMenuMqtt(msg.from); // Pasamos el ID del usuario
            } catch (err) {
                console.error("❌ Error al iniciar menú MQTT:", err);
                await msg.reply("⚠️ Hubo un problema conectando con el servicio MQTT.");
            }
        },
    };

    // ==============================
    // 📌 Función para manejar la opción elegida
    // ==============================
    const handleOption = async (option) => {
        if (session.type === 'suscripciones') {
            // Procesar selección de plan en suscripciones
            await procesarSuscripcion(msg, sessionFile, session);
            return;
        }

        if (opciones[option]) {
            await opciones[option]();
        } else {
            await msg.reply(utils.justificarTexto(
                "❌ Opción no válida. Aquí está el menú nuevamente:\n" + MENU_TEXT
            ));
        }
    };

    return handleOption;
}

module.exports = menuInicio;
