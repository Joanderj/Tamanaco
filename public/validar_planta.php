<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

// Verificar la conexión
if ($conexion->connect_error) {
    die("Error en la conexión: " . $conexion->connect_error);
}

// Obtener el nombre enviado desde AJAX
$nombre = $_GET['nombre'];

// Consultar si la sucursal ya existe
$sql = $conexion->prepare("SELECT COUNT(*) AS total FROM planta WHERE nombre = ?");
$sql->bind_param("s", $nombre);
$sql->execute();
$resultado = $sql->get_result();
$fila = $resultado->fetch_assoc();

// Retornar respuesta en formato JSON
echo json_encode(['existe' => $fila['total'] > 0]);

// Cerrar conexión
$sql->close();
$conexion->close();
?>