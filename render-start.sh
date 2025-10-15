#!/usr/bin/env bash
set -e

echo "🧠 Iniciando PETBIO WhatsApp Bot..."

# ==========================================================
# 🌐 VERIFICAR RUTA DE CHROME (Render Puppeteer Cache)
# ==========================================================
CHROME_CACHE="/opt/render/.cache/puppeteer/chrome/linux-140.0.7339.207/chrome-linux64/chrome"

if [ -f "$CHROME_CACHE" ]; then
  echo "✅ Chromium detectado en la cache: $CHROME_CACHE"
else
  echo "⚠️ Chromium NO encontrado en cache."
  echo "   Ejecutando instalación de Puppeteer Chrome..."
  npx puppeteer browsers install chrome --platform=linux --arch=x64 --force
  if [ -f "$CHROME_CACHE" ]; then
    echo "✅ Chromium instalado correctamente."
  else
    echo "❌ Falló la instalación de Chromium. Revisa permisos o cache de Render."
    exit 1
  fi
fi

# ==========================================================
# 🌐 EXPORTAR RUTA PARA NODE
# ==========================================================
export PUPPETEER_EXECUTABLE_PATH="$CHROME_CACHE"

# ==========================================================
# 📌 EJECUTAR EL BOT
# ==========================================================
node index.js
