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

// Obtener el nombre del cargo desde la URL
$nombre_cargo = isset($_GET['nombre']) ? trim($_GET['nombre']) : "";

// Verificar si el nombre del cargo está vacío
if (empty($nombre_cargo)) {
    echo json_encode(["error" => "El nombre del cargo es obligatorio."]);
    exit();
}

// Consulta para verificar si el cargo ya existe
$stmt = $conexion->prepare("SELECT COUNT(*) AS total FROM cargo WHERE nombre_cargo = ?");
if ($stmt) {
    $stmt->bind_param("s", $nombre_cargo);
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