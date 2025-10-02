const mqtt = require('mqtt');

const client = mqtt.connect('mqtts://duck.lmq.cloudamqp.com:8883', {
  username: 'xdagoqsj:xdagoqsj',
  password: 'flwvAT0Npo8piPIZehUr_PnKPrs1JJ8L'
});

client.on('connect', () => {
  console.log('✅ Conectado al broker!');
  client.subscribe('test', () => console.log('📡 Suscrito a "test"'));
});

client.on('message', (topic, msg) => {
  console.log(`📩 Mensaje en ${topic}: ${msg.toString()}`);
});

client.on('error', err => {
  console.error('❌ Error MQTT:', err.message);
});
