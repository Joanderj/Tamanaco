<?php
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

$nombre_clasificacion = $_GET['nombre'];
$id_clasificacion = $_GET['id_clasificacion'] ?? null;

$stmt = $conexion->prepare("SELECT id_clasificacion FROM clasificacion WHERE nombre_clasificacion = ? AND id_clasificacion != ?");
$stmt->bind_param("si", $nombre_clasificacion, $id_clasificacion);
$stmt->execute();
$stmt->store_result();

$response = ['existe' => $stmt->num_rows > 0, 'id' => $id_clasificacion];
echo json_encode($response);

$stmt->close();
$conexion->close();
?>