// registro_mascotas_bot.js
// Registro de mascotas para WhatsApp Bot PETBIO (versión completa y optimizada)

const fs = require('fs');
const path = require('path');
const mysql = require('mysql2/promise');
const mqtt = require('mqtt');
const { execFile } = require('child_process');
const { safe, generarNumeroPetbio } = require('./utils_bot');

// --- Autocompletar raza ---
const razas = require('./razas_perros.json');
function sugerirRaza(input) {
    const term = input.toLowerCase();
    return razas.filter(r => r.toLowerCase().includes(term)).slice(0, 10);
}

// =====================
// Directorios base
// =====================
const UPLOADS_DIR = path.join(__dirname, '../WhatsApp_bot_storage/uploads/mascotas');
const PERFIL_DIR = path.join(UPLOADS_DIR, 'fotos_perfil');
const PDF_NODE_DIR = path.join(UPLOADS_DIR, 'pdfs_node');
const PDF_PYTHON_DIR = path.join(UPLOADS_DIR, 'pdfs_python');

// Crear directorios si no existen
[UPLOADS_DIR, PERFIL_DIR, PDF_NODE_DIR, PDF_PYTHON_DIR].forEach(dir => {
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
});

// =====================
// Configuración DB y MQTT
// =====================
const dbConfig = {
    host: process.env.MYSQL_HOST || '127.0.0.1',
    user: process.env.MYSQL_USER || 'josornol341',
    password: process.env.MYSQL_PASSWORD || 'R00t_Segura_2025!',
    database: process.env.MYSQL_DATABASE || 'db__produccion_petbio_segura_2025',
    port: 3310
};
const MQTT_BROKER = process.env.MQTT_BROKER || 'mqtt://mosquitto:1883';
const MQTT_CLIENT = mqtt.connect(MQTT_BROKER);

// =====================
// Función para enviar imágenes a Python vía MQTT
// =====================
function enviarImagenesPython(id_mascota, imagenes) {
    const payload = JSON.stringify({ id_mascota, imagenes });
    MQTT_CLIENT.publish(`entrenar_mascota/${id_mascota}`, payload);
}

// =====================
// Guardar imagen mascota
// =====================
async function guardarImagenMascota(msgOrUrl, nombreArchivo) {
    const extDefault = '.jpg';
    let filePath;

    // 1️⃣ Mensaje de WhatsApp con media
    if (msgOrUrl.hasMedia) {
        const media = await msgOrUrl.downloadMedia();
        if (!media || !media.data) throw new Error('No se pudo descargar la imagen');
        const ext = media.mimetype ? '.' + media.mimetype.split('/')[1] : extDefault;
        filePath = path.join(PERFIL_DIR, `${nombreArchivo}_${Date.now()}${ext}`);
        fs.writeFileSync(filePath, Buffer.from(media.data, 'base64'));
    }
    // 2️⃣ URL de imagen
    else if (typeof msgOrUrl === 'string' && msgOrUrl.startsWith('http')) {
        const https = require('https');
        const urlExt = path.extname(msgOrUrl).split('?')[0] || extDefault;
        filePath = path.join(PERFIL_DIR, `${nombreArchivo}_${Date.now()}${urlExt}`);
        await new Promise((resolve, reject) => {
            https.get(msgOrUrl, res => {
                const fileStream = fs.createWriteStream(filePath);
                res.pipe(fileStream);
                fileStream.on('finish', () => resolve());
            }).on('error', reject);
        });
    }
    // 3️⃣ Base64
    else if (typeof msgOrUrl === 'string' && msgOrUrl.startsWith('data:image')) {
        const base64Data = msgOrUrl.split(',')[1];
        filePath = path.join(PERFIL_DIR, `${nombreArchivo}_${Date.now()}${extDefault}`);
        fs.writeFileSync(filePath, Buffer.from(base64Data, 'base64'));
    }
    else {
        throw new Error('Formato de imagen inválido');
    }

    return filePath;
}

