require('dotenv').config();
const mysql = require('mysql2/promise');
const mqtt = require('mqtt');
const { Pool } = require('pg');

// ===============================
// MYSQL
// ===============================
async function getMySQLConnection() {
  try {
    const connection = await mysql.createConnection({
      host: process.env.MYSQL_HOST || 'mysql_petbio_secure',
      port: process.env.MYSQL_PORT || 3310,
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

// ===============================
// MQTT - CloudMQTT (Producción / Render)
// ===============================

console.log("🔑 MQTT Cloud Config -> Broker:", process.env.MQTT_CLOUD_BROKER);
console.log("🔑 MQTT Cloud Config -> User:", process.env.MQTT_CLOUD_USER);
console.log("🔑 MQTT Cloud Config -> Pass:", process.env.MQTT_CLOUD_PASS ? "✅ Cargada" : "❌ No definida");

const mqttCloudUrl = process.env.MQTT_CLOUD_BROKER || 'mqtts://duck-01.lmq.cloudamqp.com:8883';
const mqttCloudOptions = {
  username: process.env.MQTT_CLOUD_USER || 'xdagoqsj',
  password: process.env.MQTT_CLOUD_PASS,
  reconnectPeriod: 5000, // reconexión automática cada 5s
  protocol: mqttCloudUrl.startsWith('mqtts') ? 'mqtts' : 'mqtt',
};

/* 
// Bloque antiguo comentado: ya no es necesario porque mqtt.js maneja reconexión automática
const mqttCloud = mqtt.connect(mqttCloudUrl, mqttCloudOptions);
mqttCloud.on('connect', () => console.log('✅ Conectado a CloudMQTT'));
mqttCloud.on('error', (err) => console.error('❌ Error CloudMQTT:', err.message));
*/

const mqttCloud = mqtt.connect(mqttCloudUrl, mqttCloudOptions);

mqttCloud.on('connect', () => console.log('✅ Conectado a CloudMQTT'));
mqttCloud.on('error', (err) => {
  console.error('❌ Error CloudMQTT:', err.message);
  // No cerramos la conexión, mqtt.js intentará reconectar automáticamente
});

mqttCloud.on('close', () => {
  console.warn('⚠️ Conexión MQTT cerrada, reconectando en 5s...');
  setTimeout(() => mqttCloud.reconnect(), 5000); // opcional, puedes confiar solo en reconnectPeriod
});



/*

require('dotenv').config();
const mysql = require('mysql2/promise');
const mqtt = require('mqtt');
const { Pool } = require('pg');

// ===============================
// MYSQL
// ===============================
async function getMySQLConnection() {
  try {
    const connection = await mysql.createConnection({
      host: process.env.MYSQL_HOST || 'mysql_petbio_secure',
      port: process.env.MYSQL_PORT || 3310,
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

// ===============================
// MQTT - CloudMQTT (Producción / Render)
// ===============================

console.log("🔑 MQTT Cloud Config -> Broker:", process.env.MQTT_CLOUD_BROKER);
console.log("🔑 MQTT Cloud Config -> User:", process.env.MQTT_CLOUD_USER);
console.log("🔑 MQTT Cloud Config -> Pass:", process.env.MQTT_CLOUD_PASS ? "✅ Cargada" : "❌ No definida");



const mqttCloudUrl = process.env.MQTT_CLOUD_BROKER || 'mqtts://duck-01.lmq.cloudamqp.com:8883';
const mqttCloudOptions = {
  username: process.env.MQTT_CLOUD_USER || 'xdagoqsj',
  password: process.env.MQTT_CLOUD_PASS,
  reconnectPeriod: 5000,
  protocol: mqttCloudUrl.startsWith('mqtts') ? 'mqtts' : 'mqtt',
};
/*
const mqttCloud = mqtt.connect(mqttCloudUrl, mqttCloudOptions);

mqttCloud.on('connect', () => console.log('✅ Conectado a CloudMQTT'));
mqttCloud.on('error', (err) => console.error('❌ Error CloudMQTT:', err.message));

*/

const mqttCloud = mqtt.connect(mqttCloudUrl, mqttCloudOptions);

mqttCloud.on('connect', () => console.log('✅ Conectado a CloudMQTT'));
mqttCloud.on('error', (err) => {
  console.error('❌ Error CloudMQTT:', err.message);
  // No cerrar la conexión, mqtt.js intentará reconectar automáticamente
});

mqttCloud.on('close', () => {
  console.warn('⚠️ Conexión MQTT cerrada, reconectando en 5s...');
  setTimeout(() => mqttCloud.reconnect(), 5000);
});

*/

// ===============================
// MQTT - Mosquitto Local DEV
// ===============================
const mqttLocalDev = mqtt.connect(
  process.env.MQTT_LOCAL_DEV_BROKER || 'mqtt://mosquitto-stack:1883',
  {
    username: process.env.MQTT_LOCAL_DEV_USER || 'petbio_user_dev',
    password: process.env.MQTT_LOCAL_DEV_PASS || 'petbio2025_dev!',
    reconnectPeriod: 5000,
  }
);

mqttLocalDev.on('connect', () => console.log('✅ Conectado a Mosquitto DEV'));
mqttLocalDev.on('error', (err) => console.error('❌ Error Mosquitto DEV:', err.message));

// ===============================
// MQTT - Mosquitto Local PROD
// ===============================
const mqttLocalProd = mqtt.connect(
  process.env.MQTT_LOCAL_BROKER || 'mqtt://mosquitto-stack:1883',
  {
    username: process.env.MQTT_LOCAL_USER || 'petbio_user',
    password: process.env.MQTT_LOCAL_PASS || 'petbio2025!',
    reconnectPeriod: 5000,
  }
);

mqttLocalProd.on('connect', () => console.log('✅ Conectado a Mosquitto PROD'));
mqttLocalProd.on('error', (err) => console.error('❌ Error Mosquitto PROD:', err.message));

// ===============================
// SUPABASE (Postgres)
// ===============================
const supabasePool = new Pool({
  host: process.env.SUPABASE_HOST || 'db.jbsxvonnrahhfffeacdy.supabase.co',
  port: process.env.SUPABASE_PORT || 5432,
  user: process.env.SUPABASE_USER || 'postgres',
  password: process.env.SUPABASE_PASS || 'R00t_Segura_2025!',
  database: process.env.SUPABASE_DB || 'postgres',
  ssl: { rejectUnauthorized: false },
});

// Función para probar conexión Supabase
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

// ===============================
// EXPORTS
// ===============================
module.exports = {
  getMySQLConnection,
  mqttCloud,
  mqttLocalDev,
  mqttLocalProd,
  supabasePool,
  testSupabaseConnection,
};
