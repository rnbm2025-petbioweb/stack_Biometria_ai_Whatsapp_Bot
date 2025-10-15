#!/usr/bin/env bash
set -e

echo "🧠 Iniciando PETBIO WhatsApp Bot..."

# ==========================================================
# 🌐 DETECTAR CHROMIUM AUTOMÁTICAMENTE
# ==========================================================
PUPPETEER_EXECUTABLE_PATH=$(node -e "console.log(require('puppeteer').executablePath())")

if [ -f "$PUPPETEER_EXECUTABLE_PATH" ]; then
  echo "✅ Chromium detectado automáticamente en: $PUPPETEER_EXECUTABLE_PATH"
else
  echo "⚠️ Chromium no encontrado, ejecutando instalación..."
  npx puppeteer browsers install chrome --platform=linux --arch=x64 --force

  # Re-detectar ruta después de instalación
  PUPPETEER_EXECUTABLE_PATH=$(node -e "console.log(require('puppeteer').executablePath())")
  if [ -f "$PUPPETEER_EXECUTABLE_PATH" ]; then
    echo "✅ Chromium instalado correctamente en: $PUPPETEER_EXECUTABLE_PATH"
  else
    echo "❌ Falló la instalación de Chromium. Revisa permisos o cache de Render."
    exit 1
  fi
fi

# ==========================================================
# 🌐 EXPORTAR RUTA DE CHROMIUM PARA NODE
# ==========================================================
export PUPPETEER_EXECUTABLE_PATH
echo "🔧 Variable PUPPETEER_EXECUTABLE_PATH exportada."

# ==========================================================
# 📌 ARRANCAR EL BOT
# ==========================================================
echo "🚀 Arrancando PETBIO WhatsApp Bot..."
node index.js
