// index.js - PETBIO WhatsApp Bot Integrado 🌐 (Docker-ready)
// ==========================================================

require('dotenv').config();

const path = require('path');
const fs = require('fs');
const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const QRCode = require('qrcode');
const express = require('express');
const { createClient } = require('@supabase/supabase-js');

// ------------------ 🌐 Configuración Supabase ------------------
const supabaseUrl = 'https://jbsxvonnrahhfffeacdy.supabase.co';
const supabaseKey = process.env.SUPABASE_KEY;
const supabase = createClient(supabaseUrl, supabaseKey);

console.log("🔑 Supabase Key cargada:", supabaseKey ? "✅ Sí" : "❌ No");

// ❗ Detener el proceso si no hay clave de Supabase
if (!supabaseKey) {
  console.error("❌ ERROR: No se encontró SUPABASE_KEY. El bot no puede iniciar sin ella.");
  process.exit(1);
}

// ------------------ 📡 Configuración MQTT ------------------
const { mqttCloud } = require('./config');

if (mqttCloud) {
  mqttCloud.on('connect', () => console.log('✅ Conectado a CloudMQTT'));

  mqttCloud.on('error', (err) => {
    console.error('❌ Error CloudMQTT:', err.message);
    if (err.message.includes("Not authorized")) {
      console.error("⚠️ Credenciales MQTT inválidas o sin permisos. Revisa USER y PASS en tu .env");
      mqttCloud.end(true);
    }
  });

  mqttCloud.on('close', () => {
    console.warn('⚠️ Conexión MQTT cerrada. Reintentando en 10s...');
    setTimeout(() => mqttCloud.reconnect(), 10000);
  });
}

// ------------------ 🤖 Cliente WhatsApp ------------------

/*
const whatsappClient = new Client({
  puppeteer: {
    headless: true,
    executablePath: process.env.PUPPETEER_EXECUTABLE_PATH || '/usr/bin/chromium',
    args: [
      '--no-sandbox',
      '--disable-setuid-sandbox',
      '--disable-dev-shm-usage'
    ]
  },
  authStrategy: new LocalAuth({ dataPath: '/tmp/.wwebjs_auth' })
}); */

/*
// ------------------ 🤖 Cliente WhatsApp ------------------
const whatsappClient = new Client({
  authStrategy: new LocalAuth({
    dataPath: path.join(__dirname, './session')  // 📁 sesión persistente
  }),
  puppeteer: {
    headless: true,
    args: [
      '--no-sandbox',
      '--disable-setuid-sandbox',
      '--disable-dev-shm-usage'
    ]
  }
});
*/

//const { Client, LocalAuth } = require('whatsapp-web.js');

const whatsappClient = new Client({
  puppeteer: {
    headless: true,
    args: [
      '--no-sandbox',
      '--disable-setuid-sandbox',
      '--disable-dev-shm-usage'
    ]
  },
  authStrategy: new LocalAuth({
//    dataPath: './session/session'   // 👈 ¡Importante! No pongas path.join ni carpetas extras
    userDataDir: '/usr/src/app/session'

  })
});


// ------------------ 🌐 Express Healthcheck y QR ------------------
const app = express();
const PORT = process.env.PORT || 3000;

app.get('/health', (req, res) => {
  res.json({
    status: '✅ PETBIO Bot activo',
    supabase: !!supabaseKey,
    mqtt: mqttCloud?.connected || false,
    whatsapp: whatsappClient?.info ? "✅ Conectado" : "⏳ Esperando conexión"
  });
});

const qrPath = path.join(__dirname, 'tmp_whatsapp_qr.png');
app.get('/qr', (req, res) => {
  if (fs.existsSync(qrPath)) res.sendFile(qrPath);
  else res.status(404).send('❌ QR aún no generado');
});

