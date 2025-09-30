// saludo_del_usuario.js
const menuInicio = require('./menu_inicio');
const fs = require('fs');
const path = require('path');
const utils = require('./utils_bot');
const { iniciarMenu } = require('./menu_luego_de_registro_de_usuario');
const { mqttCloud } = require('../config.js'); // mismo cliente que menu_inicio.js

// ==============================
// 📡 Publicar eventos en MQTT
// ==============================
function publishMQTT(topic, descripcion, usuario) {
  if (mqttCloud && mqttCloud.connected) {
    mqttCloud.publish(
      `petbio/${topic}`,
      JSON.stringify({
        usuario,
        descripcion,
        fecha: new Date().toISOString(),
      }),
      { qos: 1 }
    );
    console.log(`🔹 MQTT publicado: ${topic} -> ${descripcion}`);
  }
}

// ==============================
// 🕐 Saludo dinámico según hora
// ==============================
function obtenerSaludo() {
  const hora = new Date().getHours();
  if (hora < 12) return "☀️ Buenos días";
  if (hora < 19) return "🌤️ Buenas tardes";
  return "🌙 Buenas noches";
}

/**
 * 📍 Saludo inicial e inicio del flujo de menú
 * @param {object} msg - Mensaje de WhatsApp
 * @param {string} sessionFile - Ruta del archivo de sesión
 * @param {object} session - Objeto de sesión del usuario
 * @param {string|number|null} usuarioId - ID del usuario
 */
async function saludoDelUsuario(msg, sessionFile, session, usuarioId = null) {
  console.log("📁 sessionFile recibido en saludoDelUsuario:", sessionFile);

  // ✅ Nuevo: Validación del sessionFile por si viene null o indefinido
  if (!sessionFile || typeof sessionFile !== "string") {
    console.warn("⚠️ sessionFile es null o inválido. Asignando ruta por defecto...");
    sessionFile = path.join(__dirname, "../.wwebjs_auth/session.json");
  }

  // ✅ Asegurar estructura base de session
  if (!session) session = {};
  session.type = session.type || 'menu_inicio';
  session.data = session.data || {};
  session.lastActive = Date.now();

  const hoy = new Date().toISOString().slice(0, 10); // YYYY-MM-DD
  const semana = getSemana(hoy);

  // 📆 Determinar si toca saludo largo o corto
  let mostrarSaludoLargo = false;
  if (!session.lastGreeted || session.lastGreeted !== hoy) {
    mostrarSaludoLargo = !session.lastWeek || session.lastWeek !== semana;
  }

  // 👋 Enviar saludo si no se ha saludado hoy
  if (!session.lastGreeted || session.lastGreeted !== hoy) {
    let saludo;

    if (mostrarSaludoLargo) {
      saludo =
        `${obtenerSaludo()} 👋 Somos *PETBIO* 🐾\n\n` +
        "📌 Registro Único Biométrico de Mascotas (RUBM).\n" +
        "✅ Te ayudamos a registrar y proteger la identidad biométrica de tu mascota.\n" +
        "💡 Servicios: *Historia Clínica*, *Citas*, *Suscripciones* y más.\n";
    } else {
      saludo = `${obtenerSaludo()} 👋 Bienvenido de nuevo a *PETBIO* 🐾`;
    }

    try {
      await msg.reply(utils.justificarTexto(saludo, 40));
      publishMQTT("saludo_usuario", "Saludo inicial enviado", msg.from);
    } catch (err) {
      console.error("❌ Error enviando saludo:", err);
    }

    // 📝 Guardar en sesión
    session.lastGreeted = hoy;
    session.lastWeek = semana;

    // ✅ Crear carpeta de sesión si no existe antes de escribir
    const sessionDir = path.dirname(sessionFile);
    if (!fs.existsSync(sessionDir)) {
      fs.mkdirSync(sessionDir, { recursive: true });
    }

    fs.writeFileSync(sessionFile, JSON.stringify(session, null, 2));
    console.log("💾 Sesión guardada en:", sessionFile);
  }

  // 📲 Flujo del menú según el estado del usuario
  if (session.type === 'menu_inicio' || !session.type) {
    try {
      return await menuInicio(msg, sessionFile, session);
    } catch (err) {
      console.error("❌ Error en menuInicio:", err);
    }
  }

  // 📍 Flujo posterior al registro
  if (session.type === 'post_registro' && usuarioId) {
    try {
      iniciarMenu(usuarioId);
      publishMQTT("post_registro", "Menú post-registro iniciado", usuarioId);
    } catch (err) {
      console.error("❌ Error iniciando menú post-registro:", err);
    }
  }

  return null;
}

