// ==========================================================
// 🤖 PETBIO WhatsApp Bot + Supabase + MQTT LavinMQ
// ==========================================================
// 🧩 Versión optimizada para Render (2025-10)
// ==========================================================

require('dotenv').config();
const path = require('path');
const fs = require('fs');
const express = require('express');
const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const QRCode = require('qrcode');
const { createClient } = require('@supabase/supabase-js');
const mqtt = require('mqtt');
const puppeteer = require('puppeteer');

const { sincronizarBases } = require('./config');
sincronizarBases();



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
console.log("🔑 Supabase inicializado correctamente.");

// ==========================================================
// 📡 CONFIGURACIÓN MQTT (LavinMQ CloudAMQP)
// ==========================================================
const MQTT_HOST = process.env.MQTT_HOST || 'duck.lmq.cloudamqp.com';
const MQTT_PORT = process.env.MQTT_PORT || 8883;
const MQTT_USER = process.env.MQTT_USER;
const MQTT_PASS = process.env.MQTT_PASS;
const MQTT_TOPIC = process.env.MQTT_TOPIC || 'petbio/test';

let mqttCloud = null;

try {
  const mqttOptions = {
    username: MQTT_USER,
    password: MQTT_PASS,
    port: MQTT_PORT,
    protocol: 'mqtts',
    connectTimeout: 5000,
    keepalive: 60,
    reconnectPeriod: 10000
  };

  console.log(`📡 Conectando a LavinMQ (${MQTT_HOST}:${MQTT_PORT})...`);
  mqttCloud = mqtt.connect(`mqtts://${MQTT_HOST}`, mqttOptions);

 /* mqttCloud.on('connect', () => {
    console.log(`✅ MQTT conectado y suscrito a ${MQTT_TOPIC}`);
    mqttCloud.subscribe(MQTT_TOPIC);
  }); */

  // ==========================================================
// 📡 Conexión MQTT + sincronización con bases de datos
// ==========================================================
  const { mqttCloud, supabasePool, getMySQLConnection, testSupabaseConnection, sincronizarBases } = require('./config');

// Cuando MQTT se conecta, lanza sincronización de datos
  mqttCloud.on('connect', () => {
    console.log(`✅ MQTT conectado y suscrito a ${MQTT_TOPIC}`);
    mqttCloud.subscribe(MQTT_TOPIC);

  // 🔁 Inicia sincronización automática con las bases
   try {
     sincronizarBases();
     testSupabaseConnection();
   } catch (err) {
     console.error('⚠️ No se pudo iniciar la sincronización:', err.message);
   }
});
  

  mqttCloud.on('message', (topic, msg) =>
    console.log(`📨 [${topic}] ${msg.toString()}`)
  );

  mqttCloud.on('error', err =>
    console.error('⚠️ Error MQTT:', err.message)
  );

  mqttCloud.on('close', () =>
    console.warn('🔌 MQTT desconectado, reintentando...')
  );

} catch (err) {
  console.warn('⚠️ MQTT no configurado:', err.message);
}

// ==========================================================
// 🧠 FUNCIONES SUPABASE (sesiones)
// ==========================================================
const SESSION_TTL = 1000 * 60 * 60 * 12; // 12 horas

const getSession = async (userId) => {
  try {
    const { data } = await supabase.from('sessions').select('*').eq('user_id', userId).single();
    if (data && Date.now() - new Date(data.last_active).getTime() < SESSION_TTL) {
      return JSON.parse(data.data);
    }
  } catch (err) {
    console.error('⚠️ Error al obtener sesión:', err.message);
  }
  return {};
};

const saveUserSession = async (userId, session) => {
  try {
    await supabase.from('sessions').upsert({
      user_id: userId,
      data: JSON.stringify(session),
      last_active: new Date()
    });
  } catch (err) {
    console.error('⚠️ Error guardando sesión:', err.message);
  }
};

const deleteSession = async (userId) => {
  try {
    await supabase.from('sessions').delete().eq('user_id', userId);
  } catch (err) {
    console.error('⚠️ Error eliminando sesión:', err.message);
  }
};

// ==========================================================
// 📁 SESIÓN LOCAL DEL CLIENTE WHATSAPP
// ==========================================================
const sessionDir = '/tmp/session';
if (!fs.existsSync(sessionDir)) fs.mkdirSync(sessionDir, { recursive: true });

// ==========================================================
// 🧩 DETECCIÓN AUTOMÁTICA DE CHROME EN RENDER
// ==========================================================
let chromePath;
try {
  chromePath = process.env.PUPPETEER_EXECUTABLE_PATH || puppeteer.executablePath();
  if (!fs.existsSync(chromePath)) {
    const baseDir = '/opt/render/.cache/puppeteer/chrome/';
    const dirs = fs.readdirSync(baseDir);
    if (dirs.length > 0) {
      const latest = dirs.sort().pop();
      chromePath = path.join(baseDir, latest, 'chrome-linux64/chrome');
      console.log(`🔍 Chrome detectado automáticamente en: ${chromePath}`);
    }
  }
} catch (err) {
  console.warn('⚠️ No se pudo detectar Chrome automáticamente:', err.message);
}

