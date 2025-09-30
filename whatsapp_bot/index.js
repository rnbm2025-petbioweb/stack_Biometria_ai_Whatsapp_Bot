// index.js - PETBIO WhatsApp Bot Integrado 🌐
// ===============================================
require('dotenv').config();

const path = require('path');
const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const QRCode = require('qrcode');
const express = require('express');
const { createClient } = require('@supabase/supabase-js');
//console.log("🔑 Supabase Key cargada:", supabaseKey ? "✅ Sí" : "❌ No");

// ------------------ 🌐 Configuración Supabase ------------------
const supabaseUrl = 'https://jbsxvonnrahhfffeacdy.supabase.co';
const supabaseKey = process.env.SUPABASE_KEY;
const supabase = createClient(supabaseUrl, supabaseKey);
console.log("🔑 Supabase Key cargada:", supabaseKey ? "✅ Sí" : "❌ No");

// ------------------ 📡 Configuración MQTT ------------------
const { mqttCloud, mqttLocalDev, mqttLocalProd } = require('./config');

// ------------------ 🤖 Módulos propios ------------------
const saludoDelUsuario = require('./interaccion_del_bot/saludo_del_usuario');
const menuInicioModule = require('./interaccion_del_bot/menu_inicio');
const { iniciarRegistroMascota } = require('./interaccion_del_bot/registro_mascotas_bot');
const { iniciarRegistroUsuario } = require('./interaccion_del_bot/registro_usuario_bot');
const { iniciarSuscripciones } = require('./interaccion_del_bot/suscripciones_cuidadores_bot');
const historiaClinicaBot = require('./interaccion_del_bot/historia_clinica_bot');
const crearCitaBot = require('./interaccion_del_bot/crear_cita_bot');

// ------------------ 🌐 Healthcheck ------------------
const app = express();
const PORT = process.env.PORT || 3000;

app.get('/health', (req, res) => res.send('✅ PETBIO Bot activo'));

const qrPath = path.join(__dirname, 'tmp_whatsapp_qr.png');
app.get('/qr', (req, res) => {
  const fs = require('fs');
  if (fs.existsSync(qrPath)) res.sendFile(qrPath);
  else res.status(404).send('❌ QR aún no generado');
});

app.listen(PORT, () => console.log(`🌐 Healthcheck en puerto ${PORT}`));

/*   // COMENTADO POR QUE SE DESCONECTARON DEL RENDER LOS MOSQUITTO PROD Y DEV
// ------------------ 📶 Conexión MQTT ------------------
[mqttCloud, mqttLocalDev, mqttLocalProd].forEach((client, index) => {
  const name = index === 0 ? 'CloudMQTT' : index === 1 ? 'Mosquitto DEV' : 'Mosquitto PROD';
  client.on('connect', () => console.log(`✅ Conectado a ${name}`));
  client.on('error', (err) => {
    console.error(`❌ Error ${name}:`, err.message);
    client.end(true);
  });
});

*/

// ------------------ 📶 Conexión MQTT ------------------
// Solo usamos CloudMQTT por ahora
if (mqttCloud) {
  mqttCloud.on('connect', () => console.log('✅ Conectado a CloudMQTT'));
  mqttCloud.on('error', (err) => {
    console.error('❌ Error CloudMQTT:', err.message);
    mqttCloud.end(true);
  });
}


// ------------------ 🤖 Cliente WhatsApp ------------------
/*

const whatsappClient = new Client({
  puppeteer: { headless: true, args: ['--no-sandbox','--disable-setuid-sandbox','--disable-dev-shm-usage'] },
  authStrategy: new LocalAuth({ dataPath: './.wwebjs_auth' })
});
*/

const whatsappClient = new Client({
  puppeteer: {
    headless: true,
    executablePath: '/usr/bin/chromium', // 🔹 usa el Chromium del sistema
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage']
  },
  authStrategy: new LocalAuth({ dataPath: './.wwebjs_auth' })
});

