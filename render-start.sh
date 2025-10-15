#!/usr/bin/env bash
set -e

echo "🧠 Iniciando PETBIO WhatsApp Bot..."

# Mostrar ruta de Chromium (opcional, para debugging)
echo "🔍 Verificando Chromium instalado:"
ls -l /opt/render/.cache/puppeteer/chrome/linux-*/chrome-linux64/chrome || echo "⚠️ Chromium no encontrado, Puppeteer lo instalará automáticamente."

# Ejecutar el bot
node index.js
