// menu_inicio.js
const fs = require('fs');
const path = require('path');
const utils = require('./utils_bot');

// Importar módulos de menú
const menuIdentidadCorporativa = require('./menu_identidad_corporativa');
const menuODS = require('./ODS_NUMERAL_6_menu_inicio');
const menuTarifas = require('./tarifas_menu');
const menuServicios = require('./servicios_menu');

// Texto del menú principal
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

👉 Responde con el número de la opción que deseas consultar.
`;

async function menuInicio(msg, sessionFile) {
    // Mostrar el menú al usuario
    await msg.reply(utils.justificarTexto(MENU_TEXT, 40));

    // Asegurarse de que el directorio de sesión exista
    const sessionDir = path.dirname(sessionFile);
    if (!fs.existsSync(sessionDir)) {
        fs.mkdirSync(sessionDir, { recursive: true });
    }

    // Mapa de opciones
    const opciones = {
        '1': async () => menuIdentidadCorporativa(msg),
        '2': async () => msg.reply(utils.justificarTexto(
            "🔒 Estás a punto de abrir el enlace de políticas de tratamiento de datos:\n📜 https://siac2025.com/"
        )),
        '3': async () => {
            fs.writeFileSync(sessionFile, JSON.stringify({ type: 'registro_usuario', step: 'username', data: {} }, null, 2));
            await msg.reply(utils.justificarTexto(
                "👤 Registro de usuario — escribe tu nombre:\n(escribe *cancelar* para abortar)"
            ));
        },
        '4': async () => {
            fs.writeFileSync(sessionFile, JSON.stringify({ type: 'registro_mascota', step: 'nombre', data: {} }, null, 2));
            await msg.reply(utils.justificarTexto(
                "🐶 Registro de mascota — escribe el nombre de la mascota:\n(escribe *cancelar* para abortar)"
            ));
        },
        '5': async () => msg.reply(utils.justificarTexto(
            "🌐 Estás a punto de abrir nuestras redes sociales PETBIO:\n🔗 https://registro.siac2025.com/2025/02/11/48/"
        )),
        '6': async () => menuODS(msg, sessionFile),
        '7': async () => menuTarifas(msg, sessionFile),
        '8': async () => menuServicios(msg, sessionFile),
    };

    // Función para manejar la opción elegida
    const handleOption = async (option) => {
        if (opciones[option]) {
            await opciones[option]();
        } else {
            await msg.reply(utils.justificarTexto(
                "✅ WhatsApp Cliente PETBIO Bienvenidos.\nEscribe *menu* para ver las opciones."
            ));
        }
    };

    return handleOption;
}

module.exports = menuInicio;

