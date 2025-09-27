// index.js - PETBIO WhatsApp Bot en Producción 🌐
// ===============================================

const fs = require('fs');
const path = require('path');
const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const QRCode = require('qrcode');
const express = require('express');
const mqtt = require('mqtt');

// ===============================
// 📁 Módulos propios
// ===============================
const saludoDelUsuario = require('./interaccion_del_bot/saludo_del_usuario');
const menuInicioModule = require('./interaccion_del_bot/menu_inicio');
const { iniciarRegistroMascota } = require('./interaccion_del_bot/registro_mascotas_bot');
const { iniciarRegistroUsuario } = require('./interaccion_del_bot/registro_usuario_bot');

// ===============================
// 🌐 Healthcheck HTTP para Render
// ===============================
const app = express();
const PORT = process.env.PORT || 3000;
app.get('/health', (req, res) => res.send('✅ PETBIO Bot activo'));

// ===============================
// 📲 QR WhatsApp en PNG
// ===============================
const qrPath = path.join(__dirname, 'tmp_whatsapp_qr.png');

app.get('/qr', (req, res) => {
    if (fs.existsSync(qrPath)) {
        res.sendFile(qrPath);
    } else {
        res.status(404).send('❌ QR aún no generado');
    }
});

app.listen(PORT, () => console.log(`🌐 Healthcheck en puerto ${PORT}`));


/*

// ===============================
// 📶 Conexión MQTT (CloudMQTT)
// ===============================
const mqttClient = mqtt.connect('mqtt://duck.lmq.cloudamqp.com:1883', {
    username: 'xdagoqsj',
    password: 'flwvAT0Npo8piPIZehUr_PnKPrs1JJ8L',
    reconnectPeriod: 5000, // reintento cada 5s
});

mqttClient.on('connect', () => console.log('✅ Conectado a MQTT Broker (CloudMQTT)'));
mqttClient.on('error', err => console.error('❌ Error MQTT:', err));

*/


// whatsapp_bot/config/mqttConfig.js
//const mqtt = require("mqtt");

// Las variables de entorno que configuras en Render
const MQTT_HOST = process.env.MQTT_HOST || "duck.lmq.cloudamqp.com";
const MQTT_PORT = process.env.MQTT_PORT || 1883;  // o 8883 si usas TLS
const MQTT_USER = process.env.MQTT_USER || "xdagoqsj:xdagoqsj";
const MQTT_PASS = process.env.MQTT_PASS || "flwvAT0Npo8piPIZehUr_PnKPrs1JJ8L";

// Construir la URL
const MQTT_URL = `mqtt://${MQTT_HOST}:${MQTT_PORT}`;

const client = mqtt.connect(MQTT_URL, {
  username: MQTT_USER,
  password: MQTT_PASS,
  connectTimeout: 30 * 1000, // 30s
  reconnectPeriod: 5000      // reintentos cada 5s
});

client.on("connect", () => {
  console.log("✅ Conectado a MQTT");
});

client.on("error", (err) => {
  console.error("❌ Error MQTT:", err);
});

module.exports = client;



// ===============================
// 🤖 Cliente WhatsApp
// ===============================
const client = new Client({
    puppeteer: {
        headless: true,
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage'
        ]
    },
    authStrategy: new LocalAuth({ dataPath: './.wwebjs_auth' })
});

// ===============================
// 📲 Eventos WhatsApp
// ===============================
client.on('qr', async qr => {
    console.log('📲 Escanea este código QR para vincular tu número:');
    qrcode.generate(qr, { small: true });

    // Guardar QR en PNG
    try {
        await QRCode.toFile(qrPath, qr, { width: 300 });
        console.log(`✅ QR guardado en PNG: ${qrPath}`);
    } catch (err) {
        console.error('❌ Error generando QR PNG:', err);
    }
});

client.on('ready', () => console.log('✅ Cliente WhatsApp listo y conectado!'));

client.on('disconnected', reason => {
    console.error('⚠️ Cliente desconectado:', reason);
    setTimeout(() => client.initialize(), 5000);
});

// ===============================
// 💾 Manejo de sesiones locales
// ===============================
const sessionsDir = path.join(__dirname, 'sessions');
if (!fs.existsSync(sessionsDir)) fs.mkdirSync(sessionsDir, { recursive: true });
const SESSION_TTL = 1000 * 60 * 60 * 12; // 12 horas

// ===============================
// 📡 Comandos globales
// ===============================
const CMD_MENU = ['menu', 'inicio', 'volver', 'home'];
const CMD_CANCEL = ['cancelar', 'salir', 'stop', 'terminar', 'abortar'];

// ===============================
// 💬 Flujo principal de mensajes
// ===============================
client.on('message', async msg => {
    try {
        const sessionFile = path.join(sessionsDir, `${msg.from}.json`);
        let session = {};

        if (fs.existsSync(sessionFile)) {
            session = JSON.parse(fs.readFileSync(sessionFile));
            if (Date.now() - (session.lastActive || 0) > SESSION_TTL) session = {};
        }

        session.type = session.type || 'menu_inicio';
        session.step = session.step || null;
        session.data = session.data || {};
        session.lastActive = Date.now();
        session.lastGreeted = session.lastGreeted || false;

        const userMsg = (msg.body || '').trim();
        const lcMsg = userMsg.toLowerCase();

        // Comando CANCELAR
        if (CMD_CANCEL.includes(lcMsg)) {
            try { fs.unlinkSync(sessionFile); } catch (e) {}
            await msg.reply('🛑 Registro cancelado. Escribe *menu* para volver al inicio.');
            return;
        }

        // Comando MENU
        if (CMD_MENU.includes(lcMsg)) {
            session.type = 'menu_inicio';
            session.step = null;
            session.data = {};
            session.lastActive = Date.now();
            session.lastGreeted = false;
            await saludoDelUsuario(msg, sessionFile);
            fs.writeFileSync(sessionFile, JSON.stringify(session));
            return;
        }

        // Router principal según tipo de sesión
        switch (session.type) {
            case 'menu_inicio': {
                const handleMenu = await menuInicioModule(msg, sessionFile, session);
                await handleMenu(userMsg);
                break;
            }
            case 'registro_usuario':
                await iniciarRegistroUsuario(msg, session, sessionFile);
                break;
            case 'registro_mascota':
                await iniciarRegistroMascota(msg, session, sessionFile, mqttClient);
                break;
            default:
                await msg.reply('🤖 No entendí. Escribe *menu* para volver al inicio o *cancelar* para salir.');
                break;
        }

        fs.writeFileSync(sessionFile, JSON.stringify(session));
    } catch (err) {
        console.error('❌ Error procesando mensaje:', err);
        try { await msg.reply('⚠️ Error interno. Escribe *menu* para reiniciar.'); } catch (_) {}
    }
});

// 🚀 Inicializar cliente WhatsApp
client.initialize();
