<?php
$response = ['success' => false, 'message' => ''];

$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
if ($conexion->connect_error) {
    $response['message'] = 'Error de conexión';
    echo json_encode($response);
    exit;
}

$nombre = trim($_POST['nombre_clasificacion'] ?? '');
$abreviatura = trim($_POST['abreviatura_clasificacion'] ?? '');
$opcion = $_POST['vincular_opcion_tipo'] ?? '';
$tipos = [];

if ($opcion === 'uno') {
    $tipos[] = $_POST['tipo_uno'];
} elseif ($opcion === 'varios' && isset($_POST['tipos']) && is_array($_POST['tipos'])) {
    $tipos = $_POST['tipos'];
}

if ($nombre && $abreviatura && count($tipos) > 0) {
    $conexion->begin_transaction();
    try {
        // Insertar clasificación
        $stmt = $conexion->prepare("INSERT INTO clasificacion (nombre_clasificacion, abreviacion_clasificacion, id_status) VALUES (?, ?, ?)");
        $id_status = 1; // o el ID válido que representa "Activo"
        $stmt->bind_param("ssi", $nombre, $abreviatura, $id_status);
        $stmt->execute();
        $id_clasificacion = $stmt->insert_id;
        $stmt->close();

        // Insertar relaciones con tipo
        $stmtRel = $conexion->prepare("INSERT INTO tipo_clasificacion (id_clasificacion, id_tipo) VALUES (?, ?)");
        foreach ($tipos as $tipo_id) {
            $stmtRel->bind_param("ii", $id_clasificacion, $tipo_id);
            $stmtRel->execute();
        }
        $stmtRel->close();

        $conexion->commit();
        $response['success'] = true;
    } catch (Exception $e) {
        $conexion->rollback();
        $response['message'] = 'Error al guardar: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Faltan campos requeridos';
}

$conexion->close();
echo json_encode($response);
