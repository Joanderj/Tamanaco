<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

// Verificar conexión
if ($conexion->connect_error) {
    die(json_encode(["status" => "error", "mensaje" => "Error de conexión: " . $conexion->connect_error]));
}

// Verificar si se recibió el correo
if (!empty($_POST['email'])) {
    $email = $conexion->real_escape_string($_POST['email']);

    // Consulta preparada para evitar inyección SQL
    $query = $conexion->prepare("SELECT COUNT(*) FROM personas WHERE correo_electronico = ?");
    $query->bind_param("s", $email);
    $query->execute();
    $query->bind_result($existe);
    $query->fetch();
    $query->close();

    // Enviar respuesta en formato JSON
    if ($existe > 0) {
        echo json_encode(["status" => "error", "mensaje" => "Este correo ya existe"]);
    } else {
        echo json_encode(["status" => "success", "mensaje" => "¡Este correo está disponible!"]);
    }
} else {
    echo json_encode(["status" => "error", "mensaje" => "Correo no recibido"]);
}

$conexion->close();
?>