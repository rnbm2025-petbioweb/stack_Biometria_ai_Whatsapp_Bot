/**
 * registro_usuario_bot.js
 *
 * Flujo de registro de usuarios PETBIO.
 * username → apellidos_usuario → email → password → documento_identidad
 * → tipo_persona → tiene_mascotas → aceptar_terminos.
 *
 * - Soporta "cancelar" en cualquier momento.
 * - Genera ID PETBIO único y lo guarda en MySQL.
 * - Genera un PDF tipo certificado en WhatsApp_bot_storage/certificados.
 */

const bcrypt = require('bcryptjs');
const fs = require('fs');
const path = require('path');
const mysql = require('mysql2/promise');
const PDFDocument = require('pdfkit');

// ========================
// Configuración MySQL
// ========================
const dbConfig = {
  host: process.env.MYSQL_HOST || '127.0.0.1',
  port: process.env.MYSQL_PORT || 3310,
  user: process.env.MYSQL_USER || 'root',
  password: process.env.MYSQL_PASSWORD || 'R00t_Segura_2025!',
  database: process.env.MYSQL_DATABASE || 'db__produccion_petbio_segura_2025'
};

// ========================
// Directorio de certificados
// ========================
const CERT_DIR = path.join(__dirname, '../WhatsApp_bot_storage/certificados');
if (!fs.existsSync(CERT_DIR)) {
  fs.mkdirSync(CERT_DIR, { recursive: true });
}

// ========================
// Validaciones
// ========================
function validarEntrada(step, texto) {
  texto = texto.trim();
  if (!texto) return false;

  const reglas = {
    username: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/,
    apellidos_usuario: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/,
    email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
    password: /^.{6,}$/,
    Documento_identidad: /^[0-9]+$/,
    tipo_persona: /^(Natural|Jurídica)$/i,
    tiene_mascotas: /^[01]$/,
    aceptar_terminos: /^(sí|si|acepto)$/i
  };

  return reglas[step] ? reglas[step].test(texto) : true;
}

// ========================
// Validar si usuario ya existe
// ========================
async function validarUsuarioExistente(email, Documento_identidad) {
  const conn = await mysql.createConnection(dbConfig);
  try {
    const [rows] = await conn.execute(
      `SELECT id_usuario FROM registro_usuario WHERE email = ? OR Documento_identidad = ? LIMIT 1`,
      [email, Documento_identidad]
    );
    return rows.length > 0;
  } finally {
    await conn.end();
  }
}

// ========================
// Registrar usuario en DB
// ========================
async function registrarUsuario(datos) {
  const conn = await mysql.createConnection(dbConfig);
  let { username, apellidos_usuario, email, password, Documento_identidad, tipo_persona, tiene_mascotas } = datos;

  try {
    const existe = await validarUsuarioExistente(email, Documento_identidad);
    if (existe) return { ok: false, msg: '⚠️ Correo o documento ya registrado' };

    const hashedPassword = bcrypt.hashSync(password, 10);
    const [result] = await conn.execute(
      `INSERT INTO registro_usuario
      (username, apellidos_usuario, email, password, Documento_identidad, tipo_persona, tiene_mascotas)
      VALUES (?, ?, ?, ?, ?, ?, ?)`,
      [username, apellidos_usuario, email, hashedPassword, Documento_identidad, tipo_persona, tiene_mascotas]
    );

    const id_usuario = result.insertId;
    const esCuidador = (tiene_mascotas === '1') ? 1 : 0;
    const rol = esCuidador ? 'CUID' : 'USER';
    const petbioID = `PETBIO-${rol}-${String(id_usuario).padStart(6, '0')}`;

    await conn.execute(
      `INSERT INTO identidad_petbio (id_usuario, id_petbio, es_cuidador)
       VALUES (?, ?, ?)`,
      [id_usuario, petbioID, esCuidador]
    );

    return { ok: true, petbioID };

  } catch (err) {
    console.error('❌ Error en registrarUsuario:', err);
    return { ok: false, msg: '❌ Error interno en el registro' };
  } finally {
    await conn.end();
  }
}

