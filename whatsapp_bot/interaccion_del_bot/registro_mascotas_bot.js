// registro_mascotas_bot.js
// Registro completo de mascotas para WhatsApp Bot PETBIO

const fs = require('fs');
const path = require('path');
const mysql = require('mysql2/promise');
const { execFile } = require('child_process');
const { safe } = require('./utils_bot');
const { mqttCloud } = require('../config');

const razas = require('./razas_perros.json');

// =====================
// Autocompletar raza
// =====================
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

[UPLOADS_DIR, PERFIL_DIR, PDF_NODE_DIR, PDF_PYTHON_DIR].forEach(dir => {
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
});

// =====================
// Configuración DB
// =====================
const dbConfig = {
    host: process.env.MYSQL_HOST || 'mysql_petbio_secure',
    user: process.env.MYSQL_USER || 'root',
    password: process.env.MYSQL_PASSWORD || 'R00t_Segura_2025!',
    database: process.env.MYSQL_DATABASE || 'db__produccion_petbio_segura_2025',
    port: Number(process.env.MYSQL_PORT) || 3306,
// 5 de  Octubre  se tenia en uso puerto 3310 sin exito en las opciones 3 y 4 apartir de la fecha cambiamos 3306
    charset: 'utf8mb4'
};

// =====================
// Generar número PETBIO único
// =====================
async function generarNumeroPetbio(codigo_postal) {
    const connection = await mysql.createConnection(dbConfig);
    let numero_documento_petbio = null;
    let intentos = 0;
    while (!numero_documento_petbio && intentos < 5) {
        intentos++;
        const fecha = new Date();
        const dd = String(fecha.getDate()).padStart(2, '0');
        const mm = String(fecha.getMonth() + 1).padStart(2, '0');
        const yyyy = fecha.getFullYear();
        const numero_aleatorio = String(Math.floor(Math.random() * 1000000)).padStart(6, '0');
        const temp_numero = `${codigo_postal}${dd}${mm}${yyyy}${numero_aleatorio}`;
        const [rows] = await connection.execute(
            'SELECT id FROM registro_mascotas WHERE numero_documento = ?',
            [temp_numero]
        );
        if (rows.length === 0) numero_documento_petbio = temp_numero;
    }
    await connection.end();
    if (!numero_documento_petbio) throw new Error('No se pudo generar número PETBIO único.');
    return numero_documento_petbio;
}

// =====================
// Guardar imagen
// =====================
async function guardarImagen(msgOrUrl, nombreArchivo, folder = PERFIL_DIR) {
    const extDefault = '.jpg';
    let filePath;

    if (msgOrUrl.hasMedia) {
        const media = await msgOrUrl.downloadMedia();
        if (!media || !media.data) throw new Error('No se pudo descargar la imagen');

        const allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!allowedMimes.includes(media.mimetype)) throw new Error('Formato de imagen inválido');

        const ext = '.' + media.mimetype.split('/')[1];
        filePath = path.join(folder, `${nombreArchivo}_${Date.now()}${ext}`);
        fs.writeFileSync(filePath, Buffer.from(media.data, 'base64'));
    }
    else if (typeof msgOrUrl === 'string' && msgOrUrl.startsWith('http')) {
        const https = require('https');
        const urlExt = path.extname(msgOrUrl).split('?')[0] || extDefault;
        filePath = path.join(folder, `${nombreArchivo}_${Date.now()}${urlExt}`);
        await new Promise((resolve, reject) => {
            https.get(msgOrUrl, res => {
                const fileStream = fs.createWriteStream(filePath);
                res.pipe(fileStream);
                fileStream.on('finish', resolve);
                fileStream.on('error', reject);
            }).on('error', reject);
        });
    }
    else if (typeof msgOrUrl === 'string' && msgOrUrl.startsWith('data:image')) {
        const base64Data = msgOrUrl.split(',')[1];
        filePath = path.join(folder, `${nombreArchivo}_${Date.now()}${extDefault}`);
        fs.writeFileSync(filePath, Buffer.from(base64Data, 'base64'));
    } else {
        throw new Error('Formato de imagen inválido');
    }

    return filePath;
}

// =====================
// Generar PDF Node
// =====================
async function generarPDFNode(data) {
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

        if (data.con_documento !== 'Sí') {
            data.tipo_documento = 'Cédula Biométrica';
            data.descripcion_documento = 'RUBM - RNBM';
            data.entidad_expedidora = 'PETBIO11 RNBM';
            data.numero_documento_externo = numero_documento_petbio.slice(-10);
        }

        const sql = `
            INSERT INTO registro_mascotas (
                id_usuario, nombre, apellidos, raza, edad, documento_pasaporte, relacion, con_documento,
                tipo_documento, descripcion_documento, entidad_expedidora, numero_documento_externo,
                numero_documento, barrio, ciudad, ciudad_y_barrio, codigo_postal,
                clase_mascota, condicion_mascota, lat, lng,
                created_at, ruta_img_hf0, ruta_img_hf15, ruta_img_hf30, ruta_img_hfld15,
                ruta_img_hfli15, ruta_img_perfil, ruta_img_latdr, ruta_img_latiz
            ) VALUES (
                ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),?,?,?,?,?,?,?,?
            )
        `;
        const values = [
            safe(data.id_usuario),
            safe(data.nombre),
            safe(data.apellidos),
            safe(data.raza),
            safe(data.edad),
            safe(numero_documento_petbio.slice(-10)),
            safe(data.relacion),
            safe(data.con_documento),
            safe(data.tipo_documento),
            safe(data.descripcion_documento),
            safe(data.entidad_expedidora),
            safe(data.numero_documento_externo),
            safe(numero_documento_petbio),
            safe(data.barrio),
            safe(data.ciudad),
            safe(`${data.ciudad} - ${data.barrio}`),
            safe(data.codigo_postal),
            safe(data.clase_mascota),
            safe(data.condicion_mascota),
            safe(data.lat),
            safe(data.lng),
            safe(archivos.hf0),
            safe(archivos.hf15),
            safe(archivos.hf30),
            safe(archivos.hfld15),
            safe(archivos.hfli15),
            safe(archivos.perfil),
            safe(archivos.latdr),
            safe(archivos.latiz)
        ];
        const [result] = await connection.execute(sql, values);
        const id_mascota = result.insertId;

        // Generar PDFs
        const pdfNode = await generarPDFNode({ ...data, id_mascota, numero_documento_petbio });
        const pdfPython = await generarPDFPython({ ...data, id_mascota, numero_documento_petbio });

        // Actualizar rutas de PDF en DB
        await connection.execute(
            'UPDATE registro_mascotas SET ruta_pdf = ?, ruta_pdf_python = ? WHERE id = ?',
            [pdfNode, pdfPython, id_mascota]
        );

        // Publicar imágenes a CloudMQTT
        mqttCloud.publish('petbio/imagenes', JSON.stringify({
            id_mascota,
            fotos: archivos
        }));

        return { id_mascota, pdfNode, pdfPython, numero_documento_petbio };
    } finally {
        await connection.end();
    }
}

module.exports = {
    sugerirRaza,
    guardarImagen,
    registrarMascota,
    generarPDFNode,
    generarPDFPython
};
