// tarifas_menu.js
const { justificarTexto } = require('./utils_bot');

/**
 * Muestra la información de tarifas PETBIO
 * @param {object} msg - Mensaje de WhatsApp
 */
async function menuTarifas(msg) {
    await msg.reply(justificarTexto(
`💲 *Tarifas PETBIO*

Ofrecemos planes de registro biométrico según el número de mascotas:

📌 *Trimestral* → desde 9.000 a 7.500 por mascota.
📌 *Semestral* → desde 17.000 a 13.000 por mascota.
📌 *Anual* → desde 30.000 a 23.000 por mascota.

✅ Escoge el plan según el rango de mascotas (1 a 500).
✅ Precios incluyen beneficios de trazabilidad, QR y servicios digitales.`
    ));

    await msg.reply("🌐 Consulta el simulador financiero aquí:\nhttps://petbio.siac2025.com/finanzas_suscripcion.php");
}

module.exports = menuTarifas;
