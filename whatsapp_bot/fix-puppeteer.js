// fix-puppeteer.js – Configuración moderna para Puppeteer en Render
import fs from "fs";
import path from "path";
import { execSync } from "child_process";

const envFile = path.join(process.cwd(), ".env");

(async () => {
  try {
    console.log("🧩 Configurando Puppeteer en Render...");

    // Verificamos ruta actual de Chrome
    const chromePath =
      process.env.PUPPETEER_EXECUTABLE_PATH ||
      "/opt/render/.cache/puppeteer/chrome/linux-120.0.6099.109/chrome-linux64/chrome";

    // Si no existe Chrome, lo instala usando el nuevo CLI de Puppeteer
    if (!fs.existsSync(chromePath)) {
      console.log("🚀 Instalando Chrome con Puppeteer...");
      execSync("npx puppeteer browsers install chrome --force", { stdio: "inherit" });
    }

    // Agregamos o actualizamos la variable de entorno en .env
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
