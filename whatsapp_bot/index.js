// ==========================================================
// 🤖 PETBIO WhatsApp Bot + MQTT LavinMQ + Supabase
// ==========================================================
// 🧠 Versión completa — Compatible con Render & Docker
// ==========================================================

require('dotenv').config();
const path = require('path');
const fs = require('fs');
const express = require('express');
const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const QRCode = require('qrcode');
const { createClient } = require('@supabase/supabase-js');

// ==========================================================
// 🌐 CONFIGURACIÓN SUPABASE
// ==========================================================
const supabaseUrl = 'https://jbsxvonnrahhfffeacdy.supabase.co';
const supabaseKey = process.env.SUPABASE_KEY;
if (!supabaseKey) {
  console.error("❌ ERROR: No se encontró SUPABASE_KEY en .env");
  process.exit(1);
}
const supabase = createClient(supabaseUrl, supabaseKey);
console.log("🔑 Supabase inicializado correctamente");

// ==========================================================
// 📡 CONFIGURACIÓN MQTT (LavinMQ CloudAMQP)
// ==========================================================
const mqtt = require('mqtt');
const MQTT_HOST = process.env.MQTT_HOST || 'duck.lmq.cloudamqp.com';
const MQTT_PORT = process.env.MQTT_PORT || 8883;
const MQTT_USER = process.env.MQTT_USER || 'xdagoqsj:xdagoqsj';
const MQTT_PASS = process.env.MQTT_PASS;
const MQTT_TOPIC = process.env.MQTT_TOPIC || 'petbio/test';

let mqttCloud;
try {
  const mqttOptions = {
    username: MQTT_USER,
    password: MQTT_PASS,
    port: MQTT_PORT,
    protocol: 'mqtts',
    connectTimeout: 5000,
    keepalive: 60,
    reconnectPeriod: 5000
  };
  console.log(`📡 Conectando a LavinMQ MQTT (${MQTT_HOST}:${MQTT_PORT})...`);
  mqttCloud = mqtt.connect(`mqtts://${MQTT_HOST}`, mqttOptions);

  mqttCloud.on('connect', () => {
    console.log('✅ Conectado a LavinMQ MQTT');
    mqttCloud.subscribe(MQTT_TOPIC, () =>
      console.log(`📩 Suscrito al tópico: ${MQTT_TOPIC}`)
    );
  });
  mqttCloud.on('message', (topic, msg) =>
    console.log(`📨 [${topic}] Mensaje recibido: ${msg.toString()}`)
  );
  mqttCloud.on('error', err => console.error('⚠️ Error MQTT:', err.message));
  mqttCloud.on('close', () => console.warn('🔌 MQTT desconectado.'));
} catch (err) {
  console.warn('⚠️ MQTT no configurado:', err.message);
}

// ==========================================================
// 📁 ASEGURAR DIRECTORIO DE SESIÓN
// ==========================================================
const sessionDir = '/tmp/session';
if (!fs.existsSync(sessionDir)) {
  fs.mkdirSync(sessionDir, { recursive: true });
  console.log('📁 Carpeta de sesión creada:', sessionDir);
}

// ==========================================================
// 🧠 AUTENTICACIÓN SUPABASE (Cargar/Guardar sesión)
// ==========================================================
const { loadSession, saveSession } = require('./SupabaseAuth');

// ==========================================================
// 🚀 CLIENTE WHATSAPP
// ==========================================================
let whatsappClient;
(async () => {
  const sessionFile = await loadSession();

  whatsappClient = new Client({
    puppeteer: {
      headless: true,
      args: [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-dev-shm-usage'
      ]
    },
    authStrategy: new LocalAuth({
      dataPath: sessionFile ? path.dirname(sessionFile) : sessionDir
    })
  });

  whatsappClient.on('qr', async qr => {
    console.log('📲 Escanea este código QR para vincular tu número:');
    qrcode.generate(qr, { small: true });
    const qrPath = path.join(sessionDir, 'whatsapp_qr.png');
    await QRCode.toFile(qrPath, qr, { width: 300 });
  });

  whatsappClient.on('ready', async () => {
    console.log('✅ Cliente WhatsApp listo y conectado!');
    await saveSession();
  });

  whatsappClient.on('disconnected', async reason => {
    console.warn('⚠️ Cliente desconectado:', reason);
    await saveSession();
    setTimeout(() => whatsappClient.initialize(), 5000);
  });

  whatsappClient.initialize();
})();

// ==========================================================
// 🌐 EXPRESS HEALTHCHECK + QR
// ==========================================================
const app = express();
const PORT = process.env.PORT || 3000;
const qrPath = path.join(sessionDir, 'whatsapp_qr.png');

