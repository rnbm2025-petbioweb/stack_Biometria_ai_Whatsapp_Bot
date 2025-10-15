// ODS_NUMERAL_6_menu_inicio.js
const fs = require('fs');
const path = require('path');
const { mqttCloud } = require('../config.js'); // cliente CloudMQTT
const menuIdentidadCorporativa = require('./menu_identidad_corporativa');

/**
 * Menú ODS PETBIO
 * @param {object} msg - Mensaje de WhatsApp
 * @param {string} sessionFile - Ruta del archivo de sesión
 * @returns {function} handleOption - Función para manejar la opción seleccionada
 */
async function menuInicio(msg, sessionFile) {
    const menu = `
📋 *Menú PETBIO* 🌍

1️⃣ Identidad Corporativa
2️⃣ Políticas de tratamiento de datos
3️⃣ Registro de usuario
4️⃣ Registro de mascotas
5️⃣ Redes sociales
6️⃣ ODS PETBIO

👉 Responde con el número de la opción que deseas consultar.
`;

    await msg.reply(menu);

    const handleOption = async (option, msg, sessionFile) => {
        try {
            switch(option){
                case '1':
                    await menuIdentidadCorporativa(msg);
                    publishMQTT("menu_interaccion", "Identidad Corporativa ODS", msg.from);
                    break;

                case '2':
                    await msg.reply('🔒 Estás a punto de abrir el enlace de políticas de tratamiento de datos:');
                    await msg.reply('📜 https://siac2025.com/');
                    publishMQTT("menu_interaccion", "Políticas de Datos ODS", msg.from);
                    break;

                case '3':
                    updateSession(sessionFile, { type:'registro_usuario', step:'username' });
                    await msg.reply('👤 Registro de usuario — escribe tu nombre: (cancelar para abortar)');
                    publishMQTT("menu_interaccion", "Registro Usuario ODS", msg.from);
                    break;

                case '4':
                    updateSession(sessionFile, { type:'registro_mascota', step:'nombre' });
                    await msg.reply('🐶 Registro de mascota — escribe el nombre de la mascota: (cancelar para abortar)');
                    publishMQTT("menu_interaccion", "Registro Mascota ODS", msg.from);
                    break;

                case '5':
                    await msg.reply('🌐 Estás a punto de abrir nuestras redes sociales PETBIO:');
                    await msg.reply('🔗 https://registro.siac2025.com/2025/02/11/48/');
                    publishMQTT("menu_interaccion", "Redes Sociales ODS", msg.from);
                    break;

                case '6':
                    await msg.reply(
`🌍 *ODS PETBIO - Adaptación a los Objetivos de Desarrollo Sostenible* 🌍

1️⃣ *ODS 3: Salud y Bienestar*
👉 Trazabilidad de vacunación y prevención de zoonosis con biometría y QR.
✅ Indicador: % de mascotas vacunadas registradas.

2️⃣ *ODS 9: Industria, Innovación e Infraestructura*
👉 Infraestructura escalable con CA ROOT, microservicios y dashboards en tiempo real.
✅ Indicador: N° de microservicios activos (uptime %).

3️⃣ *ODS 11: Ciudades y Comunidades Sostenibles*
👉 Identificación digital de mascotas para mejorar seguridad y convivencia comunitaria.
✅ Indicador: N° de municipios con el sistema activo.

4️⃣ *ODS 12: Producción y Consumo Responsables*
👉 Gestión de datos para campañas de esterilización, adopciones y control responsable.
✅ Indicador: N° de adopciones responsables y campañas optimizadas.

5️⃣ *ODS 16: Paz, Justicia e Instituciones Sólidas*
👉 Protección de datos con cifrado y estándares internacionales de transparencia.
✅ Indicador: N° de certificaciones en seguridad y auditorías.

6️⃣ *ODS 17: Alianzas para Lograr los Objetivos*
👉 Estrategia de colaboración con entidades públicas, privadas y académicas sin ceder capital.
✅ Indicador: N° de convenios firmados con aliados estratégicos.

⚡ PETBIO RNBM impulsa innovación, impacto social y sostenibilidad en cada etapa.`
                    );
                    publishMQTT("menu_interaccion", "ODS PETBIO", msg.from);
                    break;

                default:
                    await msg.reply('❌ No entendí. Escribe *menu* para ver las opciones.');
                    publishMQTT("menu_interaccion", "Opcion no valida ODS", msg.from);
            }
        } catch (err) {
            console.error(`[${new Date().toLocaleTimeString()}] Error en ODS menu:`, err);
        }
    };

    return handleOption;
}

// ==============================
// 📌 Función para publicar en CloudMQTT
// ==============================
function publishMQTT(topic, descripcion, usuario) {
    if (mqttCloud && mqttCloud.connected) {
        mqttCloud.publish(`petbio/${topic}`, JSON.stringify({
            usuario,
            descripcion,
            fecha: new Date().toISOString()
        }), { qos: 1 });
        console.log(`[${new Date().toLocaleTimeString()}] 🔹 MQTT publicado: ${topic} -> ${descripcion}`);
    }
}

// ==============================
// 📌 Función para actualizar sesión de manera segura
// ==============================
function updateSession(sessionFile, data) {
    let sessionData = {};
    if (fs.existsSync(sessionFile)) {
        try {
            sessionData = JSON.parse(fs.readFileSync(sessionFile));
        } catch { sessionData = {}; }
    }
    sessionData = { ...sessionData, ...data, lastActive: Date.now() };
    const sessionDir = path.dirname(sessionFile);
    if (!fs.existsSync(sessionDir)) fs.mkdirSync(sessionDir, { recursive: true });
    fs.writeFileSync(sessionFile, JSON.stringify(sessionData, null, 2));
}

module.exports = menuInicio;
