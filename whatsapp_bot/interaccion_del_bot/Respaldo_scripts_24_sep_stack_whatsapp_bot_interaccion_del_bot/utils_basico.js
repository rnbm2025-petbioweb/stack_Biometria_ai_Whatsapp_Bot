/**
 * Justifica un texto para WhatsApp (simulación).
 * Cada línea se ajusta a un ancho fijo (por defecto 40 chars).
 *
 * @param {string} text - Texto original
 * @param {number} width - Ancho máximo de cada línea
 * @returns {string} - Texto justificado
 */
function justificarTexto(text, width = 40) {
    const palabras = text.split(/\s+/);
    let lineas = [];
    let linea = [];

    let longitud = 0;
    for (let palabra of palabras) {
        if (longitud + palabra.length + linea.length > width) {
            // Justificar línea completa (excepto si tiene una sola palabra)
            if (linea.length === 1) {
                lineas.push(linea[0]); // no se puede expandir
            } else {
                let espaciosNecesarios = width - longitud;
                let espaciosPorHueco = Math.floor(espaciosNecesarios / (linea.length - 1));
                let extras = espaciosNecesarios % (linea.length - 1);

                let justificada = linea[0];
                for (let i = 1; i < linea.length; i++) {
                    justificada += ' '.repeat(espaciosPorHueco + (i <= extras ? 1 : 0)) + linea[i];
                }
                lineas.push(justificada);
            }

            // Reset línea
            linea = [];
            longitud = 0;
        }
        linea.push(palabra);
        longitud += palabra.length;
    }

    // Agregar última línea sin justificar (izquierda normal)
    if (linea.length > 0) lineas.push(linea.join(' '));

    return lineas.join('\n');
}

module.exports = { justificarTexto };
