# ==========================
# Dockerfile completo WhatsApp Bot PETBIO
# ==========================
FROM node:18-slim

# ==========================
# Instalar dependencias del sistema necesarias para Puppeteer/Chromium
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
# Crear usuario sin privilegios
# ==========================
RUN groupadd -r app && useradd -r -g app -d /usr/src/app -s /sbin/nologin app

# ==========================
# Directorio de trabajo
# ==========================
WORKDIR /usr/src/app

# ==========================
# Copiar package.json y package-lock.json, instalar dependencias
# ==========================
#COPY package.json package-lock.json ./

# ==========================
# Copiar package.json e instalar dependencias
# ==========================
#COPY package*.json ./
#RUN npm ci --only=production \
# && npm install puppeteer@19.11.1 pdfkit --save \
# && npm cache clean --force


# ==========================
# Instalar dependencias
# ==========================
#RUN npm install --production \
# && npm install puppeteer@19.11.1 pdfkit --save \
# && npm cache clean --force

#RUN npm install --omit=dev \
# && npm install puppeteer@19.11.1 pdfkit --save \
# && npm cache clean --force


# ==========================
# Copiar archivos de dependencias
# ==========================
COPY ./whatsapp_bot/package*.json /usr/src/app/

# Verificar que se copiaron correctamente
RUN ls -l /usr/src/app/

# ==========================
# Instalar dependencias en modo producción
# ==========================
RUN npm install --omit=dev \
 && npm install puppeteer@19.11.1 pdfkit --save \
 && npm cache clean --force


# ⚡ Cambio clave: usamos npm install en lugar de npm ci para Render
#RUN npm install --production \
# && npm install puppeteer@19.11.1 pdfkit --save \
# && npm cache clean --force

# ==========================
# Variables de entorno
# ==========================
ENV NODE_ENV=production
ENV PORT=3000
ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium-browser
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true

# ==========================
# Copiar todo el código fuente
# ==========================
COPY . .

# ==========================
# Crear carpetas necesarias y asignar permisos a app
# ==========================
RUN mkdir -p \
    /usr/src/app/WhatsApp_bot_storage/certificados \
    /usr/src/app/WhatsApp_bot_storage/uploads \
    /usr/src/app/sessions \
    /usr/src/app/.wwebjs_auth \
    /usr/src/app/petbio_storage \
    /tmp/puppeteer \
 && chown -R app:app /usr/src/app /tmp/puppeteer

# ==========================
# Cambiar usuario a app
# ==========================
USER app

# ==========================
# Exponer puerto
# ==========================
EXPOSE 3000

# ==========================
# Comando de inicio con Puppeteer headless
# ==========================
CMD ["sh", "-c", "dumb-init xvfb-run --server-args='-screen 0 1280x1024x24' node index.js"]
