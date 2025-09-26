// servicios_menu.js
const { justificarTexto } = require('./utils_bot');

/**
 * Muestra la lista de servicios PETBIO
 * @param {object} msg - Mensaje de WhatsApp
 */
async function menuServicios(msg) {
    await msg.reply(justificarTexto(
`📦 *Servicios PETBIO*

1️⃣ Documento de identidad digital para tu mascota.
2️⃣ Registro biométrico (huella, rostro, QR).
3️⃣ Historia clínica y de viajes.
4️⃣ Servicios de guardería y acompañamiento.
5️⃣ Acceso a productos y servicios con aliados.
6️⃣ Certificación y regulación con *PETBIO CA_ROOT*.
7️⃣ Servicios digitales y de ciberseguridad.
8️⃣ Desarrollo de software contable y empresarial.
9️⃣ Y muchos más, siempre en evolución 🚀

💡 Las *tarifas* se consultan en la opción *7️⃣ Tarifas* del menú.`
    ));
}

module.exports = menuServicios;