app.get('/health', (req, res) => {
  res.json({
    status: '✅ PETBIO Bot activo',
    supabase: !!supabaseKey,
    mqtt: mqttCloud?.connected || false,
    whatsapp: whatsappClient?.info ? "✅ Conectado" : "⏳ Esperando conexión"
  });
});

app.get('/qr', (req, res) => {
  if (fs.existsSync(qrPath)) res.sendFile(qrPath);
  else res.status(404).send('❌ QR aún no generado');
});

app.listen(PORT, () => console.log(`🌐 Healthcheck activo en puerto ${PORT}`));

// ==========================================================
// 💬 INTERACCIÓN DEL BOT
// ==========================================================
const saludoDelUsuario = require('./interaccion_del_bot/saludo_del_usuario');
const menuInicioModule = require('./interaccion_del_bot/menu_inicio');
const { iniciarRegistroMascota } = require('./interaccion_del_bot/registro_mascotas_bot');
const { iniciarRegistroUsuario } = require('./interaccion_del_bot/registro_usuario_bot');
const { iniciarSuscripciones } = require('./interaccion_del_bot/suscripciones_cuidadores_bot');
const historiaClinicaBot = require('./interaccion_del_bot/historia_clinica_bot');
const crearCitaBot = require('./interaccion_del_bot/crear_cita_bot');
const { procesarSuscripcion } = require('./interaccion_del_bot/tarifas_menu');

// ==========================================================
// 🧠 SESIONES USUARIO EN SUPABASE
// ==========================================================
const SESSION_TTL = 1000 * 60 * 60 * 12; // 12h

const getSession = async id => {
  try {
    const { data } = await supabase.from('sessions').select('*').eq('user_id', id).single();
    if (data && Date.now() - new Date(data.last_active).getTime() < SESSION_TTL) {
      return JSON.parse(data.data);
    }
  } catch (err) {
    console.error('⚠️ Error obteniendo sesión:', err.message);
  }
  return {};
};

const saveUserSession = async (id, session) => {
  try {
    await supabase.from('sessions').upsert({
      user_id: id,
      data: JSON.stringify(session),
      last_active: new Date()
    });
  } catch (err) {
    console.error('⚠️ Error guardando sesión:', err.message);
  }
};

// ==========================================================
// 📩 EVENTO PRINCIPAL DE MENSAJE
// ==========================================================
whatsappClient?.on('message', async msg => {
  try {
    let session = await getSession(msg.from);
    session.type = session.type || 'menu_inicio';
    session.data = session.data || {};
    const userMsg = msg.body.trim().toLowerCase();

    if (['hola', 'hey', 'buenas'].some(s => userMsg.includes(s))) {
      await msg.reply('👋 ¡Hola! Soy *PETBIO Bot*. Escribe *menu* para continuar.');
      await saveUserSession(msg.from, session);
      return;
    }

    if (userMsg === 'cancelar') {
      await supabase.from('sessions').delete().eq('user_id', msg.from);
      await msg.reply('🛑 Proceso cancelado. Escribe *menu* para volver al inicio.');
      return;
    }

    if (userMsg === 'menu') {
      await saludoDelUsuario(msg);
      session.type = 'menu_inicio';
      await saveUserSession(msg.from, session);
      return;
    }

    switch (session.type) {
      case 'menu_inicio':
        const menuHandler = await menuInicioModule(msg, null, session);
        await menuHandler(msg.body);
        break;
      case 'registro_usuario':
        await iniciarRegistroUsuario(msg, session);
        break;
      case 'registro_mascota':
        await iniciarRegistroMascota(msg, session, mqttCloud);
        break;
      case 'suscripciones':
        await iniciarSuscripciones(msg, session);
        break;
      case 'historia_clinica':
        await historiaClinicaBot.procesarSolicitud(msg.from);
        break;
      case 'crear_cita':
        await crearCitaBot.procesarSolicitud(msg.from);
        break;
      case 'tarifas':
        const meses = parseInt(userMsg);
        if ([3, 6, 12].includes(meses)) {
          await msg.reply(procesarSuscripcion(meses));
        } else {
          await msg.reply("❌ Opción inválida. Responde con 3, 6 o 12 meses.");
        }
        break;
      default:
        await msg.reply('🤖 No entendí. Escribe *menu* para comenzar.');
    }

    await saveUserSession(msg.from, session);

  } catch (err) {
    console.error('⚠️ Error en el bot:', err);
    try { await msg.reply('⚠️ Ocurrió un error. Escribe *menu* para reiniciar.'); } catch (_) {}
  }
});

// ==========================================================
// 📊 MONITOREO DE MEMORIA
// ==========================================================
setInterval(() => {
  const used = process.memoryUsage();
  console.log(`🧠 Memoria usada: ${(used.rss / 1024 / 1024).toFixed(2)} MB`);
}, 15000);

// ==========================================================
// 🔚 FIN DEL SCRIPT PETBIO BOT
// ==========================================================