// ==============================
// 📅 Función auxiliar: semana del año
// ==============================
function getSemana(fechaStr) {
  const fecha = new Date(fechaStr);
  const inicioAno = new Date(fecha.getFullYear(), 0, 1);
  const diff =
    fecha - inicioAno + (inicioAno.getTimezoneOffset() - fecha.getTimezoneOffset()) * 60000;
  return Math.floor(diff / (7 * 24 * 60 * 60 * 1000));
}

module.exports = saludoDelUsuario;

/* ===========================================================
 📜 CÓDIGO ORIGINAL (comentado) — AHORA REEMPLAZADO
===========================================================

async function saludoDelUsuario(msg, sessionFile, session, usuarioId = null) {
  console.log("📁 sessionFile recibido en saludoDelUsuario:", sessionFile); // 👈 Debug clave

  if (!session) session = {};

  const hoy = new Date().toISOString().slice(0, 10); // YYYY-MM-DD
  const semana = getSemana(hoy);

  // 📆 Determinar si toca saludo largo o corto
  let mostrarSaludoLargo = false;
  if (!session.lastGreeted || session.lastGreeted !== hoy) {
    mostrarSaludoLargo = !session.lastWeek || session.lastWeek !== semana;
  }

  // 👋 Enviar saludo si no se ha saludado hoy
  if (!session.lastGreeted || session.lastGreeted !== hoy) {
    let saludo;

    if (mostrarSaludoLargo) {
      saludo =
        `${obtenerSaludo()} 👋 Somos *PETBIO* 🐾\n\n` +
        "📌 Registro Único Biométrico de Mascotas (RUBM).\n" +
        "✅ Te ayudamos a registrar y proteger la identidad biométrica de tu mascota.\n" +
        "💡 Servicios: *Historia Clínica*, *Citas*, *Suscripciones* y más.\n";
    } else {
      saludo = `${obtenerSaludo()} 👋 Bienvenido de nuevo a *PETBIO* 🐾`;
    }

    try {
      await msg.reply(utils.justificarTexto(saludo, 40));
      publishMQTT("saludo_usuario", "Saludo inicial enviado", msg.from);
    } catch (err) {
      console.error("❌ Error enviando saludo:", err);
    }

    // 📝 Guardar en sesión
    session.lastGreeted = hoy;
    session.lastWeek = semana;

    if (sessionFile && typeof sessionFile === "string") {
      const sessionDir = path.dirname(sessionFile);
      if (!fs.existsSync(sessionDir)) fs.mkdirSync(sessionDir, { recursive: true });

      fs.writeFileSync(sessionFile, JSON.stringify(session, null, 2));
      console.log("💾 Sesión guardada en:", sessionFile);
    } else {
      console.error("⚠️ sessionFile es inválido en saludoDelUsuario:", sessionFile);
    }
  }

  // 📲 Flujo del menú según el estado del usuario
  if (session.type === 'menu_inicio' || !session.type) {
    try {
      return await menuInicio(msg, sessionFile, session);
    } catch (err) {
      console.error("❌ Error en menuInicio:", err);
    }
  }

  // 📍 Flujo posterior al registro
  if (session.type === 'post_registro' && usuarioId) {
    try {
      iniciarMenu(usuarioId);
      publishMQTT("post_registro", "Menú post-registro iniciado", usuarioId);
    } catch (err) {
      console.error("❌ Error iniciando menú post-registro:", err);
    }
  }

  return null;
}
*/
