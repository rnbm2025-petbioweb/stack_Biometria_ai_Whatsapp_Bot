// twilio_sandbox_dev.js
require('dotenv').config();
const express = require('express');
const bodyParser = require('body-parser');
const { MessagingResponse } = require('twilio').twiml;
const fs = require('fs');
const path = require('path');

// Importa tus módulos de bot existentes
const saludoDelUsuario = require('./interaccion_del_bot/saludo_del_usuario');
const menuInicioModule = require('./interaccion_del_bot/menu_inicio');
const { iniciarRegistroMascota } = require('./interaccion_del_bot/registro_mascotas_bot');
const { iniciarRegistroUsuario } = require('./interaccion_del_bot/registro_usuario_bot');

// Configuración
const app = express();
app.use(bodyParser.urlencoded({ extended: false }));

const sessionsDir = path.join(__dirname, 'sessions');
if (!fs.existsSync(sessionsDir)) fs.mkdirSync(sessionsDir, { recursive: true });

const CMD_MENU = ['menu', 'inicio', 'volver', 'home'];
const CMD_CANCEL = ['cancelar', 'salir', 'stop', 'terminar', 'abortar'];
const SESSION_TTL = 1000 * 60 * 60 * 12; // 12h

// Endpoint que Twilio llamará cuando llegue un mensaje al Sandbox
app.post('/whatsapp', async (req, res) => {
    try {
        const incomingMsg = (req.body.Body || '').trim();
        const from = req.body.From; // Ej: "whatsapp:+57300XXXXXXX"
        const twiml = new MessagingResponse();

        // Manejo de sesiones
        const sessionFile = path.join(sessionsDir, `${from}.json`);
        let session = {};
        if (fs.existsSync(sessionFile)) {
            session = JSON.parse(fs.readFileSync(sessionFile));
            if (Date.now() - (session.lastActive || 0) > SESSION_TTL) session = {};
        }
        session.type = session.type || 'menu_inicio';
        session.step = session.step || null;
        session.data = session.data || {};
        session.lastActive = Date.now();
        session.lastGreeted = session.lastGreeted || false;

        const lcMsg = incomingMsg.toLowerCase();

        // ---------- CANCELAR ----------
        if (CMD_CANCEL.includes(lcMsg)) {
            try { fs.unlinkSync(sessionFile); } catch (e) {}
            twiml.message('🛑 Registro cancelado. Escribe *menu* para volver al inicio.');
            res.writeHead(200, { 'Content-Type': 'text/xml' });
            return res.end(twiml.toString());
        }

        // ---------- MENU ----------
        if (CMD_MENU.includes(lcMsg)) {
            session.type = 'menu_inicio';
            session.step = null;
            session.data = {};
            session.lastActive = Date.now();
            session.lastGreeted = false;

            // Ejecuta la función de saludo de tu bot
            await saludoDelUsuario({ reply: (msg) => twiml.message(msg), body: incomingMsg, from }, sessionFile);
            fs.writeFileSync(sessionFile, JSON.stringify(session));

            res.writeHead(200, { 'Content-Type': 'text/xml' });
            return res.end(twiml.toString());
        }

        // ---------- Router principal ----------
        switch (session.type) {
            case 'menu_inicio':
                const handleMenu = await menuInicioModule({ reply: (msg) => twiml.message(msg), body: incomingMsg, from }, sessionFile, session);
                await handleMenu(incomingMsg);
                break;
            case 'registro_usuario':
                await iniciarRegistroUsuario({ reply: (msg) => twiml.message(msg), body: incomingMsg, from }, session, sessionFile);
                break;
            case 'registro_mascota':
                await iniciarRegistroMascota({ reply: (msg) => twiml.message(msg), body: incomingMsg, from }, session, sessionFile, null);
                break;
            default:
                twiml.message('🤖 No entendí. Escribe *menu* para volver al inicio o *cancelar* para salir.');
                break;
        }

        fs.writeFileSync(sessionFile, JSON.stringify(session));
        res.writeHead(200, { 'Content-Type': 'text/xml' });
        res.end(twiml.toString());

    } catch (err) {
        console.error('❌ Error procesando mensaje:', err);
        const twiml = new MessagingResponse();
        twiml.message('⚠️ Error interno. Escribe *menu* para reiniciar.');
        res.writeHead(200, { 'Content-Type': 'text/xml' });
        res.end(twiml.toString());
    }
});

// Healthcheck opcional
app.get('/health', (req, res) => res.send('✅ PETBIO Bot Dev activo'));

// Levantar servidor
const PORT = process.env.PORT || 5000;
app.listen(PORT, () => console.log(`🌐 Servidor Twilio Sandbox dev corriendo en puerto ${PORT}`));
