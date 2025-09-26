// suscripciones_cuidadores_bot.js
const mqtt = require('mqtt');
const mysql = require('mysql2/promise');
const fs = require('fs');
const path = require('path');
const utils = require('./utils_bot'); // Para justificar texto en WhatsApp

// =====================
// Configuración DB y MQTT
// =====================
const dbConfig = {
    host: process.env.MYSQL_HOST || 'mysql_petbio_secure',
    user: process.env.MYSQL_USER || 'root',
    password: process.env.MYSQL_PASSWORD || 'R00t_Segura_2025!',
    database: process.env.MYSQL_DATABASE || 'db__produccion_petbio_segura_2025',
    port: Number(process.env.MYSQL_PORT) || 3310
};

const MQTT_CLIENT = mqtt.connect(process.env.MQTT_BROKER || 'mqtt://localhost:1883', {
    clientId: 'suscripcion_bot_' + Math.random().toString(16).substr(2, 8),
    username: process.env.MQTT_USER,
    password: process.env.MQTT_PASS,
    clean: true
});

// =====================
// Conexión MQTT
// =====================
MQTT_CLIENT.on('connect', () => {
    console.log('✅ Suscripciones Bot conectado a Mosquitto');
    MQTT_CLIENT.subscribe('petbio/bot/acceso_modulo', (err) => {
        if (!err) console.log('Suscrito a petbio/bot/acceso_modulo');
    });
});

MQTT_CLIENT.on('error', (err) => {
    console.error('❌ Error MQTT:', err.message);
});

// =====================
// Contador de intentos
// =====================
let intentos = {}; // { usuarioId: { historia: n, cita: n } }

// =====================
// Funciones DB y MQTT
// =====================
async function verificarSuscripcion(id_usuario) {
    try {
        const conn = await mysql.createConnection(dbConfig);
        const [rows] = await conn.execute(
            `SELECT id FROM pago_suscripcion WHERE id_usuario = ? ORDER BY id DESC LIMIT 1`,
            [id_usuario]
        );
        await conn.end();
        return rows.length > 0;
    } catch (err) {
        console.error('❌ Error MySQL:', err.message);
        return false;
    }
}

function enviarMensaje(usuarioId, mensaje) {
    const topic = `petbio/usuario/${usuarioId}`;
    MQTT_CLIENT.publish(topic, mensaje);
}

// =====================
// Procesar acceso a módulo
// =====================
async function procesarAcceso(usuarioId, modulo) {
    const estaSuscrito = await verificarSuscripcion(usuarioId);

    if (estaSuscrito) {
        enviarMensaje(usuarioId, `✅ Tienes suscripción activa. Puedes acceder al módulo de ${modulo} sin restricciones.`);
        return true;
    }

    if (!intentos[usuarioId]) intentos[usuarioId] = { historia: 0, cita: 0 };

    if (intentos[usuarioId][modulo] < 3) {
        intentos[usuarioId][modulo]++;
        enviarMensaje(usuarioId, `⚠️ Acceso gratuito ${intentos[usuarioId][modulo]}/3 al módulo de ${modulo}.`);
        return true;
    } else {
        enviarMensaje(usuarioId,
            `❌ Has agotado tus 3 intentos gratuitos del módulo de ${modulo}.\n` +
            `Para seguir disfrutando de todos nuestros servicios (historia clínica, creación de citas, recordatorios, seguimiento de mascotas), por favor suscríbete. 🐾\n` +
            `👉 Ingresa a la sección de Suscripciones para completar tu registro.`
        );
        return false;
    }
}

// =====================
// Flujo WhatsApp para menú de suscripciones
// =====================
async function iniciarSuscripciones(msg, session, sessionFile) {
    const step = session.step || 'menu';
    const data = session.data || {};
    const texto = (msg.body || '').trim();

    try {
        if (texto.toLowerCase() === 'cancelar') {
            session.type = 'menu';
            fs.writeFileSync(sessionFile, JSON.stringify(session));
            return msg.reply('✅ Proceso cancelado. Volviendo al menú...');
        }

        switch(step) {
            case 'menu':
                const tarifaTrimestre = 25;
                const tarifaSemestre = (tarifaTrimestre * 2 * 0.97).toFixed(2);
                const tarifaAnual = (tarifaTrimestre * 4 * 0.93).toFixed(2);

                const menuTexto = `
📌 *Suscripciones Cuidadores PETBIO*

💰 Trimestre: $${tarifaTrimestre}
💰 6 meses: $${tarifaSemestre} (3% de descuento)
💰 1 año: $${tarifaAnual} (7% de descuento)

👉 Para suscribirte, ingresa aquí: https://petbio.siac2025.com/suscripciones_cuidadores
                `;
                await msg.reply(utils.justificarTexto(menuTexto, 40));
                session.step = 'menu';
                break;
        }

        session.data = data;
        fs.writeFileSync(sessionFile, JSON.stringify(session));
    } catch (err) {
        console.error(err);
        await msg.reply('❌ Ocurrió un error en el flujo de suscripciones. Volviendo al menú...');
        session.type = 'menu';
        session.step = null;
        session.data = null;
        fs.writeFileSync(sessionFile, JSON.stringify(session));
    }
}

// =====================
// Exportar funciones
// =====================
module.exports = { iniciarSuscripciones, procesarAcceso };
