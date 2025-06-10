<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

// Verificar conexión
if ($conexion->connect_error) {
    die(json_encode(["status" => "error", "mensaje" => "Error de conexión: " . $conexion->connect_error]));
}

// Verificar si se envió la cédula y nacionalidad
if (!empty($_POST['cedula']) && !empty($_POST['nacionalidad'])) {
    $cedula = $conexion->real_escape_string($_POST['cedula']);
    $nacionalidad = $conexion->real_escape_string($_POST['nacionalidad']);

    // Consulta SQL para verificar si la cédula existe
    $query = $conexion->prepare("SELECT COUNT(*) FROM personas WHERE cedula = ? AND nacionalidad = ?");
    $query->bind_param("ss", $cedula, $nacionalidad);
    $query->execute();
    $query->bind_result($existe);
    $query->fetch();
    $query->close();

    // Enviar respuesta en formato JSON
    if ($existe > 0) {
        echo json_encode(["status" => "error", "mensaje" => "Esta cédula ya existe"]);
    } else {
        echo json_encode(["status" => "success", "mensaje" => "¡Esta cédula está disponible!"]);
    }
} else {
    echo json_encode(["status" => "error", "mensaje" => "Datos insuficientes"]);
}

$conexion->close();
?>