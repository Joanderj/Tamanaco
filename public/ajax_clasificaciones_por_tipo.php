<?php
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

$id_tipo = intval($_GET['id_tipo']);
$clasificaciones = [];

$query = "SELECT c.id_clasificacion, c.nombre_clasificacion
          FROM tipo_clasificacion tc
          JOIN clasificacion c ON tc.id_clasificacion = c.id_clasificacion
          WHERE tc.id_tipo = $id_tipo AND tc.id_status = 1";

$resultado = $conexion->query($query);
while ($fila = $resultado->fetch_assoc()) {
    $clasificaciones[] = ['id' => $fila['id_clasificacion'], 'nombre' => $fila['nombre_clasificacion']];
}

header('Content-Type: application/json');
echo json_encode($clasificaciones);
