
// ==========================================================
// 🤖 PETBIO WhatsApp Bot + Supabase + MQTT LavinMQ
// Versión Render-ready con sesión persistente (Supabase)
// ==========================================================

require('dotenv').config();
const path = require('path');
const fs = require('fs');
const express = require('express');
const { Client } = require('whatsapp-web.js'); // sin LocalAuth (usaremos session:)
const qrcode = require('qrcode-terminal');
const QRCode = require('qrcode');
const { createClient } = require('@supabase/supabase-js');
const mqtt = require('mqtt');
const puppeteer = require('puppeteer');

// ==========================================================
// 🌐 CONFIGURACIÓN SUPABASE
// ==========================================================
const supabaseUrl = process.env.SUPABASE_URL || 'https://jbsxvonnrahhfffeacdy.supabase.co';
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
const MQTT_HOST = process.env.MQTT_CLOUD_BROKER || 'duck.lmq.cloudamqp.com';
const MQTT_PORT = parseInt(process.env.MQTT_PORT || '8883', 10);
const MQTT_USER = process.env.MQTT_CLOUD_USER;
const MQTT_PASS = process.env.MQTT_CLOUD_PASS;
const MQTT_TOPIC = process.env.MQTT_TOPIC || 'petbio/test';

let mqttCloud = null;

const initMQTT = () => {
  if (!MQTT_USER || !MQTT_PASS) {
    console.warn('⚠️ MQTT no configurado: faltan credenciales.');
    return;
  }
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
    mqttCloud = mqtt.connect(MQTT_HOST, mqttOptions);

    mqttCloud.on('connect', () => {
      console.log(`✅ MQTT conectado y suscrito a ${MQTT_TOPIC}`);
      mqttCloud.subscribe(MQTT_TOPIC, (err) => {
        if (err) console.warn('⚠️ Error suscribiendo tópico MQTT:', err.message);
      });
    });

    mqttCloud.on('message', (topic, msg) =>
      console.log(`📨 [${topic}] ${msg.toString()}`)
    );

    mqttCloud.on('error', err =>
      console.error('⚠️ Error MQTT:', err?.message || err)
    );

    mqttCloud.on('close', () =>
      console.warn('🔌 MQTT desconectado, reintentando...')
    );

  } catch (err) {
    console.warn('⚠️ Error inicializando MQTT:', err.message);
  }
};

initMQTT();

// ==========================================================
// 🧠 FUNCIONES SUPABASE (sessions de usuarios - UI)
// ==========================================================
const SESSION_TTL = 1000 * 60 * 60 * 12; // 12 horas

