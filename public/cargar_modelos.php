<?php
$marca_id = $_GET['marca_id'];
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
$stmt = $conexion->prepare("SELECT id_modelo, nombre_modelo FROM modelo WHERE id_status = 1 AND id_marca = ?");
$stmt->bind_param("i", $marca_id);
$stmt->execute();
$resultado = $stmt->get_result();
while ($fila = $resultado->fetch_assoc()) {
    echo "<option value='{$fila['id_modelo']}'>{$fila['nombre_modelo']}</option>";
}
$conexion->close();
