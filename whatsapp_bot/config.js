require('dotenv').config();
const mysql = require('mysql2/promise');
const mqtt = require('mqtt');
const { mqttCloud } = require('./config');
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
// MQTT - CloudMQTT (Render / Producción)
// ===============================
/*
const mqttCloud = mqtt.connect(
  process.env.MQTT_CLOUD_BROKER || 'mqtt://duck-01.lmq.cloudamqp.com:1883',
  {
    username: process.env.MQTT_CLOUD_USER || 'xdagoqsj:xdagoqsj',
//    password: process.env.MQTT_CLOUD_PASS || 'flwvAT0Npo8piPIZehUr_PnKPrs1JJ8L',
    username: process.env.MQTT_CLOUD_USER || 'xdagoqsj', // ❗️sin el prefijo xdagoqsj:
  reconnectPeriod: 5000,
  }
);
mqttCloud.on('connect', () => console.log('✅ Conectado a CloudMQTT'));
mqttCloud.on('error', (err) => console.error('❌ Error CloudMQTT:', err.message));
*/

// ===============================
// MQTT - CloudMQTT (Render / Producción)
// ===============================


//AGREGAMOS DEPURACION DE MQTT.CLOUD
console.log("🔑 MQTT Config -> Broker:", process.env.MQTT_CLOUD_BROKER);
console.log("🔑 MQTT Config -> User:", process.env.MQTT_CLOUD_USER);
console.log("🔑 MQTT Config -> Pass:", process.env.MQTT_CLOUD_PASS ? "✅ Cargada" : "❌ No definida");


/*
const mqttCloud = mqtt.connect(
  process.env.MQTT_CLOUD_BROKER || 'mqtt://duck-01.lmq.cloudamqp.com:1883',
  {
    username: process.env.MQTT_CLOUD_USER || 'xdagoqsj:xdagoqsj',
    password: process.env.MQTT_CLOUD_PASS || 'flwvAT0Npo8piPIZehUr_PnKPrs1JJ8L',
    reconnectPeriod: 5000,
  }
);
*/
/*
const mqttCloud = mqtt.connect(
  process.env.MQTT_CLOUD_BROKER || 'mqtt://duck-01.lmq.cloudamqp.com:1883',
  {
    username: process.env.MQTT_CLOUD_USER,
    password: process.env.MQTT_CLOUD_PASS,
    reconnectPeriod: 5000,
  }
);*/
//*
const mqttCloud = mqtt.connect(process.env.MQTT_CLOUD_BROKER ||'mqtt://duck-01.lmq.cloudamqp.com:8883',  {
    username: process.env.MQTT_CLOUD_USER,
    password: process.env.MQTT_CLOUD_PASS,
    reconnectPeriod: 5000,
    protocol: 'mqtts', // 👈 importante para puerto 8883
}); */



const mqttCloud = mqtt.connect(
  process.env.MQTT_CLOUD_BROKER || 'mqtt://duck.lmq.cloudamqp.com:1883',
  {
    username: process.env.MQTT_CLOUD_USER || 'xdagoqsj',
    password: process.env.MQTT_CLOUD_PASS,
    reconnectPeriod: 5000
  }
);




mqttCloud.on('connect', () => console.log('✅ Conectado a CloudMQTT'));
mqttCloud.on('error', (err) => console.error('❌ Error CloudMQTT:', err.message));


/* const mqttCloud = mqtt.connect(
  process.env.MQTT_CLOUD_BROKER || 'mqtts://duck.lmq.cloudamqp.com:8883/xdagoqsj', 
  {
    username: process.env.MQTT_CLOUD_USER || 'xdagoqsj',
    password: process.env.MQTT_CLOUD_PASS,
    reconnectPeriod: 5000
  }
);
Opción 2 — Si necesitas TLS (puerto 8883)

Solo si tu broker sí lo soporta:


/* const mqttCloud = mqtt.connect(
  process.env.MQTT_CLOUD_BROKER || 'mqtts://duck.lmq.cloudamqp.com:8883/xdagoqsj', 
  {
    username: process.env.MQTT_CLOUD_USER || 'xdagoqsj',
    password: process.env.MQTT_CLOUD_PASS,
    reconnectPeriod: 5000
  }
);
  */





// ===============================
// MQTT - Mosquitto Local (DEV)
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
mqttLocalDev.on('error', (err) =>
  console.error('❌ Error Mosquitto DEV:', err.message)
);

// ===============================
// MQTT - Mosquitto Local (PROD)
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
mqttLocalProd.on('error', (err) =>
  console.error('❌ Error Mosquitto PROD:', err.message)
);

*/

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
//  mqttLocalDev,
//  mqttLocalProd,
  supabasePool,
  testSupabaseConnection,
};