// ========================
// Generar certificado PDF
// ========================
function generarCertificadoPDF(usuario, outputDir) {
  const pdfPath = path.join(outputDir, `PETBIO_CERT_${usuario.petbioID}.pdf`);
  const doc = new PDFDocument({ size: 'A4', margin: 50 });
  const writeStream = fs.createWriteStream(pdfPath);
  doc.pipe(writeStream);

  doc.fontSize(20).text('CERTIFICADO PETBIO', { align: 'center' });
  doc.moveDown();
  doc.fontSize(14).text(`ID Usuario: ${usuario.petbioID}`);
  doc.text(`Nombre: ${usuario.username} ${usuario.apellidos_usuario}`);
  doc.text(`Email: ${usuario.email}`);
  doc.text(`Documento: ${usuario.Documento_identidad}`);
  doc.text(`Tipo de persona: ${usuario.tipo_persona}`);
  doc.text(`Tiene mascotas: ${usuario.tiene_mascotas === '1' ? 'Sí' : 'No'}`);
  doc.moveDown();
  doc.text(`📌 Este certificado garantiza tu registro en PETBIO de forma segura.`);
  doc.moveDown();
  doc.text(`🌐 Accede a tu plataforma: https://petbio.siac2025.com/identidad_rubm.php`,
    { link: 'https://petbio.siac2025.com/identidad_rubm.php', underline: true });

  doc.end();
  return new Promise(resolve => writeStream.on('finish', () => resolve(pdfPath)));
}

// ========================
// Flujo de registro
// ========================
async function iniciarRegistroUsuario(msg, session, sessionFile) {
  const texto = (msg.body || '').trim();
  const lc = texto.toLowerCase();

  // Cancelar en cualquier momento
  if (lc === 'cancelar' || lc === 'cancel') {
    try { fs.unlinkSync(sessionFile); } catch(e){}
    await msg.reply('✅ Registro cancelado. Volviendo al inicio...');
    return;
  }

  const step = session.step || 'username';
  const data = session.data || {};

  if (!validarEntrada(step, texto)) {
    await msg.reply(`❌ Entrada inválida para este paso (${step}). Intenta nuevamente.`);
    return;
  }

  // Guardar datos según el paso
  switch(step){
    case 'username': data.username = texto; session.step='apellidos_usuario'; break;
    case 'apellidos_usuario': data.apellidos_usuario = texto; session.step='email'; break;
    case 'email': data.email = texto; session.step='password'; break;
    case 'password': data.password = texto; session.step='Documento_identidad'; break;
    case 'Documento_identidad': data.Documento_identidad = texto; session.step='tipo_persona'; break;
    case 'tipo_persona': data.tipo_persona = texto; session.step='tiene_mascotas'; break;
    case 'tiene_mascotas': data.tiene_mascotas = texto; session.step='aceptar_terminos'; break;

    case 'aceptar_terminos':
      if (!validarEntrada('aceptar_terminos', texto)) {
        await msg.reply(
          '❌ Debes aceptar los Términos y Condiciones.\n' +
          'Responde "Sí" para aceptar.\n🌐 https://petbio.siac2025.com/politica_datos'
        );
        return;
      }

      data.aceptar_terminos = texto;
      await msg.reply('Procesando tu registro... ⏳');

      // ===============================
      // Registrar usuario y generar PDF
      // ===============================
      let result, pdfPath;
      try {
        result = await registrarUsuario(data);
        if (!result.ok) {
          await msg.reply(result.msg || '❌ Error al registrar.');
          return;
        }
        pdfPath = await generarCertificadoPDF({ ...data, petbioID: result.petbioID }, CERT_DIR);
      } catch (err) {
        console.error('❌ Error interno en registrarUsuario o PDF:', err);
        await msg.reply('❌ Error interno al registrar. Intenta nuevamente más tarde.');
        return;
      }

      // ✅ Enviar PDF usando createReadStream para evitar TypeError
      await msg.reply(`✅ Registro exitoso! Tu ID PETBIO es: ${result.petbioID}`);
      await msg.reply('📄 Tu certificado PETBIO ha sido generado:', {
        attachment: fs.createReadStream(pdfPath),
        mimetype: 'application/pdf',
        filename: path.basename(pdfPath)
      });

      // Limpiar sesión
      try { fs.unlinkSync(sessionFile); } catch(e){}
      return;
  }

  session.data = data;
  fs.writeFileSync(sessionFile, JSON.stringify(session));

  // Mensajes para cada paso
  const prompts = {
    'apellidos_usuario': 'Indica tus apellidos:',
    'email': 'Indica tu correo electrónico:',
    'password': 'Elige una contraseña segura (mín. 6 caracteres):',
    'Documento_identidad': 'Indica tu número de documento:',
    'tipo_persona': 'Tipo de persona (Natural o Jurídica):',
    'tiene_mascotas': '¿Tienes mascotas? Responde 1 para Sí o 0 para No:',
    'aceptar_terminos': 'Por favor acepta los Términos y Condiciones. Responde "Sí" para aceptar.\n🌐 https://petbio.siac2025.com/politica_datos'
  };

  await msg.reply(prompts[session.step]);
}

module.exports = { iniciarRegistroUsuario };
