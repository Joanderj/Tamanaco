<?php
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
$tipos = [];

$resultado = $conexion->query("SELECT id_tipo, nombre_tipo FROM tipo WHERE id_status = 1 ORDER BY nombre_tipo ASC");
while ($fila = $resultado->fetch_assoc()) {
    $tipos[] = [
        'id' => $fila['id_tipo'],
        'nombre' => $fila['nombre_tipo']
    ];
}
$conexion->close();

echo json_encode($tipos);
?>
