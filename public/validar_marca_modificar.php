<?php
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

$nombre = $_GET['nombre'];
$id_marca = $_GET['id'] ?? null;

$stmt = $conexion->prepare("SELECT id_marca FROM marca WHERE nombre_marca = ? AND id_marca != ?");
$stmt->bind_param("si", $nombre, $id_marca);
$stmt->execute();
$stmt->store_result();

$response = ['existe' => $stmt->num_rows > 0, 'id' => $id_marca];
echo json_encode($response);

$stmt->close();
$conexion->close();