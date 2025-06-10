<?php
// Conexión a la base de datos
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');

if ($conexion->connect_error) {
    die(json_encode(['error' => 'Error de conexión a la base de datos.']));
}

// Obtener el ID de la marca
$idsucursal = isset($_GET['id_sucursal']) ? (int)$_GET['id_sucursal'] : 0;

// Consulta para obtener el nombre de la marca
$queryvalor1 = "SELECT s.nombre,s.fecha_creacion,s.direccion,p.paisnombre,e.estadonombre FROM sucursal s 
JOIN pais p ON p.id = s.pais_id_pais
JOIN estado e ON e.id = s.estado_id_estado
WHERE s.id_sucursal = ?";
$stmtvalor1 = $conexion->prepare($queryvalor1);
$stmtvalor1->bind_param("i", $idsucursal);
$stmtvalor1->execute();
$resultvalor1 = $stmtvalor1->get_result();
$valor1 = $resultvalor1->fetch_assoc();

// Consulta para obtener los modelos relacionados
$queryvalor2 = "
    SELECT se.nombre_sede 
    FROM sede se
    INNER JOIN sede_sucursal ss ON se.id_sede = ss.id_sede
    WHERE ss.id_sucursal = ?
";
$stmtvalor2 = $conexion->prepare($queryvalor2);
$stmtvalor2->bind_param("i", $idsucursal);
$stmtvalor2->execute();
$resultvalor2 = $stmtvalor2->get_result();

$valor2 = [];
while ($row = $resultvalor2->fetch_assoc()) {
    $valor2[] = $row['nombre_sede'];
}

// Respuesta en formato JSON
echo json_encode([
    'nombre' => $valor1['nombre'],
    'pais' => $valor1['paisnombre'],
    'estado' => $valor1['estadonombre'],
    'creacion' => $valor1['fecha_creacion'],
    'direccion' => $valor1['direccion'],
    'sucursal' => $valor2
]);

// Cerrar conexión
$conexion->close();
?>