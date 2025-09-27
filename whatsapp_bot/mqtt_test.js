require('dotenv').config(); // Si usas .env localmente

const mqtt = require('mqtt');

const MQTT_BROKER = process.env.MQTT_BROKER || "mqtt://duck-01.lmq.cloudamqp.com:1883";
const MQTT_USER = process.env.MQTT_USER || "tu_usuario_proporcionado_por_CloudMQTT";
const MQTT_PASS = process.env.MQTT_PASS || "tu_contraseña_proporcionada_por_CloudMQTT";

const client = mqtt.connect(MQTT_BROKER, {
  username: MQTT_USER,
  password: MQTT_PASS
});

client.on('connect', () => {
  console.log('✅ Conectado a MQTT correctamente');
  client.end();
});

client.on('error', (err) => {
  console.error('❌ Error de conexión MQTT:', err.message);
  client.end();
});