// ------------------ 📲 Eventos WhatsApp ------------------
whatsappClient.on('qr', async qr => {
  console.log('📲 Escanea este código QR para vincular tu número:');
  qrcode.generate(qr, { small: true });
  try { await QRCode.toFile(qrPath, qr, { width: 300 }); }
  catch (err) { console.error('❌ Error generando QR PNG:', err); }
});

whatsappClient.on('ready', () => console.log('✅ Cliente WhatsApp listo y conectado!'));

whatsappClient.on('disconnected', async reason => {
  console.error('⚠️ Cliente desconectado:', reason);
  try { await whatsappClient.destroy(); } catch (_) {}
  setTimeout(() => whatsappClient.initialize(), 5000);
});

// ------------------ 🧠 Manejo de sesiones en Supabase ------------------
const SESSION_TTL = 1000 * 60 * 60 * 12; // 12 horas
const getSession = async (userId) => {
  const { data } = await supabase.from('sessions').select('*').eq('user_id', userId).single();
  if (data && Date.now() - new Date(data.last_active).getTime() < SESSION_TTL) return JSON.parse(data.data);
  return {};
};
const saveSession = async (userId, session) => {
  await supabase.from('sessions').upsert({ user_id: userId, data: JSON.stringify(session), last_active: new Date() });
};
const deleteSession = async (userId) => {
  await supabase.from('sessions').delete().eq('user_id', userId);
};

// ------------------ 📋 Comandos globales ------------------
const CMD_MENU = ['menu','inicio','volver','home'];
const CMD_CANCEL = ['cancelar','salir','stop','terminar','abortar'];

// ------------------ 💬 Flujo principal ------------------
whatsappClient.on('message', async msg => {
  try {
    let session = await getSession(msg.from);
    session.type = session.type || 'menu_inicio';
    session.step = session.step || null;
    session.data = session.data || {};
    session.lastActive = Date.now();
    session.lastGreeted = session.lastGreeted || false;

    const userMsg = (msg.body || '').trim();
    const lcMsg = userMsg.toLowerCase();

    // 🛑 CANCELAR
    if (CMD_CANCEL.includes(lcMsg)) {
      await deleteSession(msg.from);
      await msg.reply('🛑 Registro cancelado. Escribe *menu* para volver al inicio.');
      return;
    }

    // 📋 MENU
    if (CMD_MENU.includes(lcMsg)) {
      session.type = 'menu_inicio';
      session.step = null;
      session.data = {};
      session.lastActive = Date.now();
      session.lastGreeted = false;
      await saludoDelUsuario(msg, null);
      await saveSession(msg.from, session);
      return;
    }

    // 🔁 Router principal
    switch(session.type) {
      case 'menu_inicio':
        const handleMenu = await menuInicioModule(msg, null, session);
        await handleMenu(userMsg);
        break;
      case 'registro_usuario':
        await iniciarRegistroUsuario(msg, session, null);
        break;
/*      case 'registro_mascota':
        await iniciarRegistroMascota(msg, session, null, mqttLocalProd);
        break;
*/
      case 'registro_mascota':
        await iniciarRegistroMascota(msg, session, null, mqttCloud);
        break;
      case 'suscripciones':
        await iniciarSuscripciones(msg, session, null);
        break;
      case 'historia_clinica':
        await historiaClinicaBot.procesarSolicitud(msg.from);
        break;
      case 'crear_cita':
        await crearCitaBot.procesarSolicitud(msg.from);
        break;
      default:
        await msg.reply('🤖 No entendí. Escribe *menu* o *cancelar*.');
        break;
    }

    await saveSession(msg.from, session);
  } catch (err) {
    console.error('❌ Error procesando mensaje:', err);
    try { await msg.reply('⚠️ Error interno. Escribe *menu* para reiniciar.'); } catch (_) {}
  }
});

// ------------------ 📊 Monitoreo memoria ------------------
setInterval(() => {
  const used = process.memoryUsage().rss / 1024 / 1024;
  console.log(`📊 Memoria usada: ${used.toFixed(2)} MB`);
}, 10000);

// 🚀 Inicializar cliente WhatsApp
whatsappClient.initialize();
