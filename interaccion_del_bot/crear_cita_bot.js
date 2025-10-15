/**
 * crear_cita_bot.js
 *
 * Bot de PETBIO que responde a solicitudes de creación de citas veterinarias.
 */

const mqtt = require('mqtt');
const mysql = require('mysql2/promise');

// ==========================
// Configuración MQTT
// ==========================

// 🔴 SOLO ACTIVAR UNO SEGÚN EL ENTORNO
// const MQTT_BROKER = 'mqtt://localhost:1883';              // 🖥️ Local DEV (Mosquitto local)
// const MQTT_BROKER = 'mqtt://192.168.1.20:1883';           // 🖧 Mosquitto LAN DEV
const MQTT_BROKER = process.env.MQTT_CLOUD || 'mqtt://duck-01.lmq.cloudamqp.com:1883'; // ☁️ MQTT Cloud PROD

const client = mqtt.connect(MQTT_BROKER, {
  clientId: 'crear_cita_bot_' + Math.random().toString(16).substr(2, 8),
  clean: true,
  connectTimeout: 4000,
  username: process.env.MQTT_USER || 'usuario_cloud',
  password: process.env.MQTT_PASS || 'clave_cloud'
});

client.on('connect', () => {
  console.log('✅ Crear Cita Bot conectado a MQTT');
});

client.on('error', (err) => {
  console.error('❌ Error MQTT:', err.message);
});

// ==========================
// Configuración DB
// ==========================
const dbConfig = {
  host: process.env.MYSQL_HOST || 'mysql_petbio_secure',
  user: process.env.MYSQL_USER || 'root',
  password: process.env.MYSQL_PASSWORD || 'R00t_Segura_2025!',
  database: process.env.MYSQL_DATABASE || 'db__produccion_petbio_segura_2025',
  port: Number(process.env.MYSQL_PORT) || 3310
};

// ==========================
// Contadores de intentos por usuario
// ==========================
let intentos = {}; // { usuarioId: { cita: n, historia: n } }

// ==========================
// Función para enviar mensaje al usuario
// ==========================
function enviarMensaje(usuarioId, mensaje) {
  const topic = `petbio/usuario/${usuarioId}`;
  client.publish(topic, mensaje);
}

// ==========================
// Verificar suscripción
// ==========================
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

// ==========================
// Validar acceso con intentos gratuitos
// ==========================
async function validarAcceso(usuarioId, modulo) {
  const suscrito = await verificarSuscripcion(usuarioId);
  if (suscrito) return true;

  if (!intentos[usuarioId]) intentos[usuarioId] = { cita: 0, historia: 0 };

  if (intentos[usuarioId][modulo] < 3) {
    intentos[usuarioId][modulo]++;
    const restantes = 3 - intentos[usuarioId][modulo];
    enviarMensaje(usuarioId,
      `⚠️ Acceso gratuito ${intentos[usuarioId][modulo]}/3 al módulo de ${modulo}.\n` +
      `👉 Te quedan ${restantes} intentos.\n\n` +
      `🐾 PETBIO es tu sistema integral para el cuidado de tus mascotas.\n` +
      `💡 Suscríbete para acceso ilimitado y beneficios exclusivos. Consulta tarifas en la opción 7 del menú.`
    );
    return true;
  } else {
    enviarMensaje(usuarioId,
      `❌ Has agotado tus 3 intentos gratuitos en el módulo de ${modulo}.\n` +
      `🐾 Para seguir usando PETBIO, suscríbete ahora.\n` +
      `💳 Consulta nuestras tarifas en la opción 7 del menú.`
    );
    return false;
  }
}

// ==========================
// Obtener mascotas del usuario
// ==========================
async function obtenerMascotas(id_usuario) {
  const conn = await mysql.createConnection(dbConfig);
  const [mascotas] = await conn.execute(
    `SELECT id, nombre, apellidos, clase_mascota
     FROM registro_mascotas WHERE id_usuario = ?`,
    [id_usuario]
  );
  await conn.end();
  return mascotas;
}

// ==========================
// Guardar cita
// ==========================
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

// ==========================
// Procesar solicitud de cita
// ==========================
async function procesarSolicitud(usuarioId) {
  if (!(await validarAcceso(usuarioId, 'cita'))) return;

  const mascotas = await obtenerMascotas(usuarioId);
  if (mascotas.length === 0) {
    enviarMensaje(usuarioId, '❌ No se encontraron mascotas registradas. Por favor registra primero tu mascota.');
    return;
  }

  // Aquí deberías continuar con el flujo:
  // 1. Mostrar mascotas y pedir selección
  // 2. Pedir fecha, hora, modalidad
  // 3. Pedir motivo y observaciones
  // 4. Guardar en DB con guardarCita()
  // 5. Confirmar al usuario
  enviarMensaje(usuarioId,
    `📅 Para crear una cita, selecciona primero una de tus mascotas.\n` +
    mascotas.map((m, idx) => `${idx + 1}. ${m.nombre} (${m.clase_mascota})`).join('\n')
  );

  // 👇 Aquí podrías suscribirte a `petbio/entrada/${usuarioId}` como en historia_clinica_bot.js
  // para manejar paso a paso el flujo de datos del usuario.
}

// ==========================
// Suscribirse al topic
// ==========================
const topicSolicitud = 'petbio/bot/crear_cita';
client.subscribe(topicSolicitud, (err) => {
  if (!err) console.log(`✅ Suscrito al topic ${topicSolicitud}`);
});

client.on('message', (topic, message) => {
  if (topic === topicSolicitud) {
    try {
      const payload = JSON.parse(message.toString());
      procesarSolicitud(payload.usuarioId);
    } catch (err) {
      console.error('❌ Error procesando payload:', err.message);
    }
  }
});
