import puppeteer from "puppeteer";
try {
  console.log("🔧 Verificando instalación de Chrome...");
  await puppeteer.launch({ headless: true });
  console.log("✅ Chrome disponible para Puppeteer");
} catch (err) {
  console.error("⚠️ Chrome no disponible:", err.message);
}
