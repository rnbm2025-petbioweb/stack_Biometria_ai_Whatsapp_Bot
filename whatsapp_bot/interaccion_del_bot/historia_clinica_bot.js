/**
 * historia_clinica_bot.js
 *
 * Bot de PETBIO que responde a solicitudes de consulta de Historia Clínica.
 */

const mqtt = require('mqtt');
const mysql = require('mysql2/promise');

// Configuración MQTT
const MQTT_BROKER = 'mqtt://localhost:1883';
const client = mqtt.connect(MQTT_BROKER, {
    clientId: 'historia_clinica_bot_' + Math.random().toString(16).substr(2, 8),
    clean: true,
    connectTimeout: 4000
});

client.on('connect', () => {
    console.log('✅ Historia Clínica Bot conectado a Mosquitto');
});

// Configuración DB
const dbConfig = {
    host: 'mysql_petbio_secure',
    user: 'root',
    password: 'R00t_Segura_2025!',
    database: 'db__produccion_petbio_segura_2025',
    port: 3306
};

// Contadores de intentos por usuario
let intentos = {}; // { usuarioId: { historia: n } }

// Función para enviar mensaje al usuario
function enviarMensaje(usuarioId, mensaje) {
    const topic = `petbio/usuario/${usuarioId}`;
    client.publish(topic, mensaje);
}

// Función para verificar suscripción
async function verificarSuscripcion(id_usuario) {
    const conn = await mysql.createConnection(dbConfig);
    const [rows] = await conn.execute(
        `SELECT id FROM pago_suscripcion WHERE id_usuario = ? ORDER BY id DESC LIMIT 1`,
        [id_usuario]
    );
    await conn.end();
    return rows.length > 0;
}

// Validar acceso al módulo con intentos gratuitos
async function validarAcceso(usuarioId, modulo) {
    const suscrito = await verificarSuscripcion(usuarioId);
    if (suscrito) return true;

    if (!intentos[usuarioId]) intentos[usuarioId] = { historia: 0 };
    if (intentos[usuarioId][modulo] < 3) {
        intentos[usuarioId][modulo]++;
        const restantes = 3 - intentos[usuarioId][modulo];
        enviarMensaje(usuarioId,
            `⚠️ Tienes ${restantes} intento(s) restante(s) para consultar Historias Clínicas.\n` +
            `PETBIO es tu sistema integral para el cuidado de tus mascotas 🐾.\n` +
            `💡 Para acceso ilimitado y beneficios exclusivos, suscríbete ahora. Revisa tarifas en la opción 7 del menú.`
        );
        return true;
    } else {
        enviarMensaje(usuarioId,
            `❌ Has agotado tus 3 intentos gratuitos para consultar Historias Clínicas.\n` +
            `Para seguir disfrutando de todos los servicios PETBIO, suscríbete 🐾.\n` +
            `💳 Consulta nuestras tarifas en la opción 7 del menú.`
        );
        return false;
    }
}

// Obtener mascotas del usuario
async function obtenerMascotas(id_usuario) {
    const conn = await mysql.createConnection(dbConfig);
    const [mascotas] = await conn.execute(
        `SELECT id, nombre, apellidos, edad, raza, clase_mascota, ciudad, barrio
         FROM registro_mascotas WHERE id_usuario = ?`,
        [id_usuario]
    );
    await conn.end();
    return mascotas;
}

// Obtener historia clínica de una mascota
async function obtenerHistoriaClinica(id_mascota) {
    const conn = await mysql.createConnection(dbConfig);
    const [historias] = await conn.execute(
        `SELECT h.*, a.ruta_archivo, a.tipo AS tipo_archivo
         FROM historia_clinica h
         LEFT JOIN archivos_historia_clinica a ON h.id_historia = a.id_historia
         WHERE h.id_mascota = ?`,
        [id_mascota]
    );
    await conn.end();
    return historias;
}

// Procesar solicitud de historia clínica
async function procesarSolicitud(usuarioId) {
    if (!(await validarAcceso(usuarioId, 'historia'))) return;

    const mascotas = await obtenerMascotas(usuarioId);
    if (mascotas.length === 0) {
        enviarMensaje(usuarioId, '❌ No se encontraron mascotas registradas.');
        return;
    }

    let mensaje = '🐾 Tus mascotas registradas:\n';
    mascotas.forEach((m, idx) => {
        mensaje += `${idx + 1}. ${m.nombre} ${m.apellidos} - ${m.clase_mascota}\n`;
    });
    mensaje += '\nResponde con el número de la mascota para ver su historia clínica.';
    enviarMensaje(usuarioId, mensaje);

    const topicEntrada = `petbio/entrada/${usuarioId}`;
    client.subscribe(topicEntrada, (err) => {
        if (!err) console.log(`✅ Escuchando elección de mascota para usuario ${usuarioId}`);
    });

    client.once('message', async (topic, message) => {
        if (topic !== topicEntrada) return;

        const opcion = parseInt(message.toString().trim());
        if (isNaN(opcion) || opcion < 1 || opcion > mascotas.length) {
            enviarMensaje(usuarioId, '❌ Opción inválida. Por favor responde con el número correcto.');
            return;
        }

        const mascotaSeleccionada = mascotas[opcion - 1];
        const historias = await obtenerHistoriaClinica(mascotaSeleccionada.id);

        if (historias.length === 0) {
            enviarMensaje(usuarioId, `❌ No hay historia clínica para ${mascotaSeleccionada.nombre}.`);
            return;
        }

        for (let h of historias) {
            let msgHistorial = `🩺 Historia Clínica de ${mascotaSeleccionada.nombre}:\n`;
            msgHistorial += `⚕️ Condición: ${h.id_condicion}\n`;
            msgHistorial += `📝 Motivo: ${h.motivo_consulta}\n`;
            msgHistorial += `🧪 Diagnóstico: ${h.diagnostico}\n`;
            msgHistorial += `💊 Tratamiento: ${h.tratamiento || 'No registrado'}\n`;
            msgHistorial += `📌 Observaciones: ${h.observaciones || 'Ninguna'}\n`;
            enviarMensaje(usuarioId, msgHistorial);

            if (h.ruta_archivo) {
                enviarMensaje(usuarioId, `📎 Archivo (${h.tipo_archivo}): ${h.ruta_archivo}`);
            }
        }

        // Volver a menú principal
        const menuBot = require('./menu_luego_de_registro_de_usuario');
        menuBot.iniciarMenu(usuarioId);
    });
}

// Suscribirse al topic principal
const topicSolicitud = 'petbio/bot/historia_clinica';
client.subscribe(topicSolicitud, (err) => {
    if (!err) console.log(`✅ Suscrito al topic ${topicSolicitud}`);
});

client.on('message', (topic, message) => {
    if (topic === topicSolicitud) {
        const payload = JSON.parse(message.toString());
        const usuarioId = payload.usuarioId;
        procesarSolicitud(usuarioId);
    }
});
