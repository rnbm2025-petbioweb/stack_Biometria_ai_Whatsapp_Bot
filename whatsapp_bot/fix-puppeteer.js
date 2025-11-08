// fix-puppeteer.js – Configuración moderna para Puppeteer en Render
import fs from "fs";
import path from "path";
import puppeteer from "puppeteer";

const envFile = path.join(process.cwd(), ".env");

(async () => {
  try {
    console.log("🧩 Configurando Puppeteer en Render...");

    // Forzamos a Puppeteer a instalar su propio Chrome si no existe
    const chromePath = process.env.PUPPETEER_EXECUTABLE_PATH || "/usr/bin/chromium-browser";
    if (!fs.existsSync(chromePath)) {
      console.log("🚀 Instalando Chrome con Puppeteer CLI...");
      const { execSync } = await import("child_process");
      execSync("npx puppeteer browsers install chrome --force", { stdio: "inherit" });
    }

    // Añadir o actualizar la variable en .env
    let envContent = fs.existsSync(envFile) ? fs.readFileSync(envFile, "utf8") : "";
    if (!envContent.includes("PUPPETEER_EXECUTABLE_PATH=")) {
      envContent += `\nPUPPETEER_EXECUTABLE_PATH=${chromePath}\n`;
      fs.writeFileSync(envFile, envContent);
    }

    console.log(`✅ Puppeteer configurado con Chrome en: ${chromePath}`);
  } catch (err) {
    console.error("❌ Error configurando Puppeteer:", err.message);
    process.exit(1);
  }
})();
