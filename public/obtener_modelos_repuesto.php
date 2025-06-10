<?php
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

$id_marca = $_GET['id_marca'];
$sql = "SELECT modelo.id_modelo, modelo.nombre_modelo 
        FROM modelo 
        INNER JOIN marca_modelo ON modelo.id_modelo = marca_modelo.id_modelo 
        WHERE marca_modelo.id_marca = $id_marca";

$resultado = $conexion->query($sql);
$modelos = [];

while ($fila = $resultado->fetch_assoc()) {
    $modelos[] = $fila;
}

echo json_encode($modelos);
$conexion->close();
?>