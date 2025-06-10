<?php
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
$conexion->set_charset("utf8");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$query = "SELECT id_marca, nombre_marca FROM marca WHERE id_status = 1";
$resultado = $conexion->query($query);

$marcas = array();
while ($fila = $resultado->fetch_assoc()) {
    $marcas[] = $fila;
}

echo json_encode($marcas);
$conexion->close();
?>
