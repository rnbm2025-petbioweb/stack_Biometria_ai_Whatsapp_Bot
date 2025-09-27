// integrador_de_menu_router_bot.js
// ==================================================
// Integrador completo PETBIO: usuarios, mascotas, menú y sesiones
// ==================================================

const fs = require('fs');
const path = require('path');
const { mqttCloud /*, mqttLocalDev, mqttLocalProd */ } = require('../config');
const saludoDelUsuario = require('./saludo_del_usuario');

// Funciones centralizadas
const { iniciarRegistroMascota, registrarMascota, sugerirRaza } = require('./registro_mascotas_bot');
const { iniciarRegistroUsuario } = require('./registro_usuario_bot');

// =====================
// Directorio de sesiones
// =====================
const SESSIONS_DIR = path.join(__dirname, 'sessions');
if (!fs.existsSync(SESSIONS_DIR)) fs.mkdirSync(SESSIONS_DIR, { recursive: true });

function getSessionFile(userId) { return path.join(SESSIONS_DIR, `session_${userId}.json`); }
function cargarSesion(userId) {
    const f = getSessionFile(userId);
    return fs.existsSync(f) ? JSON.parse(fs.readFileSync(f)) : null;
}
function guardarSesion(userId, session) { fs.writeFileSync(getSessionFile(userId), JSON.stringify(session)); }

// =====================
// 📶 Conexión MQTT
// =====================
/*
[mqttLocalDev, mqttLocalProd].forEach((client, index) => {
    const name = index === 0 ? 'Mosquitto DEV' : 'Mosquitto PROD';
    client.on('connect', () => console.log(`✅ Conectado a ${name}`));
    client.on('error', (err) => {
        console.error(`❌ Error ${name}:`, err.message);
        client.end(true);
    });
});
*/

// 👉 Solo usamos CloudMQTT
if (mqttCloud) {
    mqttCloud.on('connect', () => console.log('✅ Conectado a CloudMQTT'));
    mqttCloud.on('error', (err) => {
        console.error('❌ Error CloudMQTT:', err.message);
        mqttCloud.end(true);
    });
}

// =====================
// Router central
// =====================
async function router(msg, userId) {
    let session = cargarSesion(userId) || { type: 'menu_inicio', step: null, data: {}, id_usuario: userId };
    const texto = (msg.body || '').trim();
    const lc = texto.toLowerCase();

    // Comando global: cancelar o volver al menú
    if (lc === 'menu') {
        session.type = 'menu_inicio';
        session.step = null;
        session.data = {};
        guardarSesion(userId, session);
    }
    if (lc === 'cancelar' || lc === 'cancel') {
        try { fs.unlinkSync(getSessionFile(userId)); } catch (e) { }
        await msg.reply('✅ Operación cancelada. Volviendo al menú principal...');
        session.type = 'menu_inicio';
        session.step = null;
        session.data = {};
        guardarSesion(userId, session);
        return;
    }

    try {
        switch (session.type) {
            // ===================== Menú principal =====================
            case 'menu_inicio':
            case 'esperando_opcion_menu':
                await saludoDelUsuario(msg, getSessionFile(userId), session);

                await msg.reply(
                    '📌 Selecciona opción del menú:\n' +
                    '1️⃣ Registrar usuario\n' +
                    '2️⃣ Registrar mascota\n' +
                    '3️⃣ Suscribirse'
                );

                // Determinar flujo según texto
                if (texto === '1' || texto.includes('usuario')) {
                    session.type = 'registro_usuario';
                    session.step = 'username';
                } else if (texto === '2' || texto.includes('mascota')) {
                    session.type = 'registro_mascota';
                    session.step = 'nombre';
                } else if (texto === '3' || texto.includes('suscribirse')) {
                    session.type = 'suscripcion';
                }
                break;

            // ===================== Registro de usuario =====================
            case 'registro_usuario':
                await iniciarRegistroUsuario(msg, session, getSessionFile(userId));
                break;

            // ===================== Registro de mascota =====================
            case 'registro_mascota':
                await iniciarRegistroMascota(msg, session, getSessionFile(userId), mqttCloud);
                break;

            // ===================== Suscripción (placeholder) =====================
            case 'suscripcion':
                await msg.reply('🔔 Flujo de suscripción aún no implementado. Responde "menu" para volver al inicio.');
                break;

            // ===================== Menú post-registro =====================
            case 'menu_post_registro':
                await msg.reply('✅ Menú post-registro. Responde "menu" para volver al inicio.');
                break;

            default:
                await msg.reply('❌ Flujo no reconocido. Escribe *menu* para volver al inicio.');
                session.type = 'menu_inicio';
                session.step = null;
                session.data = {};
                break;
        }

        guardarSesion(userId, session);

    } catch (e) {
        console.error('❌ Error en router integrador:', e);
        await msg.reply('❌ Error interno. Escribe *menu* para reiniciar.');
        session.type = 'menu_inicio';
        session.step = null;
        session.data = {};
        guardarSesion(userId, session);
    }
}

// =====================
// Suscripción de usuario (puede llamarse desde router si se implementa)
// =====================
async function iniciarSuscripcionUsuario(msg, session) {
    try {
        await msg.reply(`💳 Suscripción PETBIO 💳\n$3 USD/mes. Responde SUSCRIBIRME para activar.`);
        session.type = 'suscripcion';
        session.step = 'confirmar';
        guardarSesion(session.id_usuario, session);
    } catch (e) { console.error(e); }
}

// =====================
// Exportamos funciones
// =====================
module.exports = {
    router,
    iniciarRegistroMascota,
    registrarMascota,
    sugerirRaza,
    iniciarRegistroUsuario,
    iniciarSuscripcionUsuario
};
