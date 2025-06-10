<?php
header('Content-Type: application/json');

$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
if ($conexion->connect_error) {
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

$id_proveedor = isset($_GET['id_proveedor']) ? intval($_GET['id_proveedor']) : 0;

$sql = "SELECT * FROM proveedor WHERE id_proveedor = $id_proveedor";
$result = $conexion->query($sql);

if ($result->num_rows > 0) {
    $proveedor = $result->fetch_assoc();
    echo json_encode($proveedor);
} else {
    echo json_encode(['error' => 'Proveedor no encontrado']);
}
