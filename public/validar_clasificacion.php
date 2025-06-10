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

// Obtener el nombre de la clasificación desde la URL
$nombre_clasificacion = isset($_GET['nombre']) ? trim($_GET['nombre']) : "";

// Verificar si el nombre de la clasificación está vacío
if (empty($nombre_clasificacion)) {
    echo json_encode(["error" => "El nombre de la clasificación es obligatorio."]);
    exit();
}

// Consulta para verificar si la clasificación ya existe
$stmt = $conexion->prepare("SELECT COUNT(*) AS total FROM clasificacion WHERE nombre_clasificacion = ?");
if ($stmt) {
    $stmt->bind_param("s", $nombre_clasificacion);
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