// tarifas_menu.js (versión simplificada)
function justificarTexto(texto, ancho = 40) {
  const palabras = texto.split(' ');
  const lineas = [];
  let linea = '';

  for (const palabra of palabras) {
    if ((linea + palabra).length > ancho) {
      lineas.push(linea.trim());
      linea = palabra + ' ';
    } else {
      linea += palabra + ' ';
    }
  }
  if (linea) lineas.push(linea.trim());
  return lineas.join('\n');
}

const MENU_TARIFAS = justificarTexto(`
💲 *Tarifas PETBIO* 🐾

📌 Trimestral → $25.000 para cuidadores.
📌 Semestral → desde 17.000 a 13.000 por mascota.
📌 Anual → desde 30.000 a 23.000 por mascota.

✅ Escoge el plan según el rango de mascotas (1 a 500).
✅ Precios incluyen trazabilidad, QR y servicios digitales.

🌐 Consulta el simulador financiero:
https://petbio.siac2025.com/finanzas_suscripcion.php

📌 Suscriptores:
- 3 meses → 15% de descuento.
- 6 meses → 25% de descuento.
- 1 año → 35% de descuento.
`);

function calcularDescuento(precioBase, meses) {
  let descuento = 0;
  if (meses === 3) descuento = 0.15;
  else if (meses === 6) descuento = 0.25;
  else if (meses === 12) descuento = 0.35;

  return precioBase - (precioBase * descuento);
}

function mostrarMenuTarifas() {
  return MENU_TARIFAS + "\n\n💡 Responde con el número de meses (3, 6 o 12) para calcular tu precio con descuento.";
}

function procesarSuscripcion(meses, precioBase = 25000) {
  const precioFinal = calcularDescuento(precioBase, meses);
  return `💳 El precio con descuento para ${meses} meses es: $${precioFinal.toLocaleString()}\n\n✅ ¿Quieres confirmar tu suscripción? Responde *confirmar ${meses}*.`;
}

module.exports = { mostrarMenuTarifas, procesarSuscripcion };
