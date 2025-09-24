// bot_petbio.js
// Lógica central de negocio: DB, PDF, MQTT, biometría, utilidades, suscripciones

const fs = require('fs');
const path = require('path');
const mysql = require('mysql2/promise');
const PDFDocument = require('pdfkit');
const mqtt = require('mqtt');
const axios = require('axios');
const { execFile } = require('child_process');
const { URL } = require('url');

// =====================
// Configuración MySQL
// =====================
const dbConfig = {
  host: process.env.MYSQL_HOST || '127.0.0.1',
  user: process.env.MYSQL_USER || 'root',
  password: process.env.MYSQL_PASSWORD || 'R00t_Segura_2025!',
  database: process.env.MYSQL_DATABASE || 'db__produccion_petbio_segura_2025',
  port: process.env.MYSQL_PORT || 3306
};

// =====================
// Configuración MQTT con reconexión
// =====================
const MQTT_BROKER = process.env.MQTT_BROKER || 'mqtt://mosquitto:1883';
const MQTT_CLIENT = mqtt.connect(MQTT_BROKER, { reconnectPeriod: 5000 });
MQTT_CLIENT.on('connect', () => console.log('✅ Conectado a MQTT Broker'));
MQTT_CLIENT.on('error', err => console.error('❌ Error MQTT:', err));

// =====================
// Directorios
// =====================
const UPLOADS_DIR = path.join(__dirname, 'WhatsApp_bot_storage/uploads/mascotas');
if (!fs.existsSync(UPLOADS_DIR)) fs.mkdirSync(UPLOADS_DIR, { recursive: true });

const DOCUMENTS_DIR = path.join(__dirname, 'WhatsApp_bot_storage/documentos_bot');
if (!fs.existsSync(DOCUMENTS_DIR)) fs.mkdirSync(DOCUMENTS_DIR, { recursive: true });

// =====================
// Autocompletar raza
// =====================
const razas = require('./interaccion_del_bot/razas_perros.json');
function sugerirRaza(input) {
  const term = input.toLowerCase();
  return razas.filter(r => r.toLowerCase().includes(term)).slice(0, 10);
}

// =====================
// Utilidades
// =====================
function safe(val) {
  return val === undefined ? null : val;
}

function generarNumeroPetbio(codigo_postal = '00000') {
  const fecha = new Date();
  const dd = String(fecha.getDate()).padStart(2, '0');
  const mm = String(fecha.getMonth() + 1).padStart(2, '0');
  const yyyy = fecha.getFullYear();
  const numero_aleatorio = String(Math.floor(Math.random() * 1000000)).padStart(6, '0');
  return `${codigo_postal}${dd}${mm}${yyyy}${numero_aleatorio}`;
}

// =====================
// Guardar imagen
// =====================
async function guardarImagen(texto) {
  if (!texto) throw new Error('❌ No enviaste URL ni archivo');

  let rutaCompleta;
  try {
    new URL(texto); // Si es URL válida
    const nombreArchivo = `perfil_${Date.now()}.jpg`;
    rutaCompleta = path.join(UPLOADS_DIR, nombreArchivo);
    const response = await axios.get(texto, { responseType: 'arraybuffer' });
    fs.writeFileSync(rutaCompleta, response.data);
  } catch (e) {
    const posibleRuta = path.join(UPLOADS_DIR, texto);
    if (fs.existsSync(posibleRuta)) {
      rutaCompleta = posibleRuta;
    } else {
      throw new Error('❌ URL o archivo inválido para la imagen');
    }
  }

  return rutaCompleta;
}

// =====================
// Enviar imágenes a Python
// =====================
function enviarImagenesPython(id_mascota, imagenes) {
  const payload = JSON.stringify({ id_mascota, imagenes });
  MQTT_CLIENT.publish(`entrenar_mascota/${id_mascota}`, payload);
}

// =====================
// Generar PDF Node.js
// =====================
async function generarPDFMascota(mascota) {
  const pdfPath = path.join(DOCUMENTS_DIR, `cedula_petbio_${mascota.id_mascota}_${Date.now()}.pdf`);
  const doc = new PDFDocument({ size: [285.6, 180], layout: 'landscape' });
  doc.pipe(fs.createWriteStream(pdfPath));

  doc.rect(0, 0, 285.6, 180).fill('#F5F0DC');
  doc.fillColor('#27445D').font('Helvetica-Bold').fontSize(14);
  doc.text('CÉDULA BIOMÉTRICA PETBIO', 0, 10, { align: 'center' });

  doc.fillColor('#000').font('Helvetica').fontSize(10);
  const startY = 40;
  doc.text(`ID PETBIO: ${mascota.numero_documento_petbio.slice(-6)}`, 20, startY);
  doc.text(`Nombre: ${mascota.nombre}`, 20, startY + 15);
  doc.text(`Raza: ${mascota.raza}`, 20, startY + 30);
  doc.text(`Edad: ${mascota.edad} años`, 20, startY + 45);

  if (mascota.ruta_img_perfil && fs.existsSync(mascota.ruta_img_perfil)) {
    doc.image(mascota.ruta_img_perfil, 180, startY, { width: 80, height: 80 });
  }

  doc.end();
  return pdfPath;
}

