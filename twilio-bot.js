// twilio-bot.js - Bot PETBIO con Twilio
const express = require('express');
const bodyParser = require('body-parser');
const fetch = require('node-fetch');
const twilio = require('twilio');

const app = express();
app.use(bodyParser.urlencoded({ extended: false }));
app.use(bodyParser.json());

// --- Configuración de Twilio ---
const accountSid = 'ACe15576d5f4212f1aa78999a3f53820d1';
const authToken = 'f462e76653a85ac6172d441a7bd67332'; // Auth Token
const twilioNumber = 'whatsapp:+14155238886';          // Sandbox Twilio
const adminNumber = 'whatsapp:+573007019277';          // Tu WhatsApp personal

const client = twilio(accountSid, authToken);

// --- Función para enviar mensaje ---
async function sendMessage(to, body) {
    try {
        const message = await client.messages.create({
            from: twilioNumber,
            to: to.startsWith('whatsapp:') ? to : `whatsapp:${to}`,
            body
        });
        console.log('Mensaje enviado:', message.sid);
        return message;
    } catch (err) {
        console.error('Error enviando mensaje:', err);
        throw err;
    }
}

// --- Endpoint para enviar mensajes desde CRM o pruebas ---
app.post('/send-message', async (req, res) => {
    const { number, message } = req.body;
    if (!number || !message) return res.status(400).json({ status: 'error', error: 'Faltan parámetros' });

    try {
        await sendMessage(number, message);
        res.json({ status: 'success', number, message });
    } catch (err) {
        res.status(500).json({ status: 'error', error: err.message });
    }
});

// --- Endpoint de prueba para el Sandbox ---
app.post('/incoming', async (req, res) => {
    const from = req.body.From;
    const body = req.body.Body;
    console.log(`Mensaje entrante de ${from}: ${body}`);

    // Responder automáticamente
    let respuesta = `¡Hola! Este es un mensaje de prueba desde el bot PETBIO.`;

    // Notificar fuera de horario si aplica
    if (!dentroHorarioLaboral()) {
        await sendMessage(adminNumber, `⚠️ Mensaje fuera de horario de ${from}: ${body}`);
    }

    try {
        await sendMessage(from, respuesta);
        console.log('Respuesta enviada a', from);
    } catch (err) {
        console.error('Error enviando respuesta:', err);
    }

    res.sendStatus(200);
});

// --- Función de horario laboral ---
function dentroHorarioLaboral() {
    const ahora = new Date();
    const hora = ahora.getHours();
    const dia = ahora.getDay();
    return (dia >= 1 && dia <= 5) && (hora >= 8 && hora < 18);
}

// --- Servidor HTTP ---
app.listen(3000, () => console.log('Bot HTTP server running on port 3000'));
