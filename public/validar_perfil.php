<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

// Verificar conexión
if ($conexion->connect_error) {
    die(json_encode(["status" => "error", "mensaje" => "Error de conexión: " . $conexion->connect_error]));
}

// Verificar si se recibieron los datos
if (!empty($_POST['usuario']) && !empty($_POST['id_perfil'])) {
    $usuario = $conexion->real_escape_string($_POST['usuario']);
    $id_perfil = intval($_POST['id_perfil']);

    // Consulta SQL para verificar si el usuario ya existe dentro del mismo perfil
    $query = $conexion->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = ? AND id_perfil = ?");
    $query->bind_param("si", $usuario, $id_perfil);
    $query->execute();
    $query->bind_result($existe);
    $query->fetch();
    $query->close();

    // Enviar respuesta JSON
    if ($existe > 0) {
        echo json_encode(["status" => "error", "mensaje" => "Este usuario ya existe en este perfil"]);
    } else {
        echo json_encode(["status" => "success", "mensaje" => "¡Este usuario está disponible!"]);
    }
} else {
    echo json_encode(["status" => "error", "mensaje" => "Datos insuficientes"]);
}

$conexion->close();
?>