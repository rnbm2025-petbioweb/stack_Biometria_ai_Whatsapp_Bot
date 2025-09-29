const { Client } = require('whatsapp-web.js');
const fs = require('fs');
const path = require('path');

const SESSION_FILE_PATH = path.join(__dirname, 'session.json');
let sessionData = null;

// Cargar sesión si existe
if (fs.existsSync(SESSION_FILE_PATH)) {
    sessionData = require(SESSION_FILE_PATH);
    console.log('🔑 Sesión existente cargada.');
}

const client = new Client({
    session: sessionData
});

// Generar QR si no hay sesión
client.on('qr', (qr) => {
    console.log('📲 Escanea este QR con tu teléfono:');
    console.log(qr);
});

// Guardar sesión
client.on('authenticated', (session) => {
    fs.writeFileSync(SESSION_FILE_PATH, JSON.stringify(session));
    console.log('✅ Sesión guardada correctamente.');
});

// Manejo de fallo de autenticación
client.on('auth_failure', () => {
    console.log('❌ Falló la autenticación. Borrando sesión antigua...');
    if (fs.existsSync(SESSION_FILE_PATH)) fs.unlinkSync(SESSION_FILE_PATH);
});

// Listo
client.on('ready', () => {
    console.log('🎉 WhatsApp Bot listo y conectado.');
});

client.initialize();

module.exports = client;
