<?php
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

$abreviatura = $_GET['abreviatura'];
$id_clasificacion = $_GET['id_clasificacion'] ?? null;

$stmt = $conexion->prepare("SELECT id_clasificacion FROM clasificacion WHERE abreviacion_clasificacion = ? AND id_clasificacion != ?");
$stmt->bind_param("si", $abreviatura, $id_clasificacion);
$stmt->execute();
$stmt->store_result();

$response = ['existe' => $stmt->num_rows > 0, 'id' => $id_clasificacion];
echo json_encode($response);

$stmt->close();
$conexion->close();
?>