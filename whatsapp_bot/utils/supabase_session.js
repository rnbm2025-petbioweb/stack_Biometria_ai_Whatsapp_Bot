// utils/supabase_session.js
// ===============================
// Módulo para manejar sesiones en Supabase
// ===============================
const { createClient } = require('@supabase/supabase-js');
require('dotenv').config();

const supabase = createClient(process.env.SUPABASE_URL, process.env.SUPABASE_KEY);

// ✅ Obtiene sesión activa desde Supabase Auth
async function getSession() {
  try {
    const { data, error } = await supabase.auth.getSession();
    if (error) throw error;
    return data?.session || null;
  } catch (err) {
    console.error('❌ Error obteniendo sesión de Supabase:', err.message);
    return null;
  }
}

// ✅ Guarda un log en Supabase (opcional)
async function logBotEvent(event, details) {
  try {
    await supabase.from('bot_logs').insert([{ event, details, created_at: new Date() }]);
  } catch (err) {
    console.error('⚠️ Error guardando log en Supabase:', err.message);
  }
}

module.exports = { getSession, logBotEvent };
