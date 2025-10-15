<?php
// ===============================
// Integración WhatsApp - Registro PETBIO
// ===============================

// Incluir conexión y sesión como en identidad_rubm.php
require_once __DIR__ . '/conexion_petbio_nueva.php';
require_once __DIR__ . '/sesion_global.php';
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

// Datos recibidos desde webhook de WhatsApp
$input = json_decode(file_get_contents('php://input'), true);

// Validar usuario logueado
$id_usuario = $_SESSION['id_usuario'] ?? null;
if (!$id_usuario) {
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

// ===============================
// Funciones auxiliares
// ===============================
function enviar_mensaje($numero, $texto) {
    // Aquí iría integración con API WhatsApp (Twilio, Meta API, etc.)
    // Ejemplo: Twilio PHP SDK
    // $client->messages->create($numero, ['from'=> $twilio_number, 'body' => $texto]);
}

// ===============================
// Flujo de preguntas
// ===============================
$paso = $input['paso'] ?? 1;
$numero_usuario = $input['numero'] ?? null;
$respuesta = trim($input['mensaje'] ?? '');

switch($paso) {

    case 1:
        enviar_mensaje($numero_usuario, "¡Hola! Vamos a registrar tu mascota. ¿Cuál es el nombre de la mascota?");
        $siguiente_paso = 2;
        break;

    case 2:
        $_SESSION['nombre_mascota'] = $respuesta;
        enviar_mensaje($numero_usuario, "Perfecto. Ahora, por favor indica los apellidos del cuidador:");
        $siguiente_paso = 3;
        break;

    case 3:
        $_SESSION['apellidos_cuidador'] = $respuesta;
        enviar_mensaje($numero_usuario, "Selecciona la clase de mascota:\n1. Caninos\n2. Felinos\n3. Equinos\n4. Porcinos\n5. Ganado\n6. Otros Mamíferos");
        $siguiente_paso = 4;
        break;

    case 4:
        $clases = ['1'=>'Caninos','2'=>'Felinos','3'=>'Equinos','4'=>'Porcinos','5'=>'Ganado','6'=>'Otros Mamíferos'];
        $_SESSION['clase_mascota'] = $clases[$respuesta] ?? 'Otros Mamíferos';
        enviar_mensaje($numero_usuario, "Condición institucional/social de la mascota:\n1. Hogar familiar\n2. Hogar veterinario\n3. Hogar de paso\n4. Centro regulador\n5. Abandono\n6. Otros");
        $siguiente_paso = 5;
        break;

    case 5:
        $condiciones = ['1'=>'hogar_familiar','2'=>'hogar_veterinario','3'=>'hogar_paso','4'=>'centro_regulador','5'=>'abandono','6'=>'otros'];
        $_SESSION['condicion_mascota'] = $condiciones[$respuesta] ?? 'otros';
        enviar_mensaje($numero_usuario, "Ahora, selecciona la condición médica (ejemplo: sana, enfermedad crónica, etc.)");
        $siguiente_paso = 6;
        break;

    case 6:
        $_SESSION['condicion_medica'] = $respuesta;
        enviar_mensaje($numero_usuario, "Indica la raza de la mascota:");
        $siguiente_paso = 7;
        break;

    case 7:
        $_SESSION['raza'] = $respuesta;
        enviar_mensaje($numero_usuario, "Indica la edad de la mascota (en años):");
        $siguiente_paso = 8;
        break;

    case 8:
        $_SESSION['edad'] = $respuesta;
        enviar_mensaje($numero_usuario, "Ciudad donde resides:");
        $siguiente_paso = 9;
        break;

    case 9:
        $_SESSION['ciudad'] = $respuesta;
        enviar_mensaje($numero_usuario, "Barrio:");
        $siguiente_paso = 10;
        break;

    case 10:
        $_SESSION['barrio'] = $respuesta;
        enviar_mensaje($numero_usuario, "Código postal:");
        $siguiente_paso = 11;
        break;

    case 11:
        $_SESSION['codigo_postal'] = $respuesta;
        enviar_mensaje($numero_usuario, "¿Deseas capturar la huella nasal ahora? Envía la foto como archivo adjunto.");
        $siguiente_paso = 12;
        break;

    case 12:
        // Aquí se puede guardar el archivo multimedia y asociarlo a la sesión
        $_SESSION['img_hf0'] = $respuesta; // ejemplo
        enviar_mensaje($numero_usuario, "Registro completado. Gracias por usar HUELLIT@S PETBIO.");
        $siguiente_paso = null;

        // Guardar en DB
        $stmt = $conexion->prepare("INSERT INTO mascotas (id_usuario,nombre,apellidos,clase,condicion_social,condicion_medica,raza,edad,ciudad,barrio,codigo_postal) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("issssssssss",$id_usuario,$_SESSION['nombre_mascota'],$_SESSION['apellidos_cuidador'],$_SESSION['clase_mascota'],$_SESSION['condicion_mascota'],$_SESSION['condicion_medica'],$_SESSION['raza'],$_SESSION['edad'],$_SESSION['ciudad'],$_SESSION['barrio'],$_SESSION['codigo_postal']);
        $stmt->execute();
        break;

    default:
        enviar_mensaje($numero_usuario, "Error en el flujo. Por favor reinicia el registro.");
        $siguiente_paso = 1;
        break;
}

// Retornar siguiente paso al bot de WhatsApp
echo json_encode(['siguiente_paso'=>$siguiente_paso]);