app.listen(PORT, () => console.log(`🌐 Healthcheck en puerto ${PORT}`));

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
  try {
    const { data } = await supabase.from('sessions').select('*').eq('user_id', userId).single();
    if (data && Date.now() - new Date(data.last_active).getTime() < SESSION_TTL) {
      return JSON.parse(data.data);
    }
  } catch (err) {
    console.error(`❌ Error obteniendo sesión de ${userId}:`, err.message);
  }
  return {};
};

const saveSession = async (userId, session) => {
  try {
    const { error } = await supabase.from('sessions').upsert({
      user_id: userId,
      data: JSON.stringify(session),
      last_active: new Date()
    });
    if (error) throw error;
  } catch (err) {
    console.error(`❌ Error guardando sesión de ${userId}:`, err.message);
  }
};

const deleteSession = async (userId) => {
  try {
    await supabase.from('sessions').delete().eq('user_id', userId);
  } catch (err) {
    console.error(`❌ Error eliminando sesión de ${userId}:`, err.message);
  }
};

// ------------------ 📋 Comandos globales ------------------
const CMD_MENU = ['menu', 'inicio', 'volver', 'home'];
const CMD_CANCEL = ['cancelar', 'salir', 'stop', 'terminar', 'abortar'];

// ------------------ 💬 Flujo principal ------------------
const saludoDelUsuario = require('./interaccion_del_bot/saludo_del_usuario');
const menuInicioModule = require('./interaccion_del_bot/menu_inicio');
const { iniciarRegistroMascota } = require('./interaccion_del_bot/registro_mascotas_bot');
const { iniciarRegistroUsuario } = require('./interaccion_del_bot/registro_usuario_bot');
const { iniciarSuscripciones } = require('./interaccion_del_bot/suscripciones_cuidadores_bot');
const historiaClinicaBot = require('./interaccion_del_bot/historia_clinica_bot');
const crearCitaBot = require('./interaccion_del_bot/crear_cita_bot');
<<<<<<< HEAD
/*
=======
const { mostrarMenuTarifas, procesarSuscripcion } = require('./interaccion_del_bot/tarifas_menu');

>>>>>>> faf9d38 (Actualización: cambios en archivos principales, limpieza de copias de respaldo)
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
    switch (session.type) {
      case 'menu_inicio':
        const handleMenu = await menuInicioModule(msg, null, session);
        await handleMenu(userMsg);
        break;
      case 'registro_usuario':
        const sessionFileSafe = path.join(__dirname, `sessions/${msg.from}.json`);
        await iniciarRegistroUsuario(msg, session, sessionFileSafe);
        break;
      case 'registro_mascota':
        await iniciarRegistroMascota(msg, session, null, mqttCloud);
        break;
      case 'suscripciones':
        await iniciarSuscripciones(msg, session, null);
        break;
      case 'historia_clinica':
        console.log('📡 Ingresando al flujo historia_clinica...');
        await historiaClinicaBot.procesarSolicitud(msg.from);
        break;
      case 'crear_cita':
        await crearCitaBot.procesarSolicitud(msg.from);
        break;
      case 'tarifas':
        const meses = parseInt(lcMsg);
        if ([3, 6, 12].includes(meses)) {
          await msg.reply(procesarSuscripcion(meses));
        } else if (lcMsg.startsWith('confirmar')) {
          const partes = lcMsg.split(' ');
          const mesesConfirmados = parseInt(partes[1]);
          if ([3, 6, 12].includes(mesesConfirmados)) {
            await msg.reply(`🎉 ¡Gracias por suscribirte al plan de ${mesesConfirmados} meses! 🐾`);
            session.type = 'menu_inicio';
          } else {
            await msg.reply("⚠️ Debes indicar un período válido: 3, 6 o 12 meses.");
          }
        } else {
          await msg.reply("❌ Opción inválida en tarifas. Responde con 3, 6 o 12 meses, o escribe *menu*.");
        }
        break;
      default:
        await msg.reply('🤖 No entendí. Escribe *menu* o *cancelar*.');
        break;
<<<<<<< HEAD



/*
	case 'tarifas':
	    const meses = parseInt(lcMsg);
	    if ([3, 6, 12].includes(meses)) {
	        await msg.reply(procesarSuscripcion(meses));
	    } else if (lcMsg.startsWith('confirmar')) {
	        const partes = lcMsg.split(' ');
	        const mesesConfirmados = parseInt(partes[1]);
	        if ([3, 6, 12].includes(mesesConfirmados)) {
	            await msg.reply(`🎉 ¡Gracias por suscribirte al plan de ${mesesConfirmados} meses! 🐾`);
	            session.type = 'menu_inicio'; // 🔹 volvemos al menú
	        } else {
	            await msg.reply("⚠️ Debes indicar un período válido: 3, 6 o 12 meses.");
	        }
	    } else {
	        await msg.reply("❌ Opción inválida en tarifas. Responde con 3, 6 o 12 meses, o escribe *menu*.");
	    }
	    break;   


=======
>>>>>>> faf9d38 (Actualización: cambios en archivos principales, limpieza de copias de respaldo)
    }

    await saveSession(msg.from, session);

  } catch (err) {
<<<<<<< HEAD
    console.error('Trabajamos para mejorar los servicios.:', err);
    try { await msg.reply('⚠️  Visite nuestro sitio: petbio.siac2025.com/identidad_rubm.php; conozca el modulo para el registro de biometria. Escribe *menu* para reiniciar.'); } catch (_) {}  });
*/
=======
    console.error('⚠️ Error en el bot:', err);
    try { await msg.reply('⚠️ Ocurrió un error. Escribe *menu* para reiniciar.'); } catch (_) {}
  }
});

