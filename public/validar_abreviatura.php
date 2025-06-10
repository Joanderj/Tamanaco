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

// Obtener la abreviatura desde la URL
$abreviatura = isset($_GET['abreviatura']) ? trim($_GET['abreviatura']) : "";

// Verificar si la abreviatura está vacía
if (empty($abreviatura)) {
    echo json_encode(["error" => "La abreviatura es obligatoria."]);
    exit();
}

// Consulta para verificar si la abreviatura ya existe
$stmt = $conexion->prepare("SELECT COUNT(*) AS total FROM clasificacion WHERE abreviacion_clasificacion = ?");
if ($stmt) {
    $stmt->bind_param("s", $abreviatura);
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