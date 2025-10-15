#!/usr/bin/env bash
set -e

echo "🧠 Iniciando PETBIO WhatsApp Bot..."

# Ruta de Chrome descargado por Puppeteer
PUPPETEER_CHROME="/opt/render/.cache/puppeteer/chrome/linux-140.0.7339.207/chrome-linux64/chrome"

# Verifica si existe el Chrome de Puppeteer
if [ -f "$PUPPETEER_CHROME" ]; then
  echo "🔍 Chrome de Puppeteer encontrado en $PUPPETEER_CHROME"
  export PUPPETEER_EXECUTABLE_PATH="$PUPPETEER_CHROME"
elif [ -f "/usr/bin/chromium-browser" ]; then
  echo "🔍 Chromium del sistema encontrado: /usr/bin/chromium-browser"
  export PUPPETEER_EXECUTABLE_PATH="/usr/bin/chromium-browser"
else
  echo "⚠️ Chrome/Chromium no encontrado. Puppeteer podría fallar al iniciar."
fi

# Ejecutar el bot
node index.js
