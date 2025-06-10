<?php
session_start();
header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// Obtener datos del request
$data = json_decode(file_get_contents('php://input'), true);
$id_inventario_producto = $data['id_inventario_producto'] ?? null;
$field2 = $data['field2'] ?? null;
$value2 = $data['value2'] ?? null;

// Validar datos
if (!$id_inventario_producto || !$field2 || $value2 === null) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit();
}

// Validar que el campo sea editable
$allowedFields = ['stock_maximo'];
if (!in_array($field2, $allowedFields)) {
    echo json_encode(['success' => false, 'message' => 'Campo no editable']);
    exit();
}

// Conexión a la base de datos
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
if ($conexion->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $conexion->connect_error]);
    exit();
}

// Actualizar en la base de datos
$sql = "UPDATE inventario_producto SET $field2 = ? WHERE id_inventario_producto = ?";
$stmt = $conexion->prepare($sql);

if ($field2 === 'stock_maximo') {
    $stmt->bind_param("ii", $value2, $id_inventario_producto);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $stmt->error]);
}

$stmt->close();
$conexion->close();
?>