if (!fs.existsSync(chromePath || '')) {
  console.error('❌ Chrome no encontrado en ninguna ruta conocida.');
  console.error('👉 Asegúrate de tener en el build command: npx puppeteer install chrome');
}

// ==========================================================
// 🤖 CLIENTE WHATSAPP (config Render-ready)
// ==========================================================
let whatsappClient;

try {
  whatsappClient = new Client({
    authStrategy: new LocalAuth({ dataPath: sessionDir }),
    puppeteer: {
      executablePath: chromePath,
      headless: true,
      args: [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-gpu',
        '--disable-dev-shm-usage',
        '--no-zygote',
        '--disable-software-rasterizer',
        '--disable-extensions',
        '--disable-web-security',
        '--disable-features=VizDisplayCompositor',
        '--single-process'
      ],
    },
  });
} catch (err) {
  console.error('⚠️ Puppeteer no pudo inicializar correctamente:', err.message);
  whatsappClient = null;
}

// ==========================================================
// 🌐 EXPRESS HEALTHCHECK + QR
// ==========================================================
const app = express();
const PORT = process.env.PORT || 10000;
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
// 📲 EVENTOS DEL CLIENTE WHATSAPP
// ==========================================================
if (whatsappClient) {
  whatsappClient.on('qr', async qr => {
    console.log('📲 Escanea este código QR para vincular tu número:');
    qrcode.generate(qr, { small: true });
    await QRCode.toFile(qrPath, qr, { width: 300 });
  });

  whatsappClient.on('ready', () =>
    console.log('✅ Cliente WhatsApp listo y conectado!')
  );

  whatsappClient.on('disconnected', async reason => {
    console.warn('⚠️ Cliente desconectado:', reason);
    setTimeout(() => whatsappClient.initialize(), 5000);
  });
}

// ==========================================================
// 💬 LÓGICA DE INTERACCIÓN PRINCIPAL
// ==========================================================
const saludoDelUsuario = require('./interaccion_del_bot/saludo_del_usuario');
const menuInicioModule = require('./interaccion_del_bot/menu_inicio');
const { iniciarRegistroMascota } = require('./interaccion_del_bot/registro_mascotas_bot');
const { iniciarRegistroUsuario } = require('./interaccion_del_bot/registro_usuario_bot');
const { iniciarSuscripciones } = require('./interaccion_del_bot/suscripciones_cuidadores_bot');
const historiaClinicaBot = require('./interaccion_del_bot/historia_clinica_bot');
const crearCitaBot = require('./interaccion_del_bot/crear_cita_bot');
const { procesarSuscripcion } = require('./interaccion_del_bot/tarifas_menu');

const CMD_MENU = ['menu', 'inicio', 'volver', 'home'];
const CMD_CANCEL = ['cancelar', 'salir', 'stop', 'terminar', 'abortar'];

if (whatsappClient) {
  whatsappClient.on('message', async msg => {
    try {
      const userMsg = (msg.body || '').trim().toLowerCase();
      let session = await getSession(msg.from);
      session.type = session.type || 'menu_inicio';
      session.data = session.data || {};

      if (CMD_CANCEL.includes(userMsg)) {
        await deleteSession(msg.from);
        await msg.reply('🛑 Registro cancelado. Escribe *menu* para comenzar de nuevo.');
        return;
      }

      if (CMD_MENU.includes(userMsg)) {
        session = { type: 'menu_inicio', data: {} };
        await saludoDelUsuario(msg);
        await saveUserSession(msg.from, session);
        return;
      }

      switch (session.type) {
        case 'menu_inicio':
          const handleMenu = await menuInicioModule(msg, null, session);
          await handleMenu(userMsg);
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
            await msg.reply('❌ Opción inválida. Usa 3, 6 o 12 meses.');
          }
          break;
        default:
          await msg.reply('🤖 No entendí. Escribe *menu* para comenzar.');
      }

      await saveUserSession(msg.from, session);

    } catch (err) {
      console.error('⚠️ Error procesando mensaje:', err);
      try {
        await msg.reply('⚠️ Ocurrió un error. Escribe *menu* para reiniciar.');
      } catch (_) {}
    }
  });
}

// ==========================================================
// 🧠 GESTIÓN DE MEMORIA AUTOMÁTICA
// ==========================================================
setInterval(() => {
  const usedMB = process.memoryUsage().rss / 1024 / 1024;
  console.log(`🧠 Memoria usada: ${usedMB.toFixed(2)} MB`);
  if (usedMB > 400) {
    console.warn('🚨 Memoria alta, reinicializando cliente para evitar crash...');
    try {
      whatsappClient?.destroy();
      setTimeout(() => whatsappClient?.initialize(), 8000);
    } catch (_) {}
  }
}, 15000);

// ==========================================================
// 🚀 INICIALIZACIÓN FINAL
// ==========================================================
if (whatsappClient) {
  whatsappClient.initialize();
  console.log('🚀 PETBIO WhatsApp Bot inicializado.');
} else {
  console.warn('⚠️ WhatsApp no se inicializó (Chromium ausente o fallo en Puppeteer).');
  console.warn('👉 Revisa que el build de Render ejecute correctamente el script "postinstall": "puppeteer install"');
}
