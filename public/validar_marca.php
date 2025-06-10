<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

// Verificar la conexión
if ($conexion->connect_error) {
    die("Error en la conexión: " . $conexion->connect_error);
}

// Obtener el nombre enviado desde AJAX
$nombreMarca = $_GET['nombre'];

// Consultar si la marca ya existe
$sql = $conexion->prepare("SELECT COUNT(*) AS total FROM marca WHERE nombre_marca = ?");
$sql->bind_param("s", $nombreMarca);
$sql->execute();
$resultado = $sql->get_result();
$fila = $resultado->fetch_assoc();

// Retornar respuesta en formato JSON
echo json_encode(['existe' => $fila['total'] > 0]);

// Cerrar conexión
$sql->close();
$conexion->close();
?>