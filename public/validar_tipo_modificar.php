<?php
header('Content-Type: application/json');

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
if ($conexion->connect_error) {
    die(json_encode(['error' => 'Error de conexión a la base de datos.']));
}

// Obtener los parámetros de la solicitud
$nombre_tipo = $_GET['nombre'] ?? '';
$id_tipo = $_GET['id_tipo'] ?? 0;

// Validar que los parámetros sean válidos
if (empty($nombre_tipo) || $id_tipo <= 0) {
    echo json_encode(['error' => 'Parámetros inválidos.']);
    exit;
}

// Consulta para verificar si el nombre del tipo ya existe, excluyendo el tipo actual
$stmt = $conexion->prepare("
    SELECT COUNT(*) AS existe 
    FROM tipo 
    WHERE nombre_tipo = ? AND id_tipo != ?
");
$stmt->bind_param("si", $nombre_tipo, $id_tipo);
$stmt->execute();
$resultado = $stmt->get_result();
$existe = $resultado->fetch_assoc()['existe'];

// Responder con el resultado de la validación
echo json_encode(['existe' => $existe > 0, 'id_tipo' => $id_tipo]);

// Cerrar la conexión
$stmt->close();
$conexion->close();
?>