// test_mqtt.js
const mqtt = require('mqtt');

const client = mqtt.connect('mqtts://duck.lmq.cloudamqp.com:8883', {
  username: 'xdagoqsj:xdagoqsj',
  password: 'flwvAT0Npo8piPIZehUr_PnKPrs1JJ8L',
  protocolVersion: 4
});

client.on('connect', () => {
  console.log('✅ Conectado al broker');
  client.subscribe('test');
});

client.on('message', (topic, message) => {
  console.log(`📩 ${topic}: ${message.toString()}`);
});

client.on('error', (err) => {
  console.error('❌ Error:', err.message);
});