const getSession = async (userId) => {
  try {
    const { data, error } = await supabase
      .from('sessions')
      .select('*')
      .eq('user_id', userId)
      .single();
    if (error) {
      // no existe -> ok
      return {};
    }
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
// 📁 SESIÓN LOCAL (solo para almacenar QR temporalmente)
// ==========================================================
const sessionDir = '/tmp/session';
if (!fs.existsSync(sessionDir)) fs.mkdirSync(sessionDir, { recursive: true });
const qrPath = path.join(sessionDir, 'whatsapp_qr.png');
console.log(`📁 Sesiones WhatsApp temporales en: ${sessionDir}`);

// ==========================================================
// 🧩 DETECCIÓN DE CHROME EN RENDER
// ==========================================================
let chromePath;
try {
  chromePath = process.env.PUPPETEER_EXECUTABLE_PATH || puppeteer.executablePath();
  console.log(`🔍 Chrome detectado en: ${chromePath}`);
} catch (err) {
  console.error('❌ Chrome no encontrado automáticamente:', err.message);
  chromePath = undefined;
}

// ==========================================================
// 🔁 Helpers para guardar / cargar sesión del BOT en Supabase
// (tabla: whatsapp_sessions with columns session_id TEXT PRIMARY KEY, data JSONB, fecha_registro TIMESTAMP, updated_at TIMESTAMP)
// ==========================================================
const BOT_SESSION_ID = process.env.BOT_SESSION_ID || 'test_session';

async function cargarSessionDelBot(sessionId = BOT_SESSION_ID) {
  try {
    const { data, error } = await supabase
      .from('whatsapp_sessions')
      .select('data')
      .eq('session_id', sessionId)
      .maybeSingle();

    if (error) {
      console.warn('⚠️ cargarSessionDelBot error:', error.message || error);
      return null;
    }
    if (!data) return null;

    // data.data puede ser JSON (jsonb) o texto; maneja ambos
    const raw = data.data;
    if (!raw) return null;
    if (typeof raw === 'string') {
      try {
        return JSON.parse(raw);
      } catch (e) {
        console.warn('⚠️ cargarSessionDelBot: JSON.parse falló, retornando raw string');
        return raw;
      }
    }
    // ya es objeto (jsonb)
    return raw;
  } catch (err) {
    console.error('⚠️ Error cargando sesión del bot:', err.message);
    return null;
  }
}

async function guardarSessionDelBot(sessionObj, sessionId = BOT_SESSION_ID) {
  try {
    const payload = typeof sessionObj === 'string' ? sessionObj : JSON.stringify(sessionObj);
    await supabase.from('whatsapp_sessions').upsert({
      session_id: sessionId,
      data: payload,
      updated_at: new Date(),
      fecha_registro: new Date()
    });
    console.log('💾 Sesión del bot guardada en Supabase.');
  } catch (err) {
    console.error('⚠️ Error guardando sesión del bot en Supabase:', err.message);
  }
}

// ==========================================================
// 🤖 CLIENTE WHATSAPP (Sesión persistente en Supabase)
// ==========================================================
let whatsappClient = null;

(async () => {
  if (!chromePath) {
    console.error('❌ No hay chromePath válido. Abortando inicialización de WhatsApp.');
    return;
  }

  try {
    console.log('🔁 Intentando restaurar sesión del bot desde Supabase...');
    const restoredSession = await cargarSessionDelBot(BOT_SESSION_ID);
    console.log(restoredSession ? '✅ Sesión restaurada desde Supabase' : '⚠️ No se encontró sesión previa');

    whatsappClient = new Client({
      session: restoredSession || undefined,
      puppeteer: {
        executablePath: chromePath,
        headless: true,
        args: [
          '--no-sandbox',
          '--disable-setuid-sandbox',
          '--disable-gpu',
          '--disable-dev-shm-usage',
          '--disable-software-rasterizer',
          '--single-process',
        ],
      },
    });

    // QR -> si no hay sesión válida este evento aparecerá
    whatsappClient.on('qr', async (qr) => {
      console.log('📲 QR generado (escanealo una vez):');
      qrcode.generate(qr, { small: true });
      try {
        await QRCode.toFile(qrPath, qr, { width: 300 });
      } catch (_) {}
    });

    // Al autenticarse: guardar sesión en Supabase
    whatsappClient.on('authenticated', async (session) => {
      console.log('🔐 Autenticado: guardando sesión en Supabase...');
      await guardarSessionDelBot(session, BOT_SESSION_ID);
    });

    whatsappClient.on('ready', () => {
      console.log('✅ Cliente WhatsApp listo y conectado.');
    });

    whatsappClient.on('disconnected', (reason) => {
      console.warn('⚠️ Cliente desconectado:', reason);
      // eliminar sesión en Supabase para forzar nuevo flujo si es un cierre no esperado (opcional)
      // await supabase.from('whatsapp_sessions').delete().eq('session_id', BOT_SESSION_ID);
      setTimeout(() => whatsappClient?.initialize(), 8000);
    });

    await whatsappClient.initialize();
    console.log('🚀 WhatsApp client initialized.');
  } catch (err) {
    console.error('❌ Error inicializando WhatsApp client:', err?.message || err);
  }
})();

// ==========================================================
// 🌐 EXPRESS HEALTHCHECK + QR
// ==========================================================
const app = express();
const PORT = process.env.PORT || 3000;

app.get('/health', (req, res) => {
  res.json({
    status: '✅ PETBIO Bot activo',
    supabase: !!supabaseKey,
    mqtt: mqttCloud?.connected || false,
    whatsapp: whatsappClient?.info ? '✅ Conectado' : '⏳ Esperando conexión'
  });
});

app.get('/qr', (req, res) => {
  if (fs.existsSync(qrPath)) res.sendFile(qrPath);
  else res.status(404).send('❌ QR aún no generado');
});

app.listen(PORT, () => {
  console.log(`🌐 Healthcheck activo en puerto ${PORT}`);
});

// ==========================================================
// EVENTOS DE MENSAJES (ejemplo de integración)
// ==========================================================
// Importa aquí tus módulos de interacción (ya que el cliente está inicializado asíncronamente,
// los listeners se registran arriba dentro del IIFE cuando corresponda)
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

function registerMessageHandler() {
  if (!whatsappClient) return;

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
      try { await msg.reply('⚠️ Ocurrió un error. Escribe *menu* para reiniciar.'); } catch (_) {}
    }
  });
}

// Cuando el cliente esté listo, registra el handler (si ya está listo, lo hace igual)
const waitForClientReady = setInterval(() => {
  if (whatsappClient && whatsappClient.info) {
    registerMessageHandler();
    clearInterval(waitForClientReady);
  }
}, 1000);

// ==========================================================
// ⚠️ Manejo global de errores
// ==========================================================
process.on('uncaughtException', (err) => {
  console.error('💥 Uncaught Exception:', err);
  setTimeout(() => process.exit(1), 2000);
});
process.on('unhandledRejection', (reason) => {
  console.error('💥 Unhandled Rejection:', reason);
  setTimeout(() => process.exit(1), 2000);
});
