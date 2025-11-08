// fix-puppeteer.js – Fuerza Puppeteer a usar la ruta correcta de Chrome
const fs = require('fs');
const path = require('path');
const puppeteer = require('puppeteer');

(async () => {
  try {
    console.log("🧩 Verificando Chrome para Puppeteer...");

    const cacheDir = '/opt/render/.cache/puppeteer';
    const revision = '1045629'; // revisión estable compatible con whatsapp-web.js

    // Crear directorio cache si no existe
    if (!fs.existsSync(cacheDir)) {
      fs.mkdirSync(cacheDir, { recursive: true });
      console.log("📁 Carpeta de caché Puppeteer creada.");
    }

    // Descargar versión específica de Chrome
    const browserFetcher = puppeteer.createBrowserFetcher({ path: cacheDir });
    const revisionInfo = await browserFetcher.download(revision);
    const chromePath = revisionInfo.executablePath;

    console.log(`✅ Chrome descargado o existente en: ${chromePath}`);

    // Escribir la ruta en un archivo auxiliar (no en .env)
    const runtimePath = path.join(__dirname, 'chrome-path.txt');
    fs.writeFileSync(runtimePath, chromePath);

    console.log(`💾 Ruta guardada en ${runtimePath}`);
    console.log('⚙️ Si usas Render, define en tu panel la variable:');
    console.log(`PUPPETEER_EXECUTABLE_PATH=${chromePath}`);

  } catch (err) {
    console.error("❌ Error instalando Puppeteer:", err.message);
    process.exit(1);
  }
})();