// =====================
// Generar PDF Node
// =====================
async function generarPDFMascota(data) {
    const PDFDocument = require('pdfkit');
    const pdfPath = path.join(PDF_NODE_DIR, `PETBIO_MASCOTA_${data.id_mascota}.pdf`);
    const doc = new PDFDocument({ size: 'A4', margin: 50 });
    const writeStream = fs.createWriteStream(pdfPath);
    doc.pipe(writeStream);

    doc.fontSize(20).text('CERTIFICADO PETBIO - MASCOTA', { align: 'center' });
    doc.moveDown();
    doc.fontSize(14).text(`ID Mascota: ${data.numero_documento_petbio}`);
    doc.text(`Nombre: ${data.nombre} ${data.apellidos || ''}`);
    doc.text(`Raza: ${data.raza}`);
    doc.text(`Edad: ${data.edad}`);
    doc.text(`Tipo de documento: ${data.tipo_documento || 'Cédula Biométrica'}`);
    doc.text(`Número: ${data.numero_documento_petbio}`);
    doc.moveDown();
    doc.text(`📌 Este certificado garantiza el registro de tu mascota en PETBIO.`);
    doc.moveDown();
    doc.text(`🌐 Plataforma: https://petbio.siac2025.com/identidad_rubm.php`, { link: 'https://petbio.siac2025.com/identidad_rubm.php', underline: true });

    doc.end();
    return new Promise(resolve => writeStream.on('finish', () => resolve(pdfPath)));
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
        const numero_documento_petbio = await generarNumeroPetbio(data.codigo_postal);

        const sql = `
            INSERT INTO registro_mascotas (
                id_usuario, nombre, apellidos, raza, edad, numero_documento,
                clase_mascota, condicion_mascota, ruta_img_perfil
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        `;
        const values = [
            safe(data.id_usuario), safe(data.nombre), safe(data.apellidos),
            safe(data.raza), safe(data.edad), safe(numero_documento_petbio),
            safe(data.clase_mascota || 'Perro'), safe(data.condicion_mascota || 'Sana'),
            safe(archivos.perfil)
        ];

        const [result] = await connection.execute(sql, values);
        const id_mascota = result.insertId;

        // Generar PDFs
        const pdfNodePath = await generarPDFMascota({ ...data, id_mascota, numero_documento_petbio, ruta_img_perfil: archivos.perfil });
        const pdfPythonPath = await generarPDFPython({ ...data, numero_documento_petbio, ruta_img_perfil: archivos.perfil });

        await connection.execute(
            'UPDATE registro_mascotas SET ruta_pdf = ?, ruta_pdf_python = ? WHERE id = ?',
            [pdfNodePath, pdfPythonPath, id_mascota]
        );

        // Enviar imágenes a Python vía MQTT
        enviarImagenesPython(id_mascota, Object.values(archivos).filter(Boolean));

        return { id_mascota, numero_documento_petbio, pdfNodePath, pdfPythonPath };
    } finally {
        await connection.end();
    }
}

// =====================
// Flujo WhatsApp paso a paso
// =====================
async function iniciarRegistroMascota(msg, session, sessionFile) {
    const step = session.step || 'nombre';
    const data = session.data || {};
    const texto = (msg.body || '').trim();

    try {
        if (texto.toLowerCase() === 'cancelar') {
            session.type = 'menu';
            fs.writeFileSync(sessionFile, JSON.stringify(session));
            return msg.reply('✅ Registro cancelado. Volviendo al menú...');
        }

        switch(step) {
            case 'nombre':
                if (!/^.{1,50}$/.test(texto)) return msg.reply('❌ Nombre inválido. Intenta nuevamente:');
                data.nombre = texto;
                session.step = 'edad';
                await msg.reply('📌 Escribe la edad de la mascota:');
                break;

            case 'edad':
                if (!/^[0-9]{1,2}$/.test(texto)) return msg.reply('❌ Edad inválida. Ingresa un número:');
                data.edad = texto;
                session.step = 'raza';
                await msg.reply('📌 Escribe la raza de la mascota:');
                break;

            case 'raza':
                data.raza = sugerirRaza(texto)[0] || texto;
                session.step = 'fotos';
                await msg.reply('📸 Envía la foto de perfil de la mascota (URL, archivo o base64).');
                break;

            case 'fotos':
                try {
                    const rutaPerfil = await guardarImagenMascota(msg, data.nombre);
                    const archivos = { perfil: rutaPerfil };
                    data.id_usuario = session.id_usuario;

                    await msg.reply('✅ Datos confirmados. Registrando mascota... ⏳');
                    const resultado = await registrarMascota(data, archivos);

                    await msg.reply(
                        `✅ Mascota registrada con ID PETBIO: ${resultado.numero_documento_petbio}\n` +
                        `📄 PDF Node: ${resultado.pdfNodePath}\n📄 PDF Python: ${resultado.pdfPythonPath}`
                    );

                    session.type = 'menu';
                    session.step = null;
                    session.data = null;

                } catch(e) {
                    console.error('Error al registrar mascota:', e);
                    await msg.reply(`❌ Error con la imagen o registro: ${e.message}`);
                }
                break;
        }

        session.data = data;
        fs.writeFileSync(sessionFile, JSON.stringify(session));

    } catch (e) {
        console.error('Error en flujo WhatsApp registro_mascota:', e);
        await msg.reply('❌ Ocurrió un error, volviendo al menú...');
        session.type = 'menu';
        session.step = null;
        session.data = null;
        fs.writeFileSync(sessionFile, JSON.stringify(session));
    }
}

// =====================
// Exportar funciones
// =====================
module.exports = { registrarMascota, iniciarRegistroMascota, sugerirRaza };
