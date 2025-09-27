// tarifas_menu.js
const mqtt = require('mqtt');

// ------------------ CONFIGURACIÓN MQTT ------------------
const MQTT_BROKER = process.env.MQTT_BROKER || 'mqtt://localhost:1883';
const client = mqtt.connect(MQTT_BROKER, {
    clientId: 'tarifas_bot_' + Math.random().toString(16).substr(2, 8),
    clean: true
});

// ------------------ MENÚ DE TARIFAS ------------------
function justificarTexto(texto, ancho = 40) {
    const palabras = texto.split(' ');
    const lineas = [];
    let linea = '';

    for (const palabra of palabras) {
        if ((linea + palabra).length > ancho) {
            lineas.push(linea.trim());
            linea = palabra + ' ';
        } else {
            linea += palabra + ' ';
        }
    }
    if (linea) lineas.push(linea.trim());
    return lineas.join('\n');
}

const MENU_TARIFAS = `
💲 *Tarifas PETBIO* 🐾

📌 Trimestral → $25.000 para cuidadores.
📌 Semestral → desde 17.000 a 13.000 por mascota.
📌 Anual → desde 30.000 a 23.000 por mascota.

✅ Escoge el plan según el rango de mascotas (1 a 500).
✅ Precios incluyen trazabilidad, QR y servicios digitales.

🌐 Consulta el simulador financiero:
https://petbio.siac2025.com/finanzas_suscripcion.php

📌 Suscriptores:
- 3 meses → 15% de descuento.
- 6 meses → 25% de descuento.
- 1 año → 35% de descuento.
`;

// ------------------ FUNCIONES ------------------
function calcularDescuento(precioBase, meses) {
    let descuento = 0;
    if (meses === 3) descuento = 0.15;
    else if (meses === 6) descuento = 0.25;
    else if (meses === 12) descuento = 0.35;

    return precioBase - (precioBase * descuento);
}

function enviarMensaje(usuarioId, mensaje) {
    const topic = `petbio/usuario/${usuarioId}`;
    client.publish(topic, justificarTexto(mensaje, 40));
}

function procesarTarifas(usuarioId, payload) {
    if (payload.accion === 'mostrar') {
        enviarMensaje(usuarioId, MENU_TARIFAS);
        enviarMensaje(usuarioId,
            "📌 Escribe *menu* para volver al inicio.\n" +
            "💡 Para suscribirte y aplicar descuento, responde con *suscripcion* seguido del período: 3, 6 o 12 meses."
        );
    } else if (payload.accion === 'calcular') {
        const { precioBase, meses } = payload;
        const precioConDescuento = calcularDescuento(precioBase, meses);
        enviarMensaje(usuarioId,
            `💳 El precio con descuento para ${meses} meses es: $${precioConDescuento.toLocaleString()}`
        );
    }
}

// ------------------ SUSCRIPCIÓN MQTT ------------------
client.on('connect', () => {
    console.log('✅ Bot de tarifas conectado al broker MQTT');
    client.subscribe('petbio/bot/tarifas', { qos: 1 });
});

client.on('message', (topic, message) => {
    if (topic === 'petbio/bot/tarifas') {
        try {
            const payload = JSON.parse(message.toString());
            if (!payload.usuarioId) return;
            procesarTarifas(payload.usuarioId, payload);
        } catch (err) {
            console.error('❌ Error al procesar mensaje de tarifas:', err.message);
        }
    }
});

// ------------------ EXPORTS ------------------
module.exports = { calcularDescuento };
