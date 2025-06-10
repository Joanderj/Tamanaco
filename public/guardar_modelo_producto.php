<?php
header('Content-Type: application/json');

$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
if ($conexion->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
    exit;
}

$nombreModelo = trim($_POST['nombre_modelo'] ?? '');
if (!$nombreModelo) {
    echo json_encode(['success' => false, 'message' => 'Nombre del modelo vacío.']);
    exit;
}

$vincular_opcion = $_POST['modelo_vincular_opcion'] ?? '';
if (!in_array($vincular_opcion, ['una', 'varias'])) {
    echo json_encode(['success' => false, 'message' => '¡Error! Opción de vinculación inválida.']);
    exit;
}

$anio = date("Y");
$status = 1;
$fechaCreacion = date("Y-m-d H:i:s");

// Validar si el nombre del modelo ya existe
$stmtCheck = $conexion->prepare("SELECT id_modelo FROM modelo WHERE nombre_modelo = ?");
$stmtCheck->bind_param("s", $nombreModelo);
$stmtCheck->execute();
$resultado = $stmtCheck->get_result();

if ($resultado->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'El modelo ya existe.']);
    $stmtCheck->close();
    $conexion->close();
    exit;
}
$stmtCheck->close();

// Insertar el nuevo modelo
$stmtInsert = $conexion->prepare("INSERT INTO modelo (nombre_modelo, año, id_status, fecha_creacion) VALUES (?, ?, ?, ?)");
$stmtInsert->bind_param("siss", $nombreModelo, $anio, $status, $fechaCreacion);

if (!$stmtInsert->execute()) {
    echo json_encode(['success' => false, 'message' => 'Error al guardar el modelo.']);
    $stmtInsert->close();
    $conexion->close();
    exit;
}

$idModelo = $stmtInsert->insert_id;
$stmtInsert->close();

if ($vincular_opcion === 'una') {
    $idMarca = $_POST['modelo_marca_una'] ?? '';
    if (!$idMarca || !is_numeric($idMarca)) {
        echo json_encode(['success' => false, 'message' => 'ID de la marca inválida o vacía.']);
        $conexion->close();
        exit;
    }

    $stmtVincula = $conexion->prepare("INSERT INTO marca_modelo (id_marca, id_modelo) VALUES (?, ?)");
    $stmtVincula->bind_param("ii", $idMarca, $idModelo);
    if (!$stmtVincula->execute()) {
        echo json_encode(['success' => false, 'message' => 'Error al vincular la marca con el modelo.']);
        $stmtVincula->close();
        $conexion->close();
        exit;
    }
    $stmtVincula->close();

} else if ($vincular_opcion === 'varias') {
    $marcas = $_POST['modelo_marcas'] ?? [];
    if (!is_array($marcas) || count($marcas) === 0) {
        echo json_encode(['success' => false, 'message' => 'No se seleccionaron marcas para vincular.']);
        $conexion->close();
        exit;
    }

    $stmtVincula = $conexion->prepare("INSERT INTO marca_modelo (id_marca, id_modelo) VALUES (?, ?)");
    foreach ($marcas as $idMarca) {
        if (!is_numeric($idMarca)) continue;
        $stmtVincula->bind_param("ii", $idMarca, $idModelo);
        $stmtVincula->execute();
    }
    $stmtVincula->close();
}

$conexion->close();

echo json_encode([
    'success' => true,
    'id_modelo' => $idModelo,
    'nombre_modelo' => $nombreModelo,
    'año' => $anio,
    'status' => $status,
    'fecha_creacion' => $fechaCreacion,
    'mensaje' => 'Modelo creado y vinculado correctamente.'
]);
