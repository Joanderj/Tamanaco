<?php
session_start(); // Iniciar la sesión para usar variables de sesión

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: iniciar_sesion.php");
    exit();
}

// Conexión a la base de datos
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');

// Verificar conexión
if ($conexion->connect_error) {
    die("Error en la conexión: " . $conexion->connect_error);
}

// Obtener datos del usuario y la persona asociada
$sql = "SELECT u.*, p.* FROM usuarios u 
        JOIN personas p ON u.id_persona = p.id_persona 
        WHERE u.usuario = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
$perfil = $result->fetch_assoc();
$stmt->close();

if (!$perfil) {
    $_SESSION['error'] = "No se encontraron datos para el usuario.";
    header("Location: datos_personales.php");
    exit();
}

// Capturar datos del formulario y manejar valores nulos
$imagen = !empty($_POST['imagen']) ? $_POST['imagen'] : null;
$usuario = !empty($_POST['usuario']) ? $_POST['usuario'] : null;
$clave = !empty($_POST['clave']) ? $_POST['clave'] : null;
$id = !empty($_POST['id']) ? $_POST['id'] : null;

// Datos fijos o predeterminados
$id_status = 1; // Estado activo por defecto

// Validación de datos
$conexion->begin_transaction(); // Iniciar una transacción para asegurar atomicidad

try {
    // Preparar la consulta de actualización
    $stmt = $conexion->prepare("UPDATE usuarios SET nombre_imagen=?, usuario=?, clave=? WHERE id_usuario=?");
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta de actualización: " . $conexion->error);
    }

    // Vincular parámetros
    $stmt->bind_param("sssi", $imagen, $usuario, $clave, $id);

    // Ejecutar la consulta de actualización
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta de actualización: " . $stmt->error);
    }

    // Registrar actividad en `registro_actividades`
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $dispositivo = $_SERVER['HTTP_USER_AGENT'];
    $accion = 'Editó';
    $actividad = 'Datos de Seguridad';
    $modulo = 'Perfil';

    $stmt = $conexion->prepare("INSERT INTO registro_actividades (id_usuario, accion, actividad, modulo, ip_address, dispositivo) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $perfil['id_usuario'], $accion, $actividad, $modulo, $ip_address, $dispositivo);

    if (!$stmt->execute()) {
        throw new Exception("Error al registrar actividad: " . $stmt->error);
    }

    // Confirmar la transacción
    $conexion->commit();

    // Guardar el mensaje en una variable de sesión
    $_SESSION['mensaje'] = "Contraseña actualizada correctamente.";

    // Redirigir al usuario a la URL deseada
    header("Location: seguridad.php");
    exit(); // Asegurar que el script se detenga después de la redirección
} catch (Exception $e) {
    // Revertir la transacción en caso de error
    $conexion->rollback();
    $_SESSION['error'] = "Error: {$e->getMessage()}";
    header("Location: seguridad.php");
    exit();
} finally {
    // Cerrar los statements y la conexión
    if (isset($stmt)) $stmt->close();
    $conexion->close();
}
?>
