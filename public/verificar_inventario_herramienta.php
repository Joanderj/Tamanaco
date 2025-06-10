<?php
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

$herramientaId = $_GET['herramienta'];
$almacenId = $_GET['almacen'];

$query = "SELECT cantidad FROM inventario_herramientas WHERE herramienta_id = ? AND id_almacen = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("ii", $herramientaId, $almacenId);
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

