<?php
session_start(); // Iniciar la sesión

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

if (!$perfil) {
    $_SESSION['error'] = "No se encontraron datos para el usuario.";
    header("Location: datos_personales.php");
    exit();
}

// Verificar si se recibió la cédula y si el método es POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cedula'])) {
    $cedula = $_POST['cedula'];
    $pais_id = $_POST['pais'] ?? $perfil["pais_id"];
    $estado_id = $_POST['estado'] ?? $perfil["estado_id"];
    $direccion = $_POST['direccion'] ?? $perfil["direccion"];

    $usuario = $perfil["id_usuario"];

    // Preparar la consulta SQL para actualizar los datos
    $stmt = $conexion->prepare("UPDATE personas SET pais_id = ?, estado_id = ?, direccion = ? WHERE cedula = ?");
    $stmt->bind_param("iiss", $pais_id, $estado_id, $direccion, $cedula);

    // Registrar actividad en `registro_actividades`
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $dispositivo = $_SERVER['HTTP_USER_AGENT'];
    $accion = 'Editó';
    $actividad = 'Dirección Habitación';
    $modulo = 'Perfil';

    // Preparar la consulta para registrar la actividad
    $stmtRegistro = $conexion->prepare("INSERT INTO registro_actividades (id_usuario, accion, actividad, modulo, ip_address, dispositivo) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtRegistro->bind_param("isssss", $perfil['id_usuario'], $accion, $actividad, $modulo, $ip_address, $dispositivo);

    // Ejecutar la consulta de actualización y la inserción de actividad
    if ($stmt->execute() && $stmtRegistro->execute()) {
        // Mensaje de éxito en la sesión
        $_SESSION['mensaje'] = "Datos actualizados correctamente.";
        header("Location: direccion_habitacion.php");
        exit();
    } else {
        // Mensaje de error en la sesión
        $_SESSION['mensaje'] = "Error al actualizar los datos: " . $stmt->error . " | " . $stmtRegistro->error;
        // Redirigir a la misma página para mostrar el mensaje
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Cerrar las declaraciones
    $stmt->close();
    $stmtRegistro->close();
}

// Cerrar conexión
$conexion->close();
?>

