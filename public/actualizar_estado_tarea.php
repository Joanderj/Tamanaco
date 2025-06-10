<?php
include 'db_connection.php';

$data = json_decode(file_get_contents("php://input"), true);
$id_solicitud = $data['id_solicitud'] ?? null;
$nuevo_status = $data['nuevo_status'] ?? null;

if (!$id_solicitud || !$nuevo_status) {
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE tareas SET status_id = :status WHERE id_solicitud = :id_solicitud");
    $stmt->execute([
        ':status' => $nuevo_status,
        ':id_solicitud' => $id_solicitud
    ]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al actualizar el estado: ' . $e->getMessage()]);
}
?>
