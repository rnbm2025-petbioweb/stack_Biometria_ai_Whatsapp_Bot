require('dotenv').config();

console.log('🔍 VARIABLES MQTT LEÍDAS DESDE .env:\n');
console.log({
  BOT_MODE: process.env.BOT_MODE,
  MQTT_BROKER: process.env.MQTT_BROKER,
  MQTT_PORT: process.env.MQTT_PORT,
  MQTT_PROTOCOL: process.env.MQTT_PROTOCOL,
  MQTT_USER: process.env.MQTT_USER,
  MQTT_PASS: process.env.MQTT_PASS,
  MQTT_CLIENT_ID: process.env.MQTT_CLIENT_ID,
});
