// suscripciones_cuidadores_bot.js
// ==========================================
// Bot PETBIO - Gestión de Suscripciones de Cuidadores
// ==========================================
// ✅ Funcionalidades:
// 1. Verifica si un usuario tiene suscripción activa en MySQL
// 2. Controla accesos gratuitos limitados a módulos (historia clínica, citas, etc.)
// 3. Publica mensajes vía MQTT (solo mqttCloud en Render)
// 4. Flujo de WhatsApp para mostrar menú de suscripciones
// 5. Guardado de estado de sesión compatible con index.js

const fs = require('fs');
const mysql = require('mysql2/promise');
const utils = require('./utils_bot'); // Función justificarTexto para WhatsApp
const { mqttCloud /*, mqttLocalDev, mqttLocalProd */ } = require('../config');

// =====================
// Configuración DB
// =====================
const dbConfig = {
  host: process.env.MYSQL_HOST || 'mysql_petbio_secure',
  user: process.env.MYSQL_USER || 'root',
  password: process.env.MYSQL_PASSWORD || 'R00t_Segura_2025!',
  database: process.env.MYSQL_DATABASE || 'db__produccion_petbio_segura_2025',
  port: Number(process.env.MYSQL_PORT) || 3310
};

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
  mqttCloud.on('connect', () => console.log('✅ Suscripciones Bot conectado a CloudMQTT'));
  mqttCloud.on('error', (err) => {
    console.error('❌ Error CloudMQTT:', err.message);
    mqttCloud.end(true);
  });
}

// =====================
// Contador de intentos por usuario y módulo
// =====================
let intentos = {}; // { usuarioId: { historia: n, cita: n } }

// =====================
// Verificar suscripción en DB
// =====================
async function verificarSuscripcion(id_usuario) {
  try {
    const conn = await mysql.createConnection(dbConfig);
    const [rows] = await conn.execute(
      `SELECT id FROM pago_suscripcion WHERE id_usuario = ? ORDER BY id DESC LIMIT 1`,
      [id_usuario]
    );
    await conn.end();
    return rows.length > 0; // true si tiene suscripción
  } catch (err) {
    console.error('❌ Error MySQL:', err.message);
    return false;
  }
}

// =====================
// Publicar mensaje vía MQTT
// =====================
function enviarMensaje(usuarioId, mensaje) {
  if (!mqttCloud || mqttCloud.disconnected) {
    console.warn('⚠️ MQTT no disponible, mensaje no enviado.');
    return;
  }
  const topic = `petbio/usuario/${usuarioId}`;
  mqttCloud.publish(topic, mensaje);
}

// =====================
// Control de acceso a módulos
// =====================
async function procesarAcceso(usuarioId, modulo) {
  const estaSuscrito = await verificarSuscripcion(usuarioId);

  // Si está suscrito, acceso total
  if (estaSuscrito) {
    enviarMensaje(usuarioId, `✅ Tienes suscripción activa. Acceso al módulo de ${modulo} garantizado.`);
    return true;
  }

  // Inicializar intentos
  if (!intentos[usuarioId]) intentos[usuarioId] = { historia: 0, cita: 0 };

  // Limitar a 3 accesos gratuitos por módulo
  if (intentos[usuarioId][modulo] < 3) {
    intentos[usuarioId][modulo]++;
    enviarMensaje(usuarioId, `⚠️ Acceso gratuito ${intentos[usuarioId][modulo]}/3 al módulo de ${modulo}.`);
    return true;
  } else {
    enviarMensaje(
      usuarioId,
      `❌ Has agotado tus 3 intentos gratuitos del módulo de ${modulo}.\n` +
      `Para seguir disfrutando de todos nuestros servicios (historia clínica, creación de citas, recordatorios, seguimiento de mascotas), por favor suscríbete. 🐾\n` +
      `👉 Ingresa a la sección de Suscripciones para completar tu registro.`
    );
    return false;
  }
}

// =====================
// Flujo de WhatsApp para menú de suscripciones
// =====================
async function iniciarSuscripciones(msg, session, sessionFile) {
  const step = session.step || 'menu';
  const data = session.data || {};
  const texto = (msg.body || '').trim();

  try {
    // Comando cancelar
    if (texto.toLowerCase() === 'cancelar') {
      session.type = 'menu_inicio';
      session.step = null;
      session.data = {};
      if (sessionFile) fs.writeFileSync(sessionFile, JSON.stringify(session));
      return msg.reply('✅ Proceso cancelado. Volviendo al menú...');
    }

    switch (step) {
      case 'menu':
        // Tarifas con descuentos
        const tarifaTrimestre = 25;
        const tarifaSemestre = (tarifaTrimestre * 2 * 0.97).toFixed(2);
        const tarifaAnual = (tarifaTrimestre * 4 * 0.93).toFixed(2);

        const menuTexto = `📌 *Suscripciones Cuidadores PETBIO*
💰 Trimestre: $${tarifaTrimestre}
💰 6 meses: $${tarifaSemestre} (3% de descuento)
💰 1 año: $${tarifaAnual} (7% de descuento)

👉 Para suscribirte, ingresa aquí: https://petbio.siac2025.com/suscripciones_cuidadores
`;

        await msg.reply(utils.justificarTexto(menuTexto, 40));
        session.step = 'menu';
        break;

      default:
        await msg.reply('❌ Paso desconocido. Volviendo al menú principal...');
        session.type = 'menu_inicio';
        session.step = null;
        session.data = {};
        break;
    }

    session.data = data;
    if (sessionFile) fs.writeFileSync(sessionFile, JSON.stringify(session));

  } catch (err) {
    console.error('❌ Error en flujo de suscripciones:', err);
    await msg.reply('❌ Ocurrió un error. Volviendo al menú principal...');
    session.type = 'menu_inicio';
    session.step = null;
    session.data = {};
    if (sessionFile) fs.writeFileSync(sessionFile, JSON.stringify(session));
  }
}

// =====================
// Exportar funciones
// =====================
module.exports = {
  suscripcionesSuscripciones: iniciarSuscripciones, // Para integrarse con index.js y el flujo principal
  procesarSuscripcion: procesarAcceso       // Para controlar intentos y verificar suscripción
};
