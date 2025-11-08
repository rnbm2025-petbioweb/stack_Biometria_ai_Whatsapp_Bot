// fix-puppeteer.js
const fs = require("fs");
const path = require("path");

const cachePath = "/opt/render/.cache/puppeteer/chrome";
const localChrome = "/usr/bin/google-chrome";

try {
  if (!fs.existsSync(cachePath)) {
    fs.mkdirSync(cachePath, { recursive: true });
  }

  // Crear un enlace simbólico si existe Chrome en el sistema
  if (fs.existsSync(localChrome)) {
    const targetPath = path.join(cachePath, "chrome-linux64", "chrome");
    fs.mkdirSync(path.dirname(targetPath), { recursive: true });
    if (!fs.existsSync(targetPath)) {
      fs.symlinkSync(localChrome, targetPath);
      console.log("✅ Chrome vinculado correctamente para Puppeteer");
    }
  } else {
    console.log("⚠️ Chrome no encontrado localmente, se usará el de Puppeteer");
  }
} catch (err) {
  console.error("❌ Error configurando Puppeteer:", err);
}
