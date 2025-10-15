// whatsapp_client.js
const { Client } = require('whatsapp-web.js');
const fs = require('fs');
const path = require('path');
const { mqttCloud } = require('./config.js'); // mismo cliente que menu_inicio.js

const SESSION_FILE_PATH = path.join(__dirname, 'session.json');
let sessionData = null;

// Cargar sesión si existe
if (fs.existsSync(SESSION_FILE_PATH)) {
    sessionData = require(SESSION_FILE_PATH);
    console.log('🔑 Sesión existente cargada.');
    publishMQTT("whatsapp_bot", "Sesión existente cargada", "bot");
}

const client = new Client({
    session: sessionData
});

// ==============================
// 📲 Manejo de QR
// ==============================
client.on('qr', (qr) => {
    console.log('📲 Escanea este QR con tu teléfono:');
    console.log(qr);
    publishMQTT("whatsapp_bot", "QR generado para autenticación", "bot");
});

// ==============================
// 🔑 Autenticación exitosa
// ==============================
client.on('authenticated', (session) => {
    fs.writeFileSync(SESSION_FILE_PATH, JSON.stringify(session));
    console.log('✅ Sesión guardada correctamente.');
    publishMQTT("whatsapp_bot", "Sesión guardada correctamente", "bot");
});

// ==============================
// ❌ Fallo de autenticación
// ==============================
client.on('auth_failure', () => {
    console.log('❌ Falló la autenticación. Borrando sesión antigua...');
    if (fs.existsSync(SESSION_FILE_PATH)) fs.unlinkSync(SESSION_FILE_PATH);
    publishMQTT("whatsapp_bot", "Falló la autenticación, sesión borrada", "bot");
});

// ==============================
// 🎉 Bot listo
// ==============================
client.on('ready', () => {
    console.log('🎉 WhatsApp Bot listo y conectado.');
    publishMQTT("whatsapp_bot", "WhatsApp Bot listo y conectado", "bot");
});

client.initialize();

// ==============================
// 📌 Función para publicar eventos en MQTT
// ==============================
function publishMQTT(topic, descripcion, usuario) {
    if (mqttCloud && mqttCloud.connected) {
        mqttCloud.publish(`petbio/${topic}`, JSON.stringify({
            usuario,
            descripcion,
            fecha: new Date().toISOString()
        }), { qos: 1 });
        console.log(`🔹 MQTT publicado: ${topic} -> ${descripcion}`);
    }
}

module.exports = client;
