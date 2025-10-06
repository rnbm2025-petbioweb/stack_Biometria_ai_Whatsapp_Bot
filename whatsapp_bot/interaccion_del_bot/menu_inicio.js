// menu_inicio.js
// ==================================================
// Menú Principal PETBIO — Gestión de opciones
// ==================================================

const fs = require('fs');
const path = require('path');
const utils = require('./utils_bot');
const { mqttCloud } = require('../config.js'); // Cliente CloudMQTT

// Importar submenús
const menuIdentidadCorporativa = require('./menu_identidad_corporativa');
const menuODS = require('./ODS_NUMERAL_6_menu_inicio');
<<<<<<< HEAD
// const { menuTarifas } = require('./tarifas_menu');
const { mostrarMenuTarifas, procesarSuscripcion } = require('./tarifas_menu');

// ⚠️ Renombramos para evitar conflicto de nombres
const { suscripcionesCuidadores, procesarSuscripcion: procesarSuscripcionCuidadores } = require('./suscripciones_cuidadores_bot');
=======
const { mostrarMenuTarifas, procesarSuscripcion } = require('./tarifas_menu');
const menuServicios = require('./servicios_menu');
const { iniciarSuscripciones, procesarAcceso } = require('./suscripciones_cuidadores_bot');
>>>>>>> faf9d38 (Actualización: cambios en archivos principales, limpieza de copias de respaldo)

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

<<<<<<< HEAD
    // ✅ Validación de sessionFile
=======
    // Validación de sessionFile
>>>>>>> faf9d38 (Actualización: cambios en archivos principales, limpieza de copias de respaldo)
    if (!sessionFile || typeof sessionFile !== "string") {
        console.warn("⚠️ sessionFile es null o inválido. Asignando ruta por defecto...");
        sessionFile = path.join(__dirname, "../.wwebjs_auth/session.json");
    }

    // Inicializar datos de sesión
    session.type = session.type || 'menu_inicio';
    session.step = session.step || 'esperando_opcion_menu';
    session.data = session.data || {};
    session.lastActive = Date.now();
    session.lastGreeted = session.lastGreeted || false;

    // Crear carpeta de sesión si no existe
    const sessionDir = path.dirname(sessionFile);
    if (!fs.existsSync(sessionDir)) fs.mkdirSync(sessionDir, { recursive: true });

<<<<<<< HEAD
    // ✅ Guardar sesión actualizada
=======
    // Guardar sesión actualizada
>>>>>>> faf9d38 (Actualización: cambios en archivos principales, limpieza de copias de respaldo)
    fs.writeFileSync(sessionFile, JSON.stringify(session, null, 2));

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
        '7': async () => {
            await msg.reply(mostrarMenuTarifas());
            session.type = 'tarifas';
<<<<<<< HEAD
            session.lastActive = Date.now();
=======
            session.step = 'esperando_periodo';
>>>>>>> faf9d38 (Actualización: cambios en archivos principales, limpieza de copias de respaldo)
            fs.writeFileSync(sessionFile, JSON.stringify(session, null, 2));
            publishMQTT("menu_interaccion", "Tarifas", msg.from);
        },
        '8': async () => {
            await menuServicios(msg, sessionFile);
            publishMQTT("menu_interaccion", "Servicios", msg.from);
        },
        '9': async () => {
            await iniciarSuscripciones(msg, sessionFile, session);
            publishMQTT("menu_interaccion", "Suscripciones Cuidadores", msg.from);
        },
    };

    // ==============================
    // 📌 Función para manejar la opción elegida
    // ==============================
    const handleOption = async (option) => {
        if (!option) return;

        // Limpiar input (espacios, saltos de línea)
        option = option.trim();

        if (session.type === 'suscripciones') {
            await procesarSuscripcionCuidadores(msg, sessionFile, session);
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
