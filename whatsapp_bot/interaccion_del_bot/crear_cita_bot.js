const mqtt = require('mqtt');
const mysql = require('mysql2/promise');

// Configuración Mosquitto
const MQTT_BROKER = 'mqtt://localhost:1883';
const client = mqtt.connect(MQTT_BROKER, { clientId: 'crear_cita_bot_' + Math.random().toString(16).substr(2, 8), clean: true });

// Configuración DB
const dbConfig = {
    host: 'mysql_petbio_secure',
    user: 'root',
    password: 'R00t_Segura_2025!',
    database: 'db__produccion_petbio_segura_2025',
    port: 3306
};

// Contadores de intentos por usuario
let intentos = {}; // { usuarioId: { cita: n, historia: n } }

function enviarMensaje(usuarioId, mensaje) {
    const topic = `petbio/usuario/${usuarioId}`;
    client.publish(topic, mensaje);
}

async function verificarSuscripcion(id_usuario) {
    const conn = await mysql.createConnection(dbConfig);
    const [rows] = await conn.execute(
        `SELECT id FROM pago_suscripcion WHERE id_usuario = ? ORDER BY id DESC LIMIT 1`,
        [id_usuario]
    );
    await conn.end();
    return rows.length > 0;
}

async function validarAcceso(usuarioId, modulo) {
    const suscrito = await verificarSuscripcion(usuarioId);
    if (suscrito) return true;

    if (!intentos[usuarioId]) intentos[usuarioId] = { cita: 0, historia: 0 };
    if (intentos[usuarioId][modulo] < 3) {
        intentos[usuarioId][modulo]++;
        const restantes = 3 - intentos[usuarioId][modulo];
        enviarMensaje(usuarioId,
            `⚠️ Tienes ${restantes} intento(s) restante(s) para el módulo de ${modulo}.\n` +
            `Recuerda que PETBIO es tu sistema integral para mascotas 🐾. Aprovecha para explorar nuestros servicios completos, como Historia Clínica detallada y Citas personalizadas.\n` +
            `💡 Para acceso ilimitado y beneficios exclusivos, suscríbete ahora. Consulta tarifas en la opción 7 del menú.`
        );
        return true;
    } else {
        enviarMensaje(usuarioId,
            `❌ Has agotado tus 3 intentos gratuitos para el módulo de ${modulo}.\n` +
            `Para seguir disfrutando de todas nuestras funcionalidades y cuidar mejor de tus mascotas, te invitamos a suscribirte 🐾.\n` +
            `💳 Revisa nuestras tarifas en la opción 7 del menú y no pierdas la oportunidad de tener todo el sistema PETBIO a tu disposición.`
        );
        return false;
    }
}

// Funciones existentes para obtener mascotas y guardar cita
async function obtenerMascotas(id_usuario) {
    const conn = await mysql.createConnection(dbConfig);
    const [mascotas] = await conn.execute(
        `SELECT id, nombre, apellidos, clase_mascota FROM registro_mascotas WHERE id_usuario = ?`,
        [id_usuario]
    );
    await conn.end();
    return mascotas;
}

async function guardarCita(cita) {
    const conn = await mysql.createConnection(dbConfig);
    const sql = `INSERT INTO citas_veterinarias
                 (id_usuario, id_mascota, aliado_nombre, aliado_tipo, fecha_cita, hora_cita, modalidad, motivo, observaciones)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)`;
    await conn.execute(sql, [
        cita.id_usuario, cita.id_mascota, cita.aliado_nombre, cita.aliado_tipo,
        cita.fecha_cita, cita.hora_cita, cita.modalidad, cita.motivo, cita.observaciones
    ]);
    await conn.end();
}

// Función principal
async function procesarSolicitud(usuarioId) {
    if (!(await validarAcceso(usuarioId, 'cita'))) return;

    const mascotas = await obtenerMascotas(usuarioId);
    if (mascotas.length === 0) {
        enviarMensaje(usuarioId, '❌ No se encontraron mascotas registradas. Por favor registra primero tu mascota.');
        return;
    }

    // El flujo completo de selección de mascota, fecha, hora, modalidad, motivo, observaciones
    // (igual al código original)
}

// Suscribirse al topic
client.subscribe('petbio/bot/crear_cita');
client.on('message', (topic, message) => {
    if (topic === 'petbio/bot/crear_cita') {
        const payload = JSON.parse(message.toString());
        procesarSolicitud(payload.usuarioId);
    }
});
