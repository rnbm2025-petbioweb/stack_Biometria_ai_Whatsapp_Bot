// ==========================================================
// ⚙️ config.js - Configuración central PETBIO BOT
// ==========================================================
// 🧠 Compatible con Render / Docker / LavinMQ (CloudAMQP)
// ==========================================================

require('dotenv').config();
const mysql = require('mysql2/promise');
const mqtt = require('mqtt');
const { Pool } = require('pg');

// ==========================================================
// ✅ MYSQL - Conexión base de datos principal
// ==========================================================
async function getMySQLConnection() {
  try {
    const connection = await mysql.createConnection({
      host: process.env.MYSQL_HOST || '127.0.0.1',
      port: process.env.MYSQL_PORT || '3306',
      user: process.env.MYSQL_USER || 'root',
      password: process.env.MYSQL_PASSWORD || 'R00t_Segura_2025!',
      database: process.env.MYSQL_DATABASE || 'db__produccion_petbio_segura_2025',
    });
    console.log('✅ Conectado a MySQL');
    return connection;
  } catch (err) {
    console.error('❌ Error MySQL:', err.message);
    throw err;
  }
}

// ==========================================================
// ✅ MQTT CLOUD (LavinMQ / CloudAMQP)
// ==========================================================
// ⚠️ Username debe ser SIN vhost. CloudAMQP usa "usuario:vhost" solo en AMQP.
// ==========================================================
const mqttCloudUrl = process.env.MQTT_CLOUD_BROKER || 'mqtts://duck.lmq.cloudamqp.com:8883';

const mqttCloudOptions = {
  username: process.env.MQTT_CLOUD_USER || 'xdagoqsj',
  password: process.env.MQTT_CLOUD_PASS || 'TU_PASSWORD_DE_LAVINMQ',
  protocol: 'mqtts',
  reconnectPeriod: Number(process.env.MQTT_RECONNECT_MS) || 5000,
  connectTimeout: Number(process.env.MQTT_CONNECT_TIMEOUT_MS) || 30000,
  rejectUnauthorized: false, // ⚙️ Evita errores de certificado en Render
  clientId:
    (process.env.MQTT_CLIENT_ID || 'petbio_bot_') +
    Math.random().toString(16).substring(2, 8),
};

// ✅ Diagnóstico en consola
console.log('🔑 Configuración MQTT LavinMQ:', {
  broker: mqttCloudUrl,
  user: mqttCloudOptions.username,
  pass: mqttCloudOptions.password ? '✅ cargada' : '❌ no definida',
  clientId: mqttCloudOptions.clientId,
});

const mqttCloud = mqtt.connect(mqttCloudUrl, mqttCloudOptions);

// ==========================================================
// 🧠 Eventos MQTT Cloud
// ==========================================================
mqttCloud.on('connect', () => console.log('✅ Conectado a LavinMQ MQTT'));
mqttCloud.on('reconnect', () => console.log('🔁 Reintentando conexión MQTT...'));
mqttCloud.on('close', () => console.warn('⚠️ Conexión MQTT cerrada'));
mqttCloud.on('offline', () => console.warn('⚠️ MQTT offline'));
mqttCloud.on('error', err => {
  console.error('❌ Error MQTT:', err?.message || err);
  if (err?.message?.includes('Not authorized')) {
    console.error('🚨 ERROR: Credenciales MQTT inválidas. Revisa MQTT_CLOUD_USER y MQTT_CLOUD_PASS en tu .env');
  }
});

// ==========================================================
// ✅ SUPABASE (PostgreSQL Backend)
// ==========================================================
const supabasePool = new Pool({
  host: process.env.SUPABASE_HOST || 'db.jbsxvonnrahhfffeacdy.supabase.co',
  port: process.env.SUPABASE_PORT || 5432,
  user: process.env.SUPABASE_USER || 'postgres',
  password: process.env.SUPABASE_PASS || 'R00t_Segura_2025!',
  database: process.env.SUPABASE_DB || 'postgres',
  ssl: { rejectUnauthorized: false },
});

async function testSupabaseConnection() {
  try {
    const client = await supabasePool.connect();
    const res = await client.query('SELECT NOW()');
    console.log('✅ Conectado a Supabase, hora actual:', res.rows[0].now);
    client.release();
  } catch (err) {
    console.error('❌ Error al conectar a Supabase:', err.message);
  }
}

// ==========================================================
// ✅ EXPORTS
// ==========================================================
module.exports = {
  getMySQLConnection,
  mqttCloud,
  supabasePool,
  testSupabaseConnection,
};
