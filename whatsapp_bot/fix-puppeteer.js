// fix-puppeteer.js – Auto-fix Puppeteer en Render (versión segura)
import fs from "fs";
import path from "path";
import { execSync } from "child_process";

const envFile = path.join(process.cwd(), ".env");

(async () => {
  try {
    console.log("🧩 Configurando Puppeteer en Render...");

    // 1️⃣ Instalar Chrome si no existe
    console.log("🚀 Verificando instalación de Chrome...");
    execSync("npx puppeteer browsers install chrome --force", { stdio: "inherit" });

    // 2️⃣ Obtener ruta real de Chrome instalada
    const chromePath = execSync("npx puppeteer browsers path chrome").toString().trim();

    console.log(`✅ Chrome instalado en: ${chromePath}`);

    // 3️⃣ Actualizar o crear variable en .env
    let envContent = fs.existsSync(envFile) ? fs.readFileSync(envFile, "utf8") : "";
    if (!envContent.includes("PUPPETEER_EXECUTABLE_PATH=")) {
      envContent += `\nPUPPETEER_EXECUTABLE_PATH=${chromePath}\n`;
    } else {
      envContent = envContent.replace(/PUPPETEER_EXECUTABLE_PATH=.*/g, `PUPPETEER_EXECUTABLE_PATH=${chromePath}`);
    }
    fs.writeFileSync(envFile, envContent);
    console.log(`✅ Variable PUPPETEER_EXECUTABLE_PATH actualizada en .env`);

    console.log("🎉 Puppeteer configurado correctamente para Render");
  } catch (err) {
    console.error("❌ Error configurando Puppeteer:", err.message);
    process.exit(1);
  }
})();
