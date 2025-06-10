<?php
session_start();
header('Content-Type: application/json');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'Usuario no autenticado.']);
    exit();
}

// Conexión a la base de datos
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
if ($conexion->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Error de conexión: ' . $conexion->connect_error]);
    exit();
}

// Obtener datos del cuerpo de la solicitud
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'];
$field = $data['field'];
$value = $data['value'];

// Validar el campo y el valor
if (!in_array($field, ['stock_minimo', 'stock_maximo']) || !is_numeric($value)) {
    echo json_encode(['success' => false, 'error' => 'Campo inválido o valor no numérico.']);
    exit();
}

// Consultar los valores actuales de stock_minimo y stock_maximo
$sql = "SELECT stock_minimo, stock_maximo FROM inventario_producto WHERE id_inventario_producto = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->bind_result($stock_minimo, $stock_maximo);
$stmt->fetch();
$stmt->close();

// Validar que stock_minimo no sea mayor que stock_maximo
if ($field === 'stock_minimo' && $value > $stock_maximo) {
    echo json_encode(['success' => false, 'error' => 'El stock mínimo no puede ser mayor que el stock máximo.']);
    exit();
} elseif ($field === 'stock_maximo' && $value < $stock_minimo) {
    echo json_encode(['success' => false, 'error' => 'El stock máximo no puede ser menor que el stock mínimo.']);
    exit();
}

// Actualizar el valor en la base de datos
$sql = "UPDATE inventario_producto SET $field = ? WHERE id_inventario_producto = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('ii', $value, $id);

if ($stmt->execute()) {
    // Calcular el nuevo punto de reorden
    $sql_reorden = "
        UPDATE inventario_producto 
        SET punto_reorden = (stock_minimo + stock_maximo) / 2 
        WHERE id_inventario_producto = ?";
    
    $stmt_reorden = $conexion->prepare($sql_reorden);
    $stmt_reorden->bind_param('i', $id);
    $stmt_reorden->execute();
    $stmt_reorden->close();

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

// Cerrar conexión
$stmt->close();
$conexion->close();
?>
