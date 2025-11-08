// fix-puppeteer.js – fuerza Puppeteer a usar la ruta correcta de Chrome
const fs = require('fs');
const path = require('path');
const puppeteer = require('puppeteer');

(async () => {
  try {
    console.log("🧩 Instalando Chrome para Puppeteer...");
    const browserFetcher = puppeteer.createBrowserFetcher();
    const revisionInfo = await browserFetcher.download('1045629'); // revisión compatible con whatsapp-web.js
    const chromePath = revisionInfo.executablePath;
    const envFile = path.join(__dirname, '.env');
    let envContent = fs.existsSync(envFile) ? fs.readFileSync(envFile, 'utf8') : '';
    if (!envContent.includes('PUPPETEER_EXECUTABLE_PATH=')) {
      envContent += `\nPUPPETEER_EXECUTABLE_PATH=${chromePath}\n`;
      fs.writeFileSync(envFile, envContent);
    }
    console.log(`✅ Chrome descargado en: ${chromePath}`);
  } catch (err) {
    console.error("❌ Error instalando Puppeteer:", err.message);
    process.exit(1);
  }
})();
