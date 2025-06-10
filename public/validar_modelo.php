<?php
// Configuración para enviar respuestas JSON
header("Content-Type: application/json");

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

// Verificar si la conexión a la base de datos es exitosa
if ($conexion->connect_error) {
    echo json_encode(["error" => "Error de conexión a la base de datos."]);
    exit();
}

// Obtener el nombre del modelo desde la URL
$nombre_modelo = isset($_GET['nombre']) ? trim($_GET['nombre']) : "";

// Verificar si el nombre del modelo está vacío
if (empty($nombre_modelo)) {
    echo json_encode(["error" => "El nombre del modelo es obligatorio."]);
    exit();
}

// Consulta para verificar si el modelo ya existe
$stmt = $conexion->prepare("SELECT COUNT(*) AS total FROM modelo WHERE nombre_modelo = ?");
$stmt->bind_param("s", $nombre_modelo);
$stmt->execute();
$resultado = $stmt->get_result();
$fila = $resultado->fetch_assoc();

// Retornar el resultado como JSON
if ($fila['total'] > 0) {
    echo json_encode(["existe" => true]);
} else {
    echo json_encode(["existe" => false]);
}

// Cerrar la conexión
$stmt->close();
$conexion->close();
?>