<?php
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

$repuestoId = $_GET['repuesto'];
$almacenId = $_GET['almacen'];

$query = "SELECT cantidad FROM inventario_repuesto WHERE id_repuesto = ? AND id_almacen = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("ii", $repuestoId, $almacenId);
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

