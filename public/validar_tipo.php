<?php
// Configuración para enviar respuestas en formato JSON
header("Content-Type: application/json");

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

// Verificar si la conexión a la base de datos es exitosa
if ($conexion->connect_error) {
    echo json_encode(["error" => "Error de conexión a la base de datos."]);
    exit();
}

// Obtener el nombre del tipo desde la URL
$nombre_tipo = isset($_GET['nombre']) ? trim($_GET['nombre']) : "";

// Verificar si el nombre del tipo está vacío
if (empty($nombre_tipo)) {
    echo json_encode(["error" => "El nombre del tipo es obligatorio."]);
    exit();
}

// Consulta para verificar si el tipo ya existe
$stmt = $conexion->prepare("SELECT COUNT(*) AS total FROM tipo WHERE nombre_tipo = ?");
if ($stmt) {
    $stmt->bind_param("s", $nombre_tipo);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $fila = $resultado->fetch_assoc();

    // Retornar el resultado como JSON
    echo json_encode(["existe" => $fila['total'] > 0]);

    // Cerrar la declaración
    $stmt->close();
} else {
    echo json_encode(["error" => "Error al preparar la consulta."]);
}

// Cerrar la conexión
$conexion->close();
?>