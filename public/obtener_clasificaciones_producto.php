<?php
header('Content-Type: application/json');

// Conexión a la base de datos
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');

// Verificar si hay errores de conexión
if ($conexion->connect_error) {
    die(json_encode(['error' => 'Error de conexión a la base de datos.']));
}

// Obtener el ID del tipo desde la solicitud
$tipoId = isset($_GET['tipo']) ? (int)$_GET['tipo'] : 0;

// Validar que el ID sea válido
if ($tipoId <= 0) {
    echo json_encode([]);
    exit;
}

// Consultar las clasificaciones relacionadas con el tipo seleccionado
$consulta = "
    SELECT clasificacion.id_clasificacion, clasificacion.nombre_clasificacion, clasificacion.abreviacion_clasificacion
    FROM clasificacion
    INNER JOIN tipo_clasificacion ON clasificacion.id_clasificacion = tipo_clasificacion.id_clasificacion
    WHERE tipo_clasificacion.id_tipo = ? AND clasificacion.id_status = 1
";
$stmt = $conexion->prepare($consulta);
$stmt->bind_param("i", $tipoId);
$stmt->execute();
$resultado = $stmt->get_result();

$clasificaciones = [];
while ($fila = $resultado->fetch_assoc()) {
    $clasificaciones[] = $fila;
}

// Devolver las clasificaciones como JSON
echo json_encode($clasificaciones);

// Cerrar la conexión
$stmt->close();
$conexion->close();
?>