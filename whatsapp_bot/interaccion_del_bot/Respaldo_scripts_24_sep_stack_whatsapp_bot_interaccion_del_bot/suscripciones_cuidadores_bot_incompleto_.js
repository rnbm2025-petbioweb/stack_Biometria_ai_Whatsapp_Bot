// suscripciones_cuidadores_bot.js
const mqtt = require('mqtt');
const mysql = require('mysql2/promise');
const utils = require('./utils_bot'); // Para justificar texto en WhatsApp

const MQTT_BROKER = 'mqtt://localhost:1883';
const client = mqtt.connect(MQTT_BROKER, { clientId: 'suscripcion_bot_' + Math.random().toString(16).substr(2, 8) });

const dbConfig = {
    host: 'mysql_petbio_secure',
    user: 'root',
    password: 'R00t_Segura_2025!',
    database: 'db__produccion_petbio_segura_2025',
    port: 3306
};

// =====================
// Función para enviar mensaje MQTT
// =====================
function enviarMensaje(usuarioId, mensaje) {
    const topic = `petbio/usuario/${usuarioId}`;
    client.publish(topic, mensaje);
}

// =====================
// Verificar suscripción en DB
// =====================
async function verificarSuscripcion(id_usuario) {
    const conn = await mysql.createConnection(dbConfig);
    const [rows] = await conn.execute(
        `SELECT id FROM pago_suscripcion WHERE id_usuario = ? ORDER BY id DESC LIMIT 1`,
        [id_usuario]
    );
    await conn.end();
    return rows.length > 0;
}

// =====================
// Contador de intentos por módulo
// =====================
let intentos = {}; // { usuarioId: { historia: n, cita: n } }

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
// Función para mostrar menú de suscripciones al usuario
// =====================
async function suscripcionesCuidadoresMenu(msg) {
    const tarifaTrimestre = 25;
    const tarifaSemestre = (tarifaTrimestre * 2 * 0.97).toFixed(2); // 3% de descuento
    const tarifaAnual = (tarifaTrimestre * 4 * 0.93).toFixed(2); // 7% de descuento

    const texto = `
📌 *Suscripciones Cuidadores PETBIO*

💰 Trimestre: $${tarifaTrimestre}
💰 6 meses: $${tarifaSemestre} (3% de descuento)
💰 1 año: $${tarifaAnual} (7% de descuento)

👉 Para suscribirte, ingresa aquí: https://petbio.siac2025.com/suscripciones_cuidadores
`;

    await msg.reply(utils.justificarTexto(texto, 40));
}

// =====================
// Suscribirse al topic de acceso a módulos
// =====================
client.on('connect', () => {
    console.log('✅ Suscripciones Bot conectado a Mosquitto');
    client.subscribe('petbio/bot/acceso_modulo', (err) => {
        if (!err) console.log('Suscrito a petbio/bot/acceso_modulo');
    });
});

client.on('message', async (topic, message) => {
    if (topic === 'petbio/bot/acceso_modulo') {
        const payload = JSON.parse(message.toString());
        const { usuarioId, modulo } = payload; // modulo: 'historia' | 'cita'
        await procesarAcceso
