// config.js
require('dotenv').config(); // Permite usar un archivo .env localmente

// ------------------ MYSQL ------------------
const mysql = require('mysql2/promise');

async function getMySQLConnection() {
  const connection = await mysql.createConnection({
    host: process.env.MYSQL_HOST || 'mysql_petbio_secure', // contenedor o IP
    port: process.env.MYSQL_PORT || 3310,
    user: process.env.MYSQL_USER || 'root',
    password: process.env.MYSQL_PASSWORD || 'R00t_Segura_2025!',
    database: process.env.MYSQL_DATABASE || 'db__produccion_petbio_segura_2025',
  });
  return connection;
}

// ------------------ MQTT ------------------
const mqtt = require('mqtt');

const mqttClient = mqtt.connect(process.env.MQTT_BROKER || 'mqtt://duck-01.lmq.cloudamqp.com:1883', {
  username: process.env.MQTT_USER || 'xdagoqsj:xdagoqsj',
  password: process.env.MQTT_PASS || 'flwvAT0Npo8piPIZehUr_PnKPrs1JJ8L',
  connectTimeout: 30 * 1000,
  reconnectPeriod: 5000
});

mqttClient.on('connect', () => console.log('✅ Cliente MQTT conectado'));
mqttClient.on('error', (err) => console.error('❌ Error MQTT SB_RDR:', err.message));

// ------------------ EXPORTS ------------------
module.exports = {
  getMySQLConnection,
  mqttClient
};
