<?php
header('Content-Type: application/json');
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');

if ($conexion->connect_error) {
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

$nombreTipo = isset($_POST['nombre_tipo']) ? trim($_POST['nombre_tipo']) : '';
$clasificaciones = isset($_POST['id_clasificacion']) ? $_POST['id_clasificacion'] : [];

if (empty($nombreTipo) || empty($clasificaciones)) {
    echo json_encode(['error' => 'Todos los campos son obligatorios.']);
    exit;
}

// Verificar duplicado
$stmtVerificar = $conexion->prepare("SELECT COUNT(*) FROM tipo WHERE nombre_tipo = ?");
$stmtVerificar->bind_param("s", $nombreTipo);
$stmtVerificar->execute();
$stmtVerificar->bind_result($existe);
$stmtVerificar->fetch();
$stmtVerificar->close();

if ($existe > 0) {
    echo json_encode(['error' => 'Ese tipo ya existe.']);
    exit;
}

// Insertar nuevo tipo
$stmt = $conexion->prepare("INSERT INTO tipo (nombre_tipo, id_status) VALUES (?, 1)");
$stmt->bind_param("s", $nombreTipo);
if (!$stmt->execute()) {
    echo json_encode(['error' => 'Error al insertar el tipo.']);
    exit;
}
$idTipo = $conexion->insert_id;
$stmt->close();

// Insertar relaciones tipo-clasificación
$stmtRelacion = $conexion->prepare("INSERT INTO tipo_clasificacion (id_tipo, id_clasificacion) VALUES (?, ?)");
foreach ($clasificaciones as $idClasificacion) {
    $idClasificacion = (int)$idClasificacion;
    $stmtRelacion->bind_param("ii", $idTipo, $idClasificacion);
    $stmtRelacion->execute();
}
$stmtRelacion->close();

// Obtener las clasificaciones relacionadas
$idsClasificaciones = implode(',', array_map('intval', $clasificaciones));
$resultClasif = $conexion->query("SELECT id_clasificacion, nombre_clasificacion, abreviacion_clasificacion FROM clasificacion WHERE id_clasificacion IN ($idsClasificaciones)");

$clasificacionesFinal = [];
while ($row = $resultClasif->fetch_assoc()) {
    $clasificacionesFinal[] = [
        'id' => $row['id_clasificacion'],
        'nombre' => $row['nombre_clasificacion'],
        'abreviacion' => $row['abreviacion_clasificacion']
    ];
}

// Devolver JSON completo para recargar los <select>
echo json_encode([
    'success' => true,
    'nuevo_tipo' => [
        'id' => $idTipo,
        'nombre' => $nombreTipo
    ],
    'clasificaciones' => $clasificacionesFinal
]);

$conexion->close();
?>
