// ==========================================================
// 🌐 config.js — Configuración central PETBIO Bot (Docker-Ready)
// ==========================================================
require('dotenv').config();
const mysql = require('mysql2/promise');
const mqtt = require('mqtt');
const { Pool } = require('pg');

// ==========================================================
// ✅ MYSQL — Conexión principal PETBIO
// ==========================================================
async function getMySQLConnection() {
  try {
    const connection = await mysql.createConnection({
      host: process.env.MYSQL_HOST || '127.0.0.1', // 🔹 Desde el 5 oct se usa 127.0.0.1:3306
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
// ✅ MQTT — LavinMQ / CloudAMQP (Producción)
// ==========================================================
// 👉 IMPORTANTE: En LavinMQ, el formato de username es "usuario:vhost"
//    En tu caso: username = "xdagoqsj:xdagoqsj"
//    Broker: duck.lmq.cloudamqp.com
//    Puerto seguro (TLS): 8883

const mqttCloudUrl = process.env.MQTT_CLOUD_BROKER || 'mqtts://duck.lmq.cloudamqp.com:8883';

const mqttCloudOptions = {
  username: process.env.MQTT_CLOUD_USER || 'xdagoqsj:xdagoqsj', // 🔹 formato correcto LavinMQ
  password: process.env.MQTT_CLOUD_PASS || 'flwvAT0Npo8piPIZehUr_PnKPrs1JJ8L',
  protocol: 'mqtts',
  reconnectPeriod: Number(process.env.MQTT_RECONNECT_MS) || 5000, // reintento cada 5s
  connectTimeout: Number(process.env.MQTT_CONNECT_TIMEOUT_MS) || 30000, // timeout 30s
  rejectUnauthorized: false, // 🔓 evita error con certificados en Render
  clientId: (process.env.MQTT_CLIENT_ID || 'petbio_bot_') + Math.random().toString(16).substring(2, 8), // 🆔 único
};

console.log('🔑 MQTT LavinMQ Config ->', {
  broker: mqttCloudUrl,
  user: mqttCloudOptions.username,
  pass: mqttCloudOptions.password ? '✅ cargada' : '❌ no definida',
  protocol: mqttCloudOptions.protocol,
  clientId: mqttCloudOptions.clientId,
});

// 🔌 Conexión MQTT
const mqttCloud = mqtt.connect(mqttCloudUrl, mqttCloudOptions);

// 🛠️ Eventos de conexión MQTT
mqttCloud.on('connect', () => console.log('✅ Conectado a LavinMQ (CloudAMQP)'));
mqttCloud.on('reconnect', () => console.log('🔁 Reintentando conexión MQTT...'));
mqttCloud.on('close', () => console.warn('⚠️ Conexión MQTT cerrada'));
mqttCloud.on('offline', () => console.warn('⚠️ MQTT sin conexión'));
mqttCloud.on('error', (err) => {
  console.error('❌ Error LavinMQ:', err?.message || err);
  if (err?.message?.includes('Not authorized')) {
    console.error('🚨 ERROR: Credenciales MQTT inválidas. Revisa MQTT_CLOUD_USER y MQTT_CLOUD_PASS en tu .env');
  }
});

// ==========================================================
// ✅ (Opcional) MQTT — Mosquitto Local DEV y PROD
// ==========================================================
// ⚠️ Descomentarlos solo si necesitas pruebas locales fuera de Render
/*
const mqttLocalDev = mqtt.connect(process.env.MQTT_LOCAL_DEV_BROKER || 'mqtt://127.0.0.1:1883', {
  username: process.env.MQTT_LOCAL_DEV_USER || 'petbio_user_dev',
  password: process.env.MQTT_LOCAL_DEV_PASS || 'petbio2025_dev!',
  reconnectPeriod: 5000,
});
mqttLocalDev.on('connect', () => console.log('✅ Conectado a Mosquitto DEV'));
mqttLocalDev.on('error', (err) => console.error('❌ Error Mosquitto DEV:', err.message));

const mqttLocalProd = mqtt.connect(process.env.MQTT_LOCAL_BROKER || 'mqtt://127.0.0.1:1883', {
  username: process.env.MQTT_LOCAL_USER || 'petbio_user',
  password: process.env.MQTT_LOCAL_PASS || 'petbio2025!',
  reconnectPeriod: 5000,
});
mqttLocalProd.on('connect', () => console.log('✅ Conectado a Mosquitto PROD'));
mqttLocalProd.on('error', (err) => console.error('❌ Error Mosquitto PROD:', err.message));
*/

// ==========================================================
// ✅ SUPABASE (PostgreSQL interno del bot)
// ==========================================================
const supabasePool = new Pool({
  host: process.env.SUPABASE_HOST || 'db.jbsxvonnrahhfffeacdy.supabase.co',
  port: process.env.SUPABASE_PORT || 5432,
  user: process.env.SUPABASE_USER || 'postgres',
  password: process.env.SUPABASE_PASS || 'R00t_Segura_2025!',
  database: process.env.SUPABASE_DB || 'postgres',
  ssl: { rejectUnauthorized: false },
});

// 🔍 Verifica conexión Supabase
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
// ✅ EXPORTAR COMPONENTES GLOBALES
// ==========================================================
module.exports = {
  getMySQLConnection,
  mqttCloud,
  supabasePool,
  testSupabaseConnection,
  // mqttLocalDev,
  // mqttLocalProd,
};
