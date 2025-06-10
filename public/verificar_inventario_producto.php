<?php
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

$productoId = $_GET['producto'];
$almacenId = $_GET['almacen'];

$query = "SELECT cantidad FROM inventario_producto WHERE id_producto = ? AND id_almacen = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("ii", $productoId, $almacenId);
$stmt->execute();
$result = $stmt->get_result();

$cantidadDisponible = 0;
if ($row = $result->fetch_assoc()) {
    $cantidadDisponible = $row['cantidad'];
}

$stmt->close();
$conexion->close();

echo json_encode(['cantidad' => $cantidadDisponible]);
?>

