// menu_inicio.js
const fs = require('fs');
const path = require('path');
const utils = require('./utils_bot');

// Importar módulos de menú existentes
const menuIdentidadCorporativa = require('./menu_identidad_corporativa');
const menuODS = require('./ODS_NUMERAL_6_menu_inicio');
const menuTarifas = require('./tarifas_menu');
const menuServicios = require('./servicios_menu');
const suscripcionesCuidadores = require('./suscripciones_cuidadores_bot');

const MENU_TEXT = `
📋 *Menú PETBIO*

1️⃣ Identidad Corporativa
2️⃣ Políticas de tratamiento de datos
3️⃣ Registro de usuario
4️⃣ Registro de mascotas
5️⃣ Redes sociales
6️⃣ ODS PETBIO 🌍
7️⃣ Tarifas 💲
8️⃣ Servicios 📦
9️⃣ Suscripciones Cuidadores

👉 Responde con el número de la opción que deseas consultar.
`;

async function menuInicio(msg, sessionFile, session) {
    // Asegurar estructura mínima de session
    session.type = session.type || 'menu_inicio';
    session.step = session.step || null;
    session.data = session.data || {};
    session.lastActive = Date.now();
    session.lastGreeted = session.lastGreeted || false;

    // Guardar sesión actualizada
    fs.writeFileSync(sessionFile, JSON.stringify(session, null, 2));

    // Mostrar menú principal
    await msg.reply(utils.justificarTexto(MENU_TEXT, 40));

    // Directorio de sesión
    const sessionDir = path.dirname(sessionFile);
    if (!fs.existsSync(sessionDir)) fs.mkdirSync(sessionDir, { recursive: true });

    // Opciones del menú
    const opciones = {
        '1': async () => menuIdentidadCorporativa(msg),
        '2': async () => msg.reply(utils.justificarTexto(
            "🔒 Estás a punto de abrir el enlace de políticas de tratamiento de datos:\n📜 https://siac2025.com/"
        )),
        '3': async () => {
            session.type = 'registro_usuario';
            session.step = 'username';
            session.data = {};
            session.lastActive = Date.now();
            fs.writeFileSync(sessionFile, JSON.stringify(session, null, 2));
            await msg.reply(utils.justificarTexto(
                "👤 Registro de usuario — escribe tu nombre:\n(escribe *cancelar* para abortar)"
            ));
        },
        '4': async () => {
            session.type = 'registro_mascota';
            session.step = 'nombre';
            session.data = {};
            session.lastActive = Date.now();
            fs.writeFileSync(sessionFile, JSON.stringify(session, null, 2));
            await msg.reply(utils.justificarTexto(
                "🐶 Registro de mascota — escribe el nombre de la mascota:\n(escribe *cancelar* para abortar)"
            ));
        },
        '5': async () => msg.reply(utils.justificarTexto(
            "🌐 Estás a punto de abrir nuestras redes sociales PETBIO:\n🔗 https://registro.siac2025.com/2025/02/11/48/"
        )),
        '6': async () => menuODS(msg, sessionFile, session),
        '7': async () => menuTarifas(msg, sessionFile, session),
        '8': async () => menuServicios(msg, sessionFile),
        '9': async () => suscripcionesCuidadores(msg),
    };

    // Función para manejar la opción elegida
    const handleOption = async (option) => {
        if (opciones[option]) {
            await opciones[option]();
        } else {
            await msg.reply(utils.justificarTexto(
                "❌ Opción no válida. Aquí está el menú nuevamente:\n" + MENU_TEXT
            ));
        }
    };

    return handleOption;
}

module.exports = menuInicio;
