<?php
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

$nombre = $_GET['nombre'];
$id = $_GET['id'] ?? null;

$stmt = $conexion->prepare("SELECT id_sucursal FROM sucursal WHERE nombre = ? AND id_sucursal != ?");
$stmt->bind_param("si", $nombre, $id);
$stmt->execute();
$stmt->store_result();

$response = ['existe' => $stmt->num_rows > 0, 'id' => $id];
echo json_encode($response);

$stmt->close();
$conexion->close();