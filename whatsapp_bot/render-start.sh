#!/usr/bin/env bash
set -e

echo "🧩 Verificando instalación de Chrome..."
if [ ! -f "/usr/bin/google-chrome" ]; then
  echo "⬇️ Instalando dependencias de Chrome..."
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
    unzip
  echo "✅ Chromium instalado."
fi

echo "⬇️ Instalando Puppeteer Chrome (runtime)..."
npx puppeteer browsers install chrome --platform=linux --arch=x64 --force

echo "🚀 Iniciando PETBIO WhatsApp Bot..."
node index.js
