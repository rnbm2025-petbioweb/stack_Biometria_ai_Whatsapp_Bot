#!/usr/bin/env bash
set -e

echo "🧠 Iniciando PETBIO WhatsApp Bot..."

# ==========================================================
# 🌐 DETECCIÓN AUTOMÁTICA DE CHROMIUM
# ==========================================================
PUPPETEER_EXECUTABLE_PATH=$(node -e "console.log(require('puppeteer').executablePath())")

if [ -f "$PUPPETEER_EXECUTABLE_PATH" ]; then
  echo "✅ Chromium detectado automáticamente en: $PUPPETEER_EXECUTABLE_PATH"
else
  echo "⚠️ Chromium no encontrado, ejecutando instalación..."
  npx puppeteer install chrome --platform=linux --arch=x64 --force
  PUPPETEER_EXECUTABLE_PATH=$(node -e "console.log(require('puppeteer').executablePath())")
  if [ -f "$PUPPETEER_EXECUTABLE_PATH" ]; then
    echo "✅ Chromium instalado correctamente en: $PUPPETEER_EXECUTABLE_PATH"
  else
    echo "❌ Falló la instalación de Chromium."
    exit 1
  fi
fi

export PUPPETEER_EXECUTABLE_PATH

# ==========================================================
# 📌 ARRANCAR EL BOT
# ==========================================================
echo "🚀 Ejecutando index.js..."
node index.js
