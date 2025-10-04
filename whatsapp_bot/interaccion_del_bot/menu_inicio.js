// menu_inicio.js
const fs = require('fs');
const path = require('path');
const utils = require('./utils_bot');
const { mqttCloud } = require('../config.js'); // Usamos directamente tu cliente CloudMQTT

// Importar submenús
const menuIdentidadCorporativa = require('./menu_identidad_corporativa');
const menuODS = require('./ODS_NUMERAL_6_menu_inicio');
//const { menuTarifas } = require('./tarifas_menu');
const { mostrarMenuTarifas, procesarSuscripcion } = require('./tarifas_menu');

const menuServicios = require('./servicios_menu');
const { suscripcionesCuidadores, procesarSuscripcion } = require('./suscripciones_cuidadores_bot');

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

👉 Responde con el número de la opción que deseas consultar.
✏️ Escribe *menu* en cualquier momento para volver aquí.
`;

// ==============================
// 🎯 Función principal
// ==============================
async function menuInicio(msg, sessionFile, session) {
    console.log("📁 sessionFile recibido en menuInicio:", sessionFile);

    // ✅ NUEVO: Validación de sessionFile antes de usarlo
    if (!sessionFile || typeof sessionFile !== "string") {
        console.warn("⚠️ sessionFile es null o inválido. Asignando ruta por defecto...");
        sessionFile = path.join(__dirname, "../.wwebjs_auth/session.json");
    }

    // ✅ Inicializar datos de sesión
    session.type = session.type || 'menu_inicio';
    session.step = session.step || null;
    session.data = session.data || {};
    session.lastActive = Date.now();
    session.lastGreeted = session.lastGreeted || false;

    // ✅ Crear carpeta de sesión si no existe antes de escribir
    const sessionDir = path.dirname(sessionFile);
    if (!fs.existsSync(sessionDir)) {
        fs.mkdirSync(sessionDir, { recursive: true });
    }

    // ✅ Guardar sesión actualizada
    fs.writeFileSync(sessionFile, JSON.stringify(session, null, 2));

    // ==============================
    // 📜 CÓDIGO ORIGINAL (comentado) — ahora reemplazado por lo anterior
    // ==============================
    /*
    // Guardar sesión actualizada
    if (sessionFile && typeof sessionFile === "string") {
        fs.writeFileSync(sessionFile, JSON.stringify(session, null, 2));
    } else {
        console.error("⚠️ sessionFile es inválido en menuInicio:", sessionFile);
    }
    */

    /*
    async function menuInicio(msg, sessionFile, session) {
        session.type = session.type || 'menu_inicio';
        session.step = session.step || null;
        session.data = session.data || {};
        session.lastActive = Date.now();
        session.lastGreeted = session.lastGreeted || false;

        // Guardar sesión actualizada
        // fs.writeFileSync(sessionFile, JSON.stringify(session, null, 2));

        // Guardar sesión actualizada ✅ con validación
        if (sessionFile && typeof sessionFile === "string") {
          fs.writeFileSync(sessionFile, JSON.stringify(session, null, 2));
        } else {
          console.error("⚠️ sessionFile es inválido en menuInicio:", sessionFile);
        }
    }
    */

    // ==============================
    // 📜 Mostrar menú principal
    // ==============================
    await msg.reply(utils.justificarTexto(MENU_TEXT, 42));

    // ==============================
    // 📌 Opciones disponibles
    // ==============================
    const opciones = {
        '1': async () => {
            await menuIdentidadCorporativa(msg);
            publishMQTT("menu_interaccion", "Identidad Corporativa", msg.from);
        },
        '2': async () => {
            await msg.reply(utils.justificarTexto(
                "🔒 Estás a punto de abrir el enlace de Políticas de Tratamiento de Datos:\n📜 https://siac2025.com/"
            ));
            publishMQTT("menu_interaccion", "Politicas de Datos", msg.from);
        },
        '3': async () => {
            session.type = 'registro_usuario';
            session.step = 'username';
            session.data = {};
            session.lastActive = Date.now();
            fs.writeFileSync(sessionFile, JSON.stringify(session, null, 2));
            await msg.reply(utils.justificarTexto(
                "👤 Registro de Usuario — escribe tu nombre:\n(Escribe *cancelar* para abortar)"
            ));
            publishMQTT("menu_interaccion", "Registro de Usuario", msg.from);
        },
        '4': async () => {
            session.type = 'registro_mascota';
            session.step = 'nombre';
            session.data = {};
            session.lastActive = Date.now();
            fs.writeFileSync(sessionFile, JSON.stringify(session, null, 2));
            await msg.reply(utils.justificarTexto(
                "🐶 Registro de Mascota — escribe el nombre de la mascota:\n(Escribe *cancelar* para abortar)"
            ));
            publishMQTT("menu_interaccion", "Registro de Mascotas", msg.from);
        },
        '5': async () => {
            await msg.reply(utils.justificarTexto(
                "🌐 Redes Sociales PETBIO:\n🔗 https://registro.siac2025.com/2025/02/11/48/"
            ));
            publishMQTT("menu_interaccion", "Redes Sociales", msg.from);
        },
        '6': async () => {
            const handleODS = await menuODS(msg, sessionFile);
            await handleODS('6', msg, sessionFile);
            publishMQTT("menu_interaccion", "ODS PETBIO", msg.from);
        },
/*        '7': async () => {
            await menuTarifas(msg, sessionFile, session);
            publishMQTT("menu_interaccion", "Tarifas", msg.from);
        },   */

	'7': async () => {
	    await msg.reply(mostrarMenuTarifas());
	    session.type = 'tarifas';
	    session.lastActive = Date.now();
	    fs.writeFileSync(sessionFile, JSON.stringify(session, null, 2));
	    publishMQTT("menu_interaccion", "Tarifas", msg.from);
	},



        '8': async () => {
            await menuServicios(msg, sessionFile);
            publishMQTT("menu_interaccion", "Servicios", msg.from);
        },
        '9': async () => {
            await suscripcionesCuidadores(msg, sessionFile, session);
            publishMQTT("menu_interaccion", "Suscripciones Cuidadores", msg.from);
        },
    };

    // ==============================
    // 📌 Función para manejar la opción elegida
    // ==============================
    const handleOption = async (option) => {
        if (session.type === 'suscripciones') {
            await procesarSuscripcion(msg, sessionFile, session);
            publishMQTT("menu_interaccion", "Procesando Suscripcion", msg.from);
            return;
        }

        if (opciones[option]) {
            await opciones[option]();
        } else {
            await msg.reply(utils.justificarTexto(
                "❌ Opción no válida. Aquí está el menú nuevamente:\n" + MENU_TEXT
            ));
            publishMQTT("menu_interaccion", "Opcion no valida", msg.from);
        }
    };

    return handleOption;
}

// ==============================
// 📌 Función para publicar eventos en MQTT
// ==============================
function publishMQTT(topic, descripcion, usuario) {
    if (mqttCloud && mqttCloud.connected) {
        mqttCloud.publish(`petbio/${topic}`, JSON.stringify({
            usuario,
            descripcion,
            fecha: new Date().toISOString()
        }), { qos: 1 });
        console.log(`🔹 MQTT publicado: ${topic} -> ${descripcion}`);
    }
}

module.exports = menuInicio;
