// fix-puppeteer.js – Configuración moderna para Puppeteer en Render
import fs from "fs";
import path from "path";
import puppeteer from "puppeteer";

const envFile = path.join(process.cwd(), ".env");

(async () => {
  try {
    console.log("🧩 Configurando Puppeteer en Render...");

    const defaultChromePath = "/opt/render/.cache/puppeteer/chrome/linux-142.0.7444.61/chrome-linux64/chrome";
    let chromePath = process.env.PUPPETEER_EXECUTABLE_PATH || defaultChromePath;

    if (!fs.existsSync(chromePath)) {
      console.log("🚀 Verificando instalación de Chrome (sin npx)...");
      // Lanzamos Puppeteer una vez para forzar instalación automática
      const browser = await puppeteer.launch({ headless: true });
      await browser.close();
      console.log("✅ Chrome detectado y operativo.");
    }

    // Guardar la ruta en .env si no existe
    let envContent = fs.existsSync(envFile) ? fs.readFileSync(envFile, "utf8") : "";
    if (!envContent.includes("PUPPETEER_EXECUTABLE_PATH=")) {
      envContent += `\nPUPPETEER_EXECUTABLE_PATH=${chromePath}\n`;
      fs.writeFileSync(envFile, envContent);
      console.log(`✅ Ruta de Chrome agregada a .env: ${chromePath}`);
    } else {
      console.log("ℹ️ Variable PUPPETEER_EXECUTABLE_PATH ya definida en .env");
    }

    console.log("✅ Puppeteer configurado correctamente en Render");
  } catch (err) {
    console.error("❌ Error configurando Puppeteer:", err.message);
    process.exit(1);
  }
})();
