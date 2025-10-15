/**
 * menu_luego_de_registro_de_usuario.js
 *
 * Menú inicial de PETBIO después del registro.
 * Publica al topic correspondiente para que otros bots atiendan vía MQTT.
 */

const mqtt = require('mqtt');

// ------------------ CONFIGURACIÓN MQTT ------------------
const MQTT_BROKER = process.env.MQTT_BROKER || 'mqtt://localhost:1883';
const MQTT_OPTIONS = {
    clientId: 'menu_usuario_' + Math.random().toString(16).substr(2, 8),
    clean: true,
    connectTimeout: 4000
};
const client = mqtt.connect(MQTT_BROKER, MQTT_OPTIONS);

client.on('connect', () => {
    console.log('✅ Menú conectado a MQTT');
});

// ------------------ CALLBACKS POR USUARIO ------------------
const callbacksPorUsuario = new Map();

client.on('message', (topic, message) => {
    if (callbacksPorUsuario.has(topic)) {
        const callback = callbacksPorUsuario.get(topic);
        callback(message.toString().trim());
    }
});

// ------------------ FUNCIONES ------------------
function enviarMensaje(usuarioId, mensaje) {
    const topic = `petbio/usuario/${usuarioId}`;
    client.publish(topic, mensaje, { qos: 1 }, (err) => {
        if (err) console.error('❌ Error enviando MQTT:', err);
    });
    console.log(`📩 Enviado a ${topic}: ${mensaje}`);
}

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

function mostrarMenu(usuarioId) {
    const menu = `
🐾 *Bienvenido a PETBIO* 🐾

Selecciona una opción:
1️⃣ Historia Clínica 📖
2️⃣ Crear Cita 📅
3️⃣ Suscripciones y Tarifas 💳
4️⃣ Volver a este menú 🔄
`;

    enviarMensaje(usuarioId, justificarTexto(menu, 45));
}

function procesarOpcion(usuarioId, opcion) {
    switch (opcion) {
        case '1':
            client.publish(`petbio/bot/historia_clinica`, JSON.stringify({ usuarioId }));
            break;
        case '2':
            client.publish(`petbio/bot/crear_cita`, JSON.stringify({ usuarioId }));
            break;
        case '3':
            client.publish(`petbio/bot/tarifas`, JSON.stringify({ usuarioId, accion: 'mostrar' }));
            break;
        case '4':
            mostrarMenu(usuarioId);
            break;
        default:
            enviarMensaje(usuarioId, '❌ Opción no válida. Selecciona 1,2,3 o 4.');
            mostrarMenu(usuarioId);
            break;
    }
}

function iniciarMenu(usuarioId) {
    const topicEntrada = `petbio/entrada/${usuarioId}`;

    client.subscribe(topicEntrada, (err) => {
        if (!err) {
            console.log(`✅ Escuchando opciones en ${topicEntrada}`);
        }
    });

    callbacksPorUsuario.set(topicEntrada, (opcion) => {
        procesarOpcion(usuarioId, opcion);
    });

    mostrarMenu(usuarioId);
}

// ------------------ EXPORTS ------------------
module.exports = { iniciarMenu };
