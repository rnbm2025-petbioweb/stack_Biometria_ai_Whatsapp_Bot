function validarPasoActual(pasoActual) {
  const pasosRequeridos = {
    mockup0: ['nombre', 'apellidos', 'clase_mascota', 'img_perfil'],
    mockup1: ['raza', 'edad', 'ciudad', 'relacion', 'img_latdr'],
    mockup2: ['barrio', 'codigo_postal', 'condicion_mascota', 'img_latiz'],
    mockup3: ['foto15'], // lateral 15° derecha
    mockup4: ['foto30'], // lateral 30° derecha
    mockup5: ['foto15_izq'], // lateral 15° izquierda
    mockup6: ['foto30_izq'], // lateral 30° izquierda
    mockup7: ['img_hf0'] // frontal 0°
  };

  const previos = {
    mockup0: null,
    mockup1: 'mockup0.php',
    mockup2: 'mockup1.php',
    mockup3: 'mockup2.php',
    mockup4: 'mockup3.php',
    mockup5: 'mockup4.php',
    mockup6: 'mockup5.php',
    mockup7: 'mockup6.php',
    mockup9: 'mockup7.php'
  };

  const requeridos = pasosRequeridos[pasoActual] || [];
  const anterior = previos[pasoActual];

  const incompletos = requeridos.filter(campo => {
    const valor = localStorage.getItem(campo);
    return !valor || valor.trim() === '';
  });

  if (incompletos.length > 0 && anterior) {
    console.warn(`⚠️ Faltan campos requeridos para ${pasoActual}: ${incompletos.join(', ')}`);
    alert("⚠️ Datos incompletos. Serás redirigido al paso anterior.");
    window.location.href = anterior;
  }
}
