<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

// Comprobar conexión
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

// Obtener el ID de la máquina desde la solicitud POST
$id_maquina = isset($_POST['id_maquina']) ? intval($_POST['id_maquina']) : 0;

// Consultar la cantidad (número de filas con ese id_maquina)
$query = "SELECT COUNT(*) AS total FROM maquina_unica WHERE id_maquina = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $id_maquina);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Devolver la cantidad total
$total_cantidad = $row ? intval($row['total']) : 0;
echo $total_cantidad;

// Cerrar conexión
$stmt->close();
$conexion->close();
?>
