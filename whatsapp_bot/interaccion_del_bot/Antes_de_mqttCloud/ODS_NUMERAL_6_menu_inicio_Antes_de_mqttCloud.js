// menu_inicio.js
const fs = require('fs');

/**
 * Función que muestra el menú inicial de PETBIO
 * y devuelve un handler para procesar la opción seleccionada.
 * @param {object} msg - Objeto del mensaje de WhatsApp
 * @param {string} sessionFile - Ruta del archivo de sesión
 * @returns {function} handleOption - Función para manejar la opción seleccionada
 */
async function menuInicio(msg, sessionFile) {
    const menu =
        "📋 *Menú PETBIO*\n\n" +
        "1️⃣ Identidad Corporativa\n" +
        "2️⃣ Políticas de tratamiento de datos\n" +
        "3️⃣ Registro de usuario\n" +
        "4️⃣ Registro de mascotas\n" +
        "5️⃣ Redes sociales\n" +
        "6️⃣ ODS PETBIO 🌍\n\n" +
        "👉 Responde con el número de la opción que deseas consultar.";

    // Enviar menú al usuario
    await msg.reply(menu);

    // Función que maneja la opción seleccionada
    const handleOption = async (option, msg, sessionFile) => {
        switch(option){
            case '1':
                // Mostrar menú de identidad corporativa
                const menuIdentidadCorporativa = require('./menu_identidad_corporativa');
                await menuIdentidadCorporativa(msg);
                break;

            case '2':
                // Políticas de tratamiento de datos
                await msg.reply('🔒 Estás a punto de abrir el enlace de políticas de tratamiento de datos:');
                await msg.reply('📜 https://siac2025.com/');
                break;

            case '3':
                // Registro de usuario
                fs.writeFileSync(sessionFile, JSON.stringify({ type:'registro_usuario', step:'username', data:{} }));
                await msg.reply('👤 Registro de usuario — escribe tu nombre: (cancelar para abortar)');
                break;

            case '4':
                // Registro de mascota
                fs.writeFileSync(sessionFile, JSON.stringify({ type:'registro_mascota', step:'nombre', data:{} }));
                await msg.reply('🐶 Registro de mascota — escribe el nombre de la mascota: (cancelar para abortar)');
                break;

            case '5':
                // Redes sociales
                await msg.reply('🌐 Estás a punto de abrir nuestras redes sociales PETBIO:');
                await msg.reply('🔗 https://registro.siac2025.com/2025/02/11/48/');
                break;

            case '6':
                // ODS PETBIO
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
                break;

            default:
                await msg.reply('❌ No entendí. Escribe *menu* para ver las opciones.');
        }
    };

    return handleOption;
}

module.exports = menuInicio;
