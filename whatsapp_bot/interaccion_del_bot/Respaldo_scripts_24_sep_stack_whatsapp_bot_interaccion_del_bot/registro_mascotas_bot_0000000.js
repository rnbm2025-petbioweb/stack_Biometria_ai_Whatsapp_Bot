// registro_mascotas_bot.js
const fs = require('fs');
const path = require('path');
const mysql = require('mysql2/promise');
const mqtt = require('mqtt');
const { execFile } = require('child_process');

// Import utils
const { safe, generarNumeroPetbio, guardarImagen, generarPDFMascota } = require('./utils_bot');

// Autocompletar raza
const razas = require('./razas_perros.json');
function sugerirRaza(input) {
    const term = input.toLowerCase();
    return razas.filter(r => r.toLowerCase().includes(term)).slice(0, 10);
}

// =====================
// Configuración DB y MQTT
// =====================
const dbConfig = {
    host: process.env.MYSQL_HOST || '127.0.0.1',
    user: process.env.MYSQL_USER || 'root',
    password: process.env.MYSQL_PASSWORD || 'R00t_Segura_2025!',
    database: process.env.MYSQL_DATABASE || 'db__produccion_petbio_segura_2025',
    port: 3310
};
const MQTT_BROKER = process.env.MQTT_BROKER || 'mqtt://mosquitto:1883';
const MQTT_CLIENT = mqtt.connect(MQTT_BROKER);

function enviarImagenesPython(id_mascota, imagenes) {
    const payload = JSON.stringify({ id_mascota, imagenes });
    MQTT_CLIENT.publish(`entrenar_mascota/${id_mascota}`, payload);
}

// =====================
// PDF con Python
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
// Registrar mascota
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
            safe(data.id_usuario), safe(data.nombre), safe(data.apellidos),
            safe(data.raza), safe(data.edad),
            safe(numero_documento_petbio.slice(-10)), safe(data.relacion || 'Propietario'),
            safe(data.con_documento || 'No'), safe(data.tipo_documento || 'Cédula Biométrica'),
            safe(data.descripcion_documento || 'RUBM - RNBM'), safe(data.entidad_expedidora || 'PETBIO11 RNBM'),
            safe(data.numero_documento_externo || numero_documento_petbio.slice(-10)),
            safe(numero_documento_petbio),
            safe(data.barrio || 'Barrio Ejemplo'), safe(data.ciudad || 'Ciudad Ejemplo'),
            safe(`${data.ciudad || 'Ciudad Ejemplo'} - ${data.barrio || 'Barrio Ejemplo'}`),
            safe(data.codigo_postal || '00000'), safe(data.clase_mascota || 'Perro'),
            safe(data.condicion_mascota || 'Sana'), safe(data.lat || null), safe(data.lng || null),
            safe(archivos.perfil)
        ];

        const [result] = await connection.execute(sql, values);
        const id_mascota = result.insertId;

        const pdfNodePath = await generarPDFMascota({ ...data, id_mascota, numero_documento_petbio, ruta_img_perfil: archivos.perfil });
        const pdfPythonPath = await generarPDFPython({ ...data, numero_documento_petbio, ruta_img_perfil: archivos.perfil });

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
// Flujo WhatsApp
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
                await msg.reply('📸 Envía la foto de perfil de la mascota (URL o archivo).');
                break;

            case 'fotos':
                try {
                    const rutaPerfil = await guardarImagen(texto);
                    const archivos = { perfil: rutaPerfil };
                    data.id_usuario = session.id_usuario;
                    const resultado = await registrarMascota(data, archivos);

                    await msg.reply(
                        `✅ Mascota registrada con ID PETBIO: ${resultado.numero_documento_petbio}\n` +
                        `📄 PDF Node: ${resultado.pdfNodePath}\n📄 PDF Python: ${resultado.pdfPythonPath}`
                    );

                    session.type = 'menu'; session.step = null; session.data = null;
                } catch(e) {
                    await msg.reply(`❌ Error con la imagen: ${e.message}`);
                }
                break;
        }

        session.data = data;
        fs.writeFileSync(sessionFile, JSON.stringify(session));
    } catch (e) {
        console.error('Error en registro_mascotas:', e);
        await msg.reply('❌ Ocurrió un error, volviendo al menú...');
        session.type = 'menu'; session.step = null; session.data = null;
        fs.writeFileSync(sessionFile, JSON.stringify(session));
    }
}

module.exports = { registrarMascota, iniciarRegistroMascota, sugerirRaza };
