#!/usr/bin/env bash
set -e

echo "🚀 Actualizando e instalando dependencias del sistema para Chromium..."
apt-get update -y
apt-get install -y chromium \
  fonts-liberation \
  libappindicator3-1 \
  libasound2 \
  libnss3 \
  libxss1 \
  libxshmfence1 \
  libatk-bridge2.0-0 \
  libgbm1 \
  libxdamage1 \
  xdg-utils \
  wget \
  unzip \
  ca-certificates \
  gnupg \
  curl

echo "✅ Dependencias del sistema instaladas correctamente."

echo "⬇️ Instalando Puppeteer Chrome (runtime)..."
npx puppeteer browsers install chrome --platform=linux --arch=x64 --force
echo "✅ Chromium y Puppeteer listos para usar."

# ===========================
# 🔹 Arranque del bot
# ===========================
echo "🧠 Iniciando PETBIO WhatsApp Bot..."

# Ruta del ejecutable de Chrome instalado por Puppeteer
CHROME_PATH=$(npx puppeteer info --path)

# Exporta la ruta para tu index.js si usas puppeteer-core
export PUPPETEER_EXECUTABLE_PATH="$CHROME_PATH"

# Inicia el bot
node index.js
