// utils/supabase_session.js
import { createClient } from '@supabase/supabase-js';
import 'dotenv/config';

const supabase = createClient(process.env.SUPABASE_URL, process.env.SUPABASE_KEY);

// ✅ Obtiene sesión activa desde Supabase
export async function getSession(usuarioId) {
  try {
    const { data, error } = await supabase
      .from('sesiones')
      .select('*')
      .eq('usuario_id', usuarioId)
      .single();

    if (error && error.code !== 'PGRST116') throw error; // PGRST116 = no rows
    return data || { data: {}, type: 'menu_inicio', step: null, lastActive: Date.now() };
  } catch (err) {
    console.error('❌ Error obteniendo sesión de Supabase:', err.message);
    return null;
  }
}

// ✅ Guarda sesión del usuario
export async function saveSession(usuarioId, session) {
  try {
    const { data, error } = await supabase
      .from('sesiones')
      .upsert([{ usuario_id: usuarioId, session, updated_at: new Date() }], { onConflict: 'usuario_id' });
    if (error) throw error;
    return data;
  } catch (err) {
    console.error('❌ Error guardando sesión en Supabase:', err.message);
  }
}

// ✅ Elimina sesión del usuario
export async function deleteSession(usuarioId) {
  try {
    const { data, error } = await supabase
      .from('sesiones')
      .delete()
      .eq('usuario_id', usuarioId);
    if (error) throw error;
    return data;
  } catch (err) {
    console.error('❌ Error eliminando sesión en Supabase:', err.message);
  }
}

// ✅ Log de eventos (opcional)
export async function logBotEvent(event, details) {
  try {
    await supabase.from('bot_logs').insert([{ event, details, created_at: new Date() }]);
  } catch (err) {
    console.error('⚠️ Error guardando log en Supabase:', err.message);
  }
}
