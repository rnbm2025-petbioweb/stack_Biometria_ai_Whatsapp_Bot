// index.js - PETBIO WhatsApp Bot en Producción 🌐
// ===============================================

const fs = require('fs');
const path = require('path');
const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
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
app.listen(PORT, () => console.log(`🌐 Healthcheck en puerto ${PORT}`));

// ===============================
// 📶 Conexión MQTT
// ===============================
const mqttClient = mqtt.connect(process.env.MQTT_BROKER || 'mqtt://127.0.0.1:1883', {
  username: process.env.MQTT_USER || 'petbio_user',
  password: process.env.MQTT_PASS || 'petbio2025!',
  reconnectPeriod: 5000, // reintento cada 5s
});
mqttClient.on('connect', () => console.log('✅ Conectado a MQTT Broker'));
mqttClient.on('error', err => console.error('❌ Error MQTT:', err));

// ===============================
// 🤖 Cliente WhatsApp
// ===============================
const tmpProfileDir = `/tmp/wwebjs_${Date.now()}`;

const client = new Client({
  authStrategy: new LocalAuth({
    dataPath: process.env.WWEBJS_AUTH_PATH || path.join(__dirname, '.wwebjs_auth'),
  }),
  puppeteer: {
    headless: true,
    executablePath: '/usr/bin/chromium', // Chromium del sistema
    args: [
      '--no-sandbox',
      '--disable-setuid-sandbox',
      '--disable-extensions',
      '--disable-dev-shm-usage',
      '--disable-gpu',
      '--single-process',
      '--no-zygote',
      `--user-data-dir=${tmpProfileDir}`,
    ],
  },
});

// ===============================
// 📲 Eventos WhatsApp
// ===============================
client.on('qr', qr => {
  console.log('📲 Escanea este código QR para vincular tu número:');
  qrcode.generate(qr, { small: true });
});

client.on('ready', () => console.log('✅ Cliente WhatsApp listo y conectado!'));

client.on('disconnected', reason => {
  console.error('⚠️ Cliente desconectado:', reason);
  try { fs.rmSync(tmpProfileDir, { recursive: true, force: true }); } catch (e) {}
  setTimeout(() => client.initialize(), 5000);
});

// ===============================
// 💾 Manejo de sesiones
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
