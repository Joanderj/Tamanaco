<?php
// Conexión a la base de datos
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');

// Verificar si la conexión falló
if ($conexion->connect_error) {
    die(json_encode(['error' => 'Error de conexión a la base de datos.']));
}

// Obtener el ID de la clasificación desde la solicitud
$idClasificacion = isset($_GET['id_clasificacion']) ? (int)$_GET['id_clasificacion'] : 0;

// Validar que se proporcionó un ID válido
if ($idClasificacion <= 0) {
    echo json_encode(['error' => 'ID de clasificación inválido.']);
    exit;
}

// Consulta para obtener el nombre de la clasificación y su fecha de creación
$queryClasificacion = "SELECT nombre_clasificacion, fecha_creacion FROM clasificacion WHERE id_clasificacion = ?";
$stmtClasificacion = $conexion->prepare($queryClasificacion);
$stmtClasificacion->bind_param("i", $idClasificacion);
$stmtClasificacion->execute();
$resultClasificacion = $stmtClasificacion->get_result();

// Validar que la clasificación exista
if ($resultClasificacion->num_rows === 0) {
    echo json_encode(['error' => 'Clasificación no encontrada.']);
    $stmtClasificacion->close();
    $conexion->close();
    exit;
}

$clasificacion = $resultClasificacion->fetch_assoc();

// Consulta para obtener los tipos relacionados con la clasificación
$queryTiposRelacionados = "
    SELECT t.nombre_tipo 
    FROM tipo t
    INNER JOIN tipo_clasificacion tc ON t.id_tipo = tc.id_tipo
    WHERE tc.id_clasificacion = ?
";
$stmtTiposRelacionados = $conexion->prepare($queryTiposRelacionados);
$stmtTiposRelacionados->bind_param("i", $idClasificacion);
$stmtTiposRelacionados->execute();
$resultTiposRelacionados = $stmtTiposRelacionados->get_result();

$tipos = [];
while ($row = $resultTiposRelacionados->fetch_assoc()) {
    $tipos[] = $row['nombre_tipo'];
}

// Generar la respuesta en formato JSON
echo json_encode([
    'nombreClasificacion' => $clasificacion['nombre_clasificacion'],
    'fechaCreacion' => $clasificacion['fecha_creacion'],
    'tipos' => $tipos
]);

// Cerrar la conexión y los recursos
$stmtClasificacion->close();
$stmtTiposRelacionados->close();
$conexion->close();
?>