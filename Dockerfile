# ==========================
# WhatsApp Bot PETBIO - Dockerfile final para Render con sesión persistente
# ==========================
FROM node:20-slim

# ==========================
# Instalar dependencias necesarias para Puppeteer y Chromium
# ==========================
RUN apt-get update && apt-get install -y --no-install-recommends \
  ca-certificates \
  fonts-liberation \
  libnss3 \
  libatk1.0-0 \
  libcups2 \
  libdrm2 \
  libxcomposite1 \
  libxdamage1 \
  libxrandr2 \
  libgbm1 \
  libasound2 \
  libpangocairo-1.0-0 \
  libgtk-3-0 \
  libpango-1.0-0 \
  libxshmfence1 \
  libx11-xcb1 \
  libx11-6 \
  libxss1 \
  wget \
  chromium \
  xvfb \
  dumb-init \
  xauth \
  dbus-x11 \
  gconf-service \
  libexpat1 \
  libfontconfig1 \
  libgcc1 \
  libgconf-2-4 \
  libgdk-pixbuf2.0-0 \
  libglib2.0-0 \
  libnspr4 \
  libstdc++6 \
  libxtst6 \
  xdg-utils \
  default-mysql-client \
 && ln -s /usr/bin/chromium /usr/bin/chromium-browser \
 && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# ==========================
# Crear directorio de trabajo
# ==========================
WORKDIR /usr/src/app

# ==========================
# Copiar archivos de dependencias e instalarlas
# ==========================
COPY package*.json ./
RUN npm install --omit=dev && \
    npm install puppeteer@19.11.1 pdfkit mqtt-cli --save && \
    npm cache clean --force

# ==========================
# Copiar todo el código fuente
# ==========================
COPY . .

# ==========================
# Crear carpetas necesarias, incluyendo sesión persistente
# ==========================
RUN mkdir -p \
  /usr/src/app/WhatsApp_bot_storage/certificados \
  /usr/src/app/WhatsApp_bot_storage/uploads \
  /usr/src/app/sessions \
  /usr/src/app/petbio_storage \
  /tmp/session \
  /tmp/puppeteer

# ==========================
# Variables de entorno
# ==========================
ENV NODE_ENV=production
ENV PORT=3000
ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium-browser
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true
ENV WWEBJS_SESSION_DIR=/tmp/session

# ==========================
# Entrypoint simplificado (no borrar sesiones)
# ==========================
RUN echo '#!/bin/sh\n\
echo "🚀 Iniciando WhatsApp bot con sesión persistente..."\n\
exec dumb-init xvfb-run --server-args="-screen 0 1280x1024x24" node whatsapp_bot/index.js\n\
' > /usr/src/app/entrypoint.sh && chmod +x /usr/src/app/entrypoint.sh

# ==========================
# Exponer el puerto del bot
# ==========================
EXPOSE 3000

# ==========================
# Comando de inicio
# ==========================
CMD ["/usr/src/app/entrypoint.sh"]