// =====================
// Generar PDF Python
// =====================
function generarPDFPython(payload) {
  return new Promise((resolve, reject) => {
    const scriptPath = '/app/biometria_ai/generar_cedula_bot.py';
    execFile('python3', [scriptPath, JSON.stringify(payload)], (error, stdout) => {
      if (error) return reject(error);
      try {
        const result = JSON.parse(stdout);
        resolve(result.cedula_path);
      } catch (e) {
        reject(e);
      }
    });
  });
}

// =====================
// Registrar mascota en DB
// =====================
async function registrarMascota(data, archivos) {
  const connection = await mysql.createConnection(dbConfig);
  try {
    const numero_documento_petbio = generarNumeroPetbio(data.codigo_postal);

    const sql = `
      INSERT INTO registro_mascotas (
        id_usuario, nombre, apellidos, raza, edad, documento_pasaporte, relacion, con_documento,
        tipo_documento, descripcion_documento, entidad_expedidora, numero_documento_externo,
        numero_documento, barrio, ciudad, ciudad_y_barrio, codigo_postal,
        clase_mascota, condicion_mascota, lat, lng, ruta_img_perfil
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `;

    const values = [
      safe(data.id_usuario),
      safe(data.nombre),
      safe(data.apellidos),
      safe(data.raza),
      safe(data.edad),
      safe(numero_documento_petbio.slice(-10)),
      safe(data.relacion || 'Propietario'),
      safe(data.con_documento || 'No'),
      safe(data.tipo_documento || 'Cédula Biométrica'),
      safe(data.descripcion_documento || 'RUBM - RNBM'),
      safe(data.entidad_expedidora || 'PETBIO11 RNBM'),
      safe(data.numero_documento_externo || numero_documento_petbio.slice(-10)),
      safe(numero_documento_petbio),
      safe(data.barrio || 'Barrio Ejemplo'),
      safe(data.ciudad || 'Ciudad Ejemplo'),
      safe(`${data.ciudad || 'Ciudad'} - ${data.barrio || 'Barrio'}`),
      safe(data.codigo_postal || '00000'),
      safe(data.clase_mascota || 'Perro'),
      safe(data.condicion_mascota || 'Sana'),
      safe(data.lat || null),
      safe(data.lng || null),
      safe(archivos.perfil)
    ];

    const [result] = await connection.execute(sql, values);
    const id_mascota = result.insertId;

    const pdfNodePath = await generarPDFMascota({
      ...data,
      id_mascota,
      numero_documento_petbio,
      ruta_img_perfil: archivos.perfil
    });

    const pdfPythonPath = await generarPDFPython({
      ...data,
      id_usuario: data.id_usuario,
      numero_documento_petbio,
      ruta_img_perfil: archivos.perfil
    });

    await connection.execute(
      'UPDATE registro_mascotas SET ruta_pdf = ?, ruta_pdf_python = ? WHERE id = ?',
      [pdfNodePath, pdfPythonPath, id_mascota]
    );

    enviarImagenesPython(id_mascota, Object.values(archivos).filter(Boolean));

    return { id_mascota, numero_documento_petbio, pdfNodePath, pdfPythonPath };
  } finally {
    await connection.end();
  }
}

// =====================
// Funciones de suscripción
// =====================
async function calcularTarifaConDescuento(planBase, meses) {
  let descuento = 0;
  if (meses === 3) descuento = 0.15;
  else if (meses === 6) descuento = 0.25;
  else if (meses === 12) descuento = 0.35;

  return planBase - (planBase * descuento);
}

async function registrarSuscripcion(data) {
  const connection = await mysql.createConnection(dbConfig);
  try {
    const fecha = new Date();
    const sql = `
      INSERT INTO pago_suscripcion
      (nombres, apellidos, documento, telefono, whatsapp, evidencia_pago, fecha_registro)
      VALUES (?, ?, ?, ?, ?, ?, ?)
    `;
    const values = [
      safe(data.nombres),
      safe(data.apellidos),
      safe(data.documento),
      safe(data.telefono),
      safe(data.whatsapp ? 1 : 0),
      safe(data.evidencia_pago),
      fecha
    ];
    const [result] = await connection.execute(sql, values);
    return { id_suscripcion: result.insertId, fecha };
  } finally {
    await connection.end();
  }
}

// =====================
// Exportamos funciones
// =====================
module.exports = {
  sugerirRaza,
  guardarImagen,
  registrarMascota,
  generarNumeroPetbio,
  generarPDFMascota,
  generarPDFPython,
  enviarImagenesPython,
  // Suscripciones
  calcularTarifaConDescuento,
  registrarSuscripcion
};
