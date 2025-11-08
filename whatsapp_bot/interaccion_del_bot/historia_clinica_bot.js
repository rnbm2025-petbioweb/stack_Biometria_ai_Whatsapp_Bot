/**
 * historia_clinica_bot.js
 *
 * Bot de PETBIO que responde a solicitudes de consulta de Historia Clínica.
 */

import mqtt from 'mqtt';
import mysql from 'mysql2/promise';
import dotenv from 'dotenv';
dotenv.config();

// ==========================
// Configuración MQTT
// ==========================
const MQTT_BROKER = process.env.MQTT_CLOUD || 'mqtt://duck-01.lmq.cloudamqp.com:1883';

const client = mqtt.connect(MQTT_BROKER, {
  clientId: 'historia_clinica_bot_' + Math.random().toString(16).substr(2, 8),
  clean: true,
  connectTimeout: 4000,
  username: process.env.MQTT_USER || 'usuario_cloud',
  password: process.env.MQTT_PASS || 'clave_cloud'
});

client.on('connect', () => console.log('✅ Historia Clínica Bot conectado a MQTT'));
client.on('error', (err) => console.error('❌ Error historiaClinica - MQTT:', err.message));

// ==========================
// Configuración DB
// ==========================
const dbConfig = {
  host: process.env.MYSQL_HOST || '127.0.0.1',
  user: process.env.MYSQL_USER || 'root',
  password: process.env.MYSQL_PASSWORD || 'R00t_Segura_2025!',
  database: process.env.MYSQL_DATABASE || 'db__produccion_petbio_segura_2025',
  port: Number(process.env.MYSQL_PORT) || 3306
};

console.log('✅ Entró a historiaClinicaBot');

// ==========================
// Contadores de intentos por usuario
// ==========================
let intentos = {}; // { usuarioId: { historia: n } }

// ==========================
// Funciones principales
// ==========================
function enviarMensaje(usuarioId, mensaje) {
  const topic = `petbio/usuario/${usuarioId}`;
  client.publish(topic, mensaje);
}

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

async function validarAcceso(usuarioId, modulo) {
  const suscrito = await verificarSuscripcion(usuarioId);
  if (suscrito) return true;

  if (!intentos[usuarioId]) intentos[usuarioId] = { historia: 0 };

  if (intentos[usuarioId][modulo] < 3) {
    intentos[usuarioId][modulo]++;
    const restantes = 3 - intentos[usuarioId][modulo];
    enviarMensaje(usuarioId,
      `⚠️ Acceso gratuito ${intentos[usuarioId][modulo]}/3 para *Historia Clínica*.\n` +
      `👉 Te quedan ${restantes} intentos.\n\n` +
      `💡 Suscríbete para acceso ilimitado y beneficios exclusivos. Revisa tarifas en la opción 7 del menú.`
    );
    return true;
  } else {
    enviarMensaje(usuarioId,
      `❌ Has agotado tus 3 intentos gratuitos en *Historia Clínica*.\n` +
      `🐾 Para continuar usando PETBIO, suscríbete ahora.\n` +
      `💳 Consulta nuestras tarifas en la opción 7 del menú.`
    );
    return false;
  }
}

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

async function procesarSolicitud(usuarioId) {
  if (!(await validarAcceso(usuarioId, 'historia'))) return;

  const mascotas = await obtenerMascotas(usuarioId);
  if (mascotas.length === 0) {
    enviarMensaje(usuarioId, '❌ No se encontraron mascotas registradas.');
    return;
  }

  let mensaje = '🐾 *Tus mascotas registradas:*\n';
  mascotas.forEach((m, idx) => {
    mensaje += `${idx + 1}. ${m.nombre} ${m.apellidos} - ${m.clase_mascota}\n`;
  });
  mensaje += '\n👉 Responde con el número de la mascota para ver su historia clínica.';
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
      let msgHistorial = `🩺 *Historia Clínica de ${mascotaSeleccionada.nombre}:*\n`;
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
    try {
      const menuBot = await import('./menu_luego_de_registro_de_usuario.js');
      menuBot.iniciarMenu(usuarioId);
    } catch (err) {
      console.error('❌ Error cargando menú principal:', err.message);
    }
  });
}

// ==========================
// Suscribirse al topic principal
// ==========================
const topicSolicitud = 'petbio/bot/historia_clinica';
client.subscribe(topicSolicitud, (err) => {
  if (!err) console.log(`✅ Suscrito al topic ${topicSolicitud}`);
});

client.on('message', (topic, message) => {
  if (topic === topicSolicitud) {
    try {
      const payload = JSON.parse(message.toString());
      const usuarioId = payload.usuarioId;
      procesarSolicitud(usuarioId);
    } catch (err) {
      console.error('❌ Error procesando payload:', err.message);
    }
  }
});

// ==========================
// Export default para index.js
// ==========================
export default {
  procesarSolicitud,
  enviarMensaje
};
