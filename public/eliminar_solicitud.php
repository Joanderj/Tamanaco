<?php
include 'db_connection.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id_solicitud'])) {
    echo json_encode(['error' => 'ID no proporcionado.']);
    exit;
}

$id_solicitud = intval($data['id_solicitud']);

try {
    $stmt = $conn->prepare("UPDATE solicitudes SET status = 2 WHERE id_solicitud = :id");
    $stmt->execute([':id' => $id_solicitud]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'No se encontró o no se actualizó la solicitud.']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
