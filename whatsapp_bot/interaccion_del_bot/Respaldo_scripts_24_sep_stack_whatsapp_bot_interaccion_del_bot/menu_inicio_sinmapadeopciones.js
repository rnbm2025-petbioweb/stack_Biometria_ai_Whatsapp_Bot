// menu_inicio.js
const fs = require('fs');
const { justificarTexto } = require('./utils_bot');

async function menuInicio(msg, sessionFile) {
    const menu =
        "📋 *Menú PETBIO*\n\n" +
        "1️⃣ Identidad Corporativa\n" +
        "2️⃣ Políticas de tratamiento de datos\n" +
        "3️⃣ Registro de usuario\n" +
        "4️⃣ Registro de mascotas\n" +
        "5️⃣ Redes sociales\n" +
        "6️⃣ ODS PETBIO 🌍\n" +
        "7️⃣ Tarifas 💲\n" +
        "8️⃣ Servicios 📦\n\n" +
        "👉 Responde con el número de la opción que deseas consultar.";

    await msg.reply(justificarTexto(menu, 40));

    const handleOption = async (option, msg, sessionFile) => {
        switch(option){
            case '1':
                const menuIdentidadCorporativa = require('./menu_identidad_corporativa');
                await menuIdentidadCorporativa(msg);
                break;

            case '2':
                await msg.reply(justificarTexto(
                    "🔒 Estás a punto de abrir el enlace de políticas de tratamiento de datos:\n📜 https://siac2025.com/"
                ));
                break;

            case '3':
                fs.writeFileSync(sessionFile, JSON.stringify({ type:'registro_usuario', step:'username', data:{} }));
                await msg.reply(justificarTexto(
                    "👤 Registro de usuario — escribe tu nombre:\n(escribe *cancelar* para abortar)"
                ));
                break;

            case '4':
                fs.writeFileSync(sessionFile, JSON.stringify({ type:'registro_mascota', step:'nombre', data:{} }));
                await msg.reply(justificarTexto(
                    "🐶 Registro de mascota — escribe el nombre de la mascota:\n(escribe *cancelar* para abortar)"
                ));
                break;

            case '5':
                await msg.reply(justificarTexto(
                    "🌐 Estás a punto de abrir nuestras redes sociales PETBIO:\n🔗 https://registro.siac2025.com/2025/02/11/48/"
                ));
                break;

            case '6':
                // 🔗 Delegar al script especializado de ODS
                const menuODS = require('./ODS_NUMERAL_6_menu_inicio');
                await menuODS(msg, sessionFile);
                break;

            case '7':
                const menuTarifas = require('./tarifas_menu'); // <- puedes mover tarifas a su propio archivo
                await menuTarifas(msg, sessionFile);
                break;

            case '8':
                const menuServicios = require('./servicios_menu'); // <- también lo puedes separar
                await menuServicios(msg, sessionFile);
                break;

            default:
                await msg.reply(justificarTexto(
                    "✅ WhatsApp Cliente PETBIO Bienvenidos.\nEscribe *menu* para ver las opciones."
                ));
        }
    };

    return handleOption;
}

module.exports = menuInicio;
