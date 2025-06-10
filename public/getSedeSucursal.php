<?php
// Conexión a la base de datos
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');

if ($conexion->connect_error) {
    die(json_encode(['error' => 'Error de conexión a la base de datos.']));
}

// Obtener el ID de la marca
$idsede = isset($_GET['id_sede']) ? (int)$_GET['id_sede'] : 0;

// Consulta para obtener el nombre de la marca
$queryvalor1 = "SELECT s.nombre_sede,st.nombre_status,sc.nombre,s.fecha_creacion FROM sede s 
JOIN status st ON st.id_status = s.id_status 
JOIN sucursal sc ON sc.id_sucursal = s.id_sucursal_fija
WHERE id_sede = ?";
$stmtvalor1 = $conexion->prepare($queryvalor1);
$stmtvalor1->bind_param("i", $idsede);
$stmtvalor1->execute();
$resultvalor1 = $stmtvalor1->get_result();
$valor1 = $resultvalor1->fetch_assoc();

// Consulta para obtener los modelos relacionados
$queryvalor2 = "
    SELECT su.nombre 
    FROM sucursal su
    INNER JOIN sede_sucursal ss ON su.id_sucursal = ss.id_sucursal
    WHERE ss.id_sede = ?
";
$stmtvalor2 = $conexion->prepare($queryvalor2);
$stmtvalor2->bind_param("i", $idsede);
$stmtvalor2->execute();
$resultvalor2 = $stmtvalor2->get_result();

$valor2 = [];
while ($row = $resultvalor2->fetch_assoc()) {
    $valor2[] = $row['nombre'];
}

// Respuesta en formato JSON
echo json_encode([
    'nombre' => $valor1['nombre_sede'],
    'fija' => $valor1['nombre'],
    'fecha' => $valor1['fecha_creacion'],
    'status' => $valor1['nombre_status'],
    'sucursal' => $valor2
]);

// Cerrar conexión
$conexion->close();
?>