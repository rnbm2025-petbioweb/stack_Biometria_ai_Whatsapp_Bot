// ==========================================================
// 🔐 SupabaseAuth.js - GESTOR DE SESIÓN WHATSAPP EN SUPABASE
// ==========================================================
const fs = require('fs');
const path = require('path');
const { createClient } = require('@supabase/supabase-js');

const supabase = createClient(
  process.env.SUPABASE_URL || process.env.SUPABASE_URL,
  process.env.SUPABASE_KEY || process.env.SUPABASE_KEY
);

const SESSION_FILE = path.join('/tmp', 'whatsapp_session.json'); // archivo temporal en /tmp
const USER_ID = 'whatsapp_main';

// ----------------------------------------------------------
// 📥 Cargar sesión desde Supabase (escribe SESSION_FILE si existe)
// ----------------------------------------------------------
async function loadSession() {
  try {
    const { data, error } = await supabase
      .from('sessions')
      .select('data')
      .eq('user_id', USER_ID)
      .single();

    if (error && error.code !== 'PGRST116') { // ignora "no rows" tipo
      throw error;
    }

    if (data && data.data) {
      // escribir el archivo temporal que usará LocalAuth (u otros puntos que esperen JSON)
      fs.writeFileSync(SESSION_FILE, JSON.stringify(data.data, null, 2));
      console.log('📦 Sesión cargada desde Supabase ->', SESSION_FILE);
      return SESSION_FILE;
    } else {
      console.log('⚠️ No se encontró sesión en Supabase (usuario nuevo).');
    }
  } catch (err) {
    console.error('❌ Error al cargar sesión desde Supabase:', err.message || err);
  }
  return null;
}

// ----------------------------------------------------------
// 💾 Guardar sesión en Supabase (lee SESSION_FILE y upsert)
// ----------------------------------------------------------
async function saveSession() {
  try {
    if (!fs.existsSync(SESSION_FILE)) {
      // nada que guardar
      // console.log('ℹ️ SESSION_FILE no existe, nada que guardar.');
      return;
    }

    const raw = fs.readFileSync(SESSION_FILE, 'utf-8');
    const sessionData = JSON.parse(raw);

    const { error } = await supabase.from('sessions').upsert({
      user_id: USER_ID,
      data: sessionData,
      last_active: new Date()
    });

    if (error) throw error;
    console.log('✅ Sesión guardada en Supabase.');
  } catch (err) {
    console.error('❌ Error guardando sesión en Supabase:', err.message || err);
  }
}

module.exports = { loadSession, saveSession, SESSION_FILE };
