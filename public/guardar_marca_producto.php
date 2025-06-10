<?php
header('Content-Type: application/json');

$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
if ($conexion->connect_error) {
    echo json_encode(['success' => false, 'mensaje' => 'Error de conexión a la base de datos']);
    exit;
}

$nombre_marca = strtoupper(trim($_POST['nombre_marca'] ?? ''));
$modelos = $_POST['modelos'] ?? [];

if (!$nombre_marca || empty($modelos)) {
    echo json_encode(['success' => false, 'mensaje' => 'Datos incompletos']);
    exit;
}

// Verificar si ya existe la marca activa
$check = $conexion->prepare("SELECT COUNT(*) FROM marca WHERE nombre_marca = ? AND id_status = 1");
$check->bind_param("s", $nombre_marca);
$check->execute();
$check->bind_result($existe);
$check->fetch();
$check->close();

if ($existe > 0) {
    echo json_encode(['success' => false, 'mensaje' => 'La marca ya existe']);
    exit;
}

// Insertar la nueva marca
$stmt = $conexion->prepare("INSERT INTO marca (nombre_marca, id_status) VALUES (?, 1)");
$stmt->bind_param("s", $nombre_marca);
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'mensaje' => 'Error al guardar marca']);
    exit;
}
$id_marca = $stmt->insert_id;
$stmt->close();

// Asociar modelos con prepared statement seguro
$stmtAssoc = $conexion->prepare("INSERT INTO marca_modelo (id_marca, id_modelo) VALUES (?, ?)");
foreach ($modelos as $id_modelo) {
    $id_modelo = (int)$id_modelo; // Asegurar que es entero
    $stmtAssoc->bind_param("ii", $id_marca, $id_modelo);
    $stmtAssoc->execute();
}
$stmtAssoc->close();

$conexion->close();

echo json_encode(['success' => true, 'id_nueva_marca' => $id_marca]);
