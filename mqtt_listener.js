const mysql = require('mysql2/promise');
const { mqttClient } = require('./config');

const dbConfig = {
  host: 'mysql_petbio_secure',
  user: 'root',
  password: 'R00t_Segura_2025!',
  database: 'db__produccion_petbio_segura_2025',
  port: 3310
};

async function actualizarDB(data) {
  const connection = await mysql.createConnection(dbConfig);
  await connection.execute(
    'UPDATE registro_mascotas SET biometria_score = ?, biometria_clase = ? WHERE id = ?',
    [data.score, data.clase, data.id_mascota]
  );
  await connection.end();
}

mqttClient.on('message', async (topic, message) => {
  const data = JSON.parse(message.toString());
  console.log('📥 Resultado biometría recibido:', data);
  await actualizarDB(data);
});

// Suscribirse al topic de resultados
mqttClient.subscribe('resultado_mascota/#', err => {
  if (!err) console.log('✅ Listener MQTT biometría activo');
});
