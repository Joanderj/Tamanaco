<?php
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');

if ($conexion->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

$consulta = "SELECT id_proveedor, nombre_proveedor FROM proveedor WHERE id_status = 1";
$resultado = $conexion->query($consulta);

$proveedores = [];

while ($fila = $resultado->fetch_assoc()) {
    $proveedores[] = $fila;
}

header('Content-Type: application/json');
echo json_encode($proveedores);
?>
