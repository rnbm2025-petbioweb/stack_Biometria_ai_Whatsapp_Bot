const fs = require('fs');
const path = require('path');

const UPLOAD_DIR = path.join(__dirname, 'uploads');

// Lista de tipos de imagenes
const tiposImagen = [
    'hf0', 'hf15', 'hf30', 'hfld15', 'hfli15',
    'perfil', 'latdr', 'latiz'
];

// Ejemplo: IDs de usuarios y clases de mascotas que quieres crear
const usuarios = ['1', '2', '3'];          // reemplaza con ids reales
const clasesMascota = ['Perro', 'Gato'];  // clases de mascotas

usuarios.forEach(id_usuario => {
    clasesMascota.forEach(clase => {
        const basePath = path.join(UPLOAD_DIR, id_usuario, clase);
        if (!fs.existsSync(basePath)) fs.mkdirSync(basePath, { recursive: true });

        tiposImagen.forEach(tipo => {
            const dirPath = path.join(basePath, tipo);
            if (!fs.existsSync(dirPath)) fs.mkdirSync(dirPath, { recursive: true });
        });
    });
});

console.log('✅ Directorios creados correctamente en "uploads"');
