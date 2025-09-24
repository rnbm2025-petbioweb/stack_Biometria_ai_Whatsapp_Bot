// servicios_menu.js
const fs = require('fs');
const path = require('path');
const utils = require('./utils_bot');

const MENU_SERVICIOS = `
📦 *Servicios PETBIO*

1️⃣ Documento de identidad digital para tu mascota.
2️⃣ Registro biométrico (huella, rostro, QR).
3️⃣ Historia clínica y de viajes.
4️⃣ Servicios de guardería y acompañamiento.
5️⃣ Acceso a productos y servicios con aliados.
6️⃣ Certificación y regulación con PETBIO CA_ROOT.
7️⃣ Servicios digitales y de ciberseguridad.
8️⃣ Desarrollo de software contable y empresarial.
9️⃣ Suscripción para cuidadores

💡 Las tarifas se consultan en la opción 7️⃣ Tarifas del menú.
`;

async function menuServicios(msg, sessionFile) {
    await msg.reply(utils.justificarTexto(MENU_SERVICIOS, 40));

    // Asegurar directorio de sesión
    const sessionDir = path.dirname(sessionFile);
    if (!fs.existsSync(sessionDir)) fs.mkdirSync(sessionDir, { recursive: true });

    const handleOption = async (option) => {
        switch(option) {
            case '9': {
                // Calcular precios con descuento
                const tarifaBaseTrimestral = 25;
                const precio6Meses = (tarifaBaseTrimestral * 2 * 0.97).toFixed(2); // 3% descuento
                const precio12Meses = (tarifaBaseTrimestral * 4 * 0.93).toFixed(2); // 7% descuento

                await msg.reply(utils.justificarTexto(
`💼 *Suscripción para Cuidadores*

- Trimestral: $${tarifaBaseTrimestral}
- 6 meses (3% descuento): $${precio6Meses}
- 12 meses (7% descuento): $${precio12Meses}

🌐 Suscríbete aquí: https://petbio.siac2025.com/suscripciones_cuidadores
`, 40));
                break;
            }
            default:
                // Mensaje informativo para todas las otras opciones
                await msg.reply(utils.justificarTexto("📌 Escribe *menu* para volver al inicio.", 40));
        }
    };

    return handleOption;
}

module.exports = menuServicios;
