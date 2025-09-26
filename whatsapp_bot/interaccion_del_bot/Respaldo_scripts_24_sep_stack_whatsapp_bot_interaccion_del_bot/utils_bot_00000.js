// utils_bot.js
const fs = require('fs');
const path = require('path');
const axios = require('axios');
const PDFDocument = require('pdfkit');
const { URL } = require('url');

// Directorios globales
const UPLOADS_DIR = path.join(__dirname, '../WhatsApp_bot_storage/uploads/mascotas');
if (!fs.existsSync(UPLOADS_DIR)) fs.mkdirSync(UPLOADS_DIR, { recursive: true });

const DOCUMENTS_DIR = path.join(__dirname, '../WhatsApp_bot_storage/documentos_bot');
if (!fs.existsSync(DOCUMENTS_DIR)) fs.mkdirSync(DOCUMENTS_DIR, { recursive: true });

function safe(val) {
    return val === undefined ? null : val;
}

function generarNumeroPetbio(codigo_postal) {
    const fecha = new Date();
    const dd = String(fecha.getDate()).padStart(2, '0');
    const mm = String(fecha.getMonth() + 1).padStart(2, '0');
    const yyyy = fecha.getFullYear();
    const numero_aleatorio = String(Math.floor(Math.random() * 1000000)).padStart(6, '0');
    return `${codigo_postal}${dd}${mm}${yyyy}${numero_aleatorio}`;
}

// Guardar imagen desde URL o archivo local
async function guardarImagen(texto) {
    if (!texto) throw new Error('❌ No enviaste URL ni archivo');
    let rutaCompleta;

    try {
        new URL(texto); // valida si es URL
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

// Generar PDF con texto justificado
async function generarPDFMascota(mascota) {
    const pdfPath = path.join(DOCUMENTS_DIR, `cedula_petbio_${mascota.id_mascota}_${Date.now()}.pdf`);
    const doc = new PDFDocument({ size: [285.6, 180], layout: 'landscape' });
    doc.pipe(fs.createWriteStream(pdfPath));

    doc.rect(0, 0, 285.6, 180).fill('#F5F0DC');
    doc.fillColor('#27445D').font('Helvetica-Bold').fontSize(14);
    doc.text('CÉDULA BIOMÉTRICA PETBIO', 0, 10, { align: 'center' });

    doc.fillColor('#000').font('Helvetica').fontSize(9);
    const startY = 40;

    // Texto justificado
    doc.text(`ID PETBIO: ${mascota.numero_documento_petbio.slice(-6)}`, 20, startY, { align: 'justify' });
    doc.text(`Nombre: ${mascota.nombre}`, 20, startY + 15, { align: 'justify' });
    doc.text(`Raza: ${mascota.raza}`, 20, startY + 30, { align: 'justify' });
    doc.text(`Edad: ${mascota.edad} años`, 20, startY + 45, { align: 'justify' });

    if (mascota.ruta_img_perfil && fs.existsSync(mascota.ruta_img_perfil)) {
        doc.image(mascota.ruta_img_perfil, 180, startY, { width: 80, height: 80 });
    }

    doc.end();
    return pdfPath;
}

module.exports = {
    safe,
    generarNumeroPetbio,
    guardarImagen,
    generarPDFMascota,
    UPLOADS_DIR,
    DOCUMENTS_DIR
};
