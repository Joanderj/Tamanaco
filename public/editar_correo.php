<?php
session_start(); // Iniciar la sesión para usar variables de sesión

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: iniciar_sesion.php");
    exit();
}

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

// Comprobar conexión
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
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

// Verificar si se encontraron datos
if (!$perfil) {
    $_SESSION['error'] = "No se encontraron datos para el usuario.";
    header("Location: datos_personales.php");
    exit();
}

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar datos del formulario y manejar valores nulos
    $email = !empty($_POST['email']) ? $_POST['email'] : $perfil["correo_electronico"];
    $cedula = $perfil["cedula"];

    // Validar el formato del correo electrónico
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Formato de correo electrónico inválido.";
        header("Location: correo.php");
        exit();
    }

    // Datos fijos o predeterminados
    $id_status = 1; // Estado activo por defecto

    // Validación de datos
    $conexion->begin_transaction(); // Iniciar una transacción para asegurar atomicidad

    try {
        // Preparar la consulta de actualización
        $stmt = $conexion->prepare("UPDATE personas SET correo_electronico=? WHERE cedula=?");
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta de actualización: " . $conexion->error);
        }

        // Vincular parámetros
        $stmt->bind_param("si", $email, $cedula);

        // Ejecutar la consulta
        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }

        // Registrar actividad en `registro_actividades`
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $dispositivo = $_SERVER['HTTP_USER_AGENT'];
        $accion = 'Edito';
        $actividad = 'Datos Correo';
        $modulo = 'Perfil';

        $stmt = $conexion->prepare("INSERT INTO registro_actividades (id_usuario, accion, actividad, modulo, ip_address, dispositivo) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $perfil['id_usuario'], $accion, $actividad, $modulo, $ip_address, $dispositivo);

        if (!$stmt->execute()) {
            throw new Exception("Error al registrar actividad: " . $stmt->error);
        }

        // Confirmar la transacción
        $conexion->commit();

        // Guardar el mensaje en una variable de sesión
        $_SESSION['mensaje'] = "Persona actualizada correctamente con cédula: $cedula.";

        // Redirigir al usuario a la URL deseada
        header("Location: correo.php");
        exit(); // Asegurar que el script se detenga después de la redirección
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conexion->rollback();
        $_SESSION['error'] = "Error: {$e->getMessage()}";
        header("Location: correo.php");
        exit();
    } finally {
        // Cerrar los statements y la conexión
        if (isset($stmt)) $stmt->close();
        $conexion->close();
    }
}
?>