>>>>>>> faf9d38 (Actualización: cambios en archivos principales, limpieza de copias de respaldo)
// ------------------ 📊 Monitoreo memoria ------------------


// colocamos esta parte el dia 6 de octubre 2:40 am}


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
    switch (session.type) {
      case 'menu_inicio':
        const handleMenu = await menuInicioModule(msg, null, session);
        await handleMenu(userMsg);
        break;

      case 'registro_usuario':
        await iniciarRegistroUsuario(msg, session, null);
        break;

      case 'registro_mascota':
        // 🔹 Por ahora usamos CloudMQTT
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

      case 'tarifas':
        const meses = parseInt(lcMsg);
        if ([3, 6, 12].includes(meses)) {
          await msg.reply(procesarSuscripcion(meses));
        } else if (lcMsg.startsWith('confirmar')) {
          const partes = lcMsg.split(' ');
          const mesesConfirmados = parseInt(partes[1]);
          if ([3, 6, 12].includes(mesesConfirmados)) {
            await msg.reply(`🎉 ¡Gracias por suscribirte al plan de ${mesesConfirmados} meses! 🐾`);
            session.type = 'menu_inicio'; // 🔹 volvemos al menú
          } else {
            await msg.reply("⚠️ Debes indicar un período válido: 3, 6 o 12 meses.");
          }
        } else {
          await msg.reply("❌ Opción inválida en tarifas. Responde con 3, 6 o 12 meses, o escribe *menu*.");
        }
        break;

      default:
        await msg.reply('🤖 No entendí. Escribe *menu* o *cancelar*.');
        break;
    }

    await saveSession(msg.from, session);

  } catch (err) {
    console.error('Trabajamos para mejorar los servicios.:', err);
    try {
      await msg.reply(
        '⚠️  Visite nuestro sitio: petbio.siac2025.com/identidad_rubm.php; conozca el modulo para el registro de biometria. Escribe *menu* para reiniciar.'
      );
    } catch (_) {}
  }
	
});	

setInterval(() => {
  const used = process.memoryUsage().rss / 1024 / 1024;
  console.log(`📊 Memoria usada: ${used.toFixed(2)} MB`);
}, 10000);
// 🚀 Inicializar cliente WhatsApp
whatsappClient.initialize();
