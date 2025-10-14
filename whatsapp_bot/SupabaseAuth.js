const fs = require('fs');
const path = require('path');
const { createClient } = require('@supabase/supabase-js');

const supabase = createClient(
  process.env.SUPABASE_URL,
  process.env.SUPABASE_KEY
);

const SESSION_FILE = path.join(__dirname, 'tmp_session.json');
const USER_ID = 'whatsapp_main';

async function loadSession() {
  try {
    const { data } = await supabase.from('sessions').select('data').eq('user_id', USER_ID).single();
    if (data && data.data) {
      fs.writeFileSync(SESSION_FILE, JSON.stringify(data.data));
      return SESSION_FILE;
    }
  } catch (err) {
    console.error('⚠️ No se pudo cargar sesión de Supabase:', err.message);
  }
  return null;
}

async function saveSession() {
  try {
    const raw = fs.existsSync(SESSION_FILE)
      ? fs.readFileSync(SESSION_FILE, 'utf-8')
      : null;
    if (raw) {
      await supabase.from('sessions').upsert({
        user_id: USER_ID,
        data: JSON.parse(raw),
        last_active: new Date()
      });
      console.log('💾 Sesión guardada en Supabase.');
    }
  } catch (err) {
    console.error('❌ Error guardando sesión en Supabase:', err.message);
  }
}

module.exports = { loadSession, saveSession, SESSION_FILE };
