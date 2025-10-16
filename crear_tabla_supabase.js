// ==========================================================
// 🗄️ Script para crear tabla whatsapp_sessions en Supabase
// ==========================================================
require('dotenv').config();
const { Pool } = require('pg');

// Conexión a Supabase usando la variable SUPABASE_DB_URL
const pool = new Pool({
  connectionString: process.env.SUPABASE_DB_URL,
  ssl: { rejectUnauthorized: false } // importante para conexión segura
});

async function crearTabla() {
  const query = `
    CREATE TABLE IF NOT EXISTS whatsapp_sessions (
      session_id TEXT PRIMARY KEY,
      data JSONB NOT NULL,
      updated_at TIMESTAMP DEFAULT NOW()
    );
  `;

  try {
    console.log('📡 Conectando a Supabase...');
    await pool.query(query);
    console.log('✅ Tabla "whatsapp_sessions" creada o ya existente.');
  } catch (err) {
    console.error('⚠️ Error al crear la tabla:', err.message);
  } finally {
    await pool.end();
  }
}

crearTabla();

