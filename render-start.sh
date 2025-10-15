#!/usr/bin/env bash
set -e

echo "🧠 Iniciando PETBIO WhatsApp Bot..."

# Verifica ruta de Chromium
if [ -f "/usr/bin/chromium-browser" ]; then
  echo "🔍 Chromium del sistema encontrado: /usr/bin/chromium-browser"
else
  echo "⚠️ Chromium no encontrado en /usr/bin/chromium-browser. Puppeteer usará su cache."
fi

# Ejecutar el bot
node index.js
