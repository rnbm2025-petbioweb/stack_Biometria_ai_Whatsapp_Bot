/**
 * menu_luego_de_registro_de_usuario.js
 *
 * Muestra el menú inicial al usuario después del registro y
 * publica los topics correspondientes para que los bots respondan vía MQTT.
 */

const mqtt = require('mqtt');

// Configuración de conexión a Mosquitto
const MQTT_BROKER = 'mqtt://192.168.1.20:1883';
const MQTT_OPTIONS = {
    clientId: 'menu_usuario_' + Math.random().toString(16).substr(2, 8),
    clean: true,
    connectTimeout: 4000
};

// Conexión al broker MQTT
const client = mqtt.connect(MQTT_BROKER, MQTT_OPTIONS);

client.on('connect', () => {
    console.log('✅ Conectado a Mosquitto');
});

// Mapa para callbacks por usuario
const callbacksPorUsuario = new Map();

// Escuchar mensajes globalmente y delegar a cada usuario
client.on('message', (topic, message) => {
    if (callbacksPorUsuario.has(topic)) {
        const callback = callbacksPorUsuario.get(topic);
        callback(message.toString().trim());
    }
});

/**
 * Enviar mensaje al usuario vía MQTT
 * @param {string|number} usuarioId
 * @param {string} mensaje
 */
function enviarMensaje(usuarioId, mensaje) {
    const topic = `petbio/usuario/${usuarioId}`;
    client.publish(topic, mensaje, { qos: 1 }, (err) => {
        if (err) console.error('Error enviando MQTT:', err);
    });
    console.log(`Mensaje enviado a ${topic}: ${mensaje}`);
}

/**
 * Mostrar menú principal al usuario
 * @param {string|number} usuarioId
 */
function mostrarMenu(usuarioId) {
    const menu = `
🐾 *Bienvenido a PETBIO* 🐾

Selecciona una opción:
1️⃣ Consultar Historia Clínica
2️⃣ Crear Cita
3️⃣ Ver Suscripciones
4️⃣ Volver al inicio
`;

    enviarMensaje(usuarioId, menu);
}

/**
 * Procesar la opción elegida por el usuario
 * @param {string|number} usuarioId
 * @param {string} opcion
 */
function procesarOpcion(usuarioId, opcion) {
    switch (opcion) {
        case '1':
            client.publish(`petbio/bot/historia_clinica`, JSON.stringify({ usuarioId }));
            break;
        case '2':
            client.publish(`petbio/bot/crear_cita`, JSON.stringify({ usuarioId }));
            break;
        case '3':
            client.publish(`petbio/bot/suscripciones_cuidadores`, JSON.stringify({ usuarioId }));
            break;
        case '4':
            mostrarMenu(usuarioId);
            break;
        default:
            enviarMensaje(usuarioId, '❌ Opción no válida. Por favor selecciona 1,2,3 o 4.');
            mostrarMenu(usuarioId);
            break;
    }
}

/**
 * Inicia la escucha del menú vía MQTT para el usuario
 * @param {string|number} usuarioId
 */
function iniciarMenu(usuarioId) {
    const topicEntrada = `petbio/entrada/${usuarioId}`;

    // Suscribirse al topic donde el usuario envía su elección
    client.subscribe(topicEntrada, (err) => {
        if (!err) {
            console.log(`✅ Escuchando opciones del usuario en ${topicEntrada}`);
        }
    });

    // Registrar callback específico para este usuario
    callbacksPorUsuario.set(topicEntrada, (opcion) => {
        procesarOpcion(usuarioId, opcion);
    });

    // Mostrar el menú al iniciar
    mostrarMenu(usuarioId);
}

// Exportar función principal
module.exports = { iniciarMenu };
