<?php
include 'db_connection.php';

$id_solicitud = $_GET['id_solicitud'] ?? null;

if (!$id_solicitud) {
    echo json_encode(['error' => 'ID de solicitud no proporcionado']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT t.titulo_tarea, t.descripcion_tarea, t.costo, t.status_id, 
               s.fecha_solicitud, ts.nombre_tipo 
        FROM tareas t
        JOIN solicitudes s ON t.id_solicitud = s.id_solicitud
        JOIN tipos_solicitudes ts ON s.id_tipo_solicitud = ts.id_tipo_solicitud
        WHERE t.id_solicitud = :id_solicitud
        LIMIT 1
    ");
    $stmt->execute([':id_solicitud' => $id_solicitud]);
    $tarea = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tarea) {
        echo json_encode(['error' => 'No se encontró una tarea asociada']);
        exit;
    }

    echo json_encode([
        'titulo' => $tarea['titulo_tarea'],
        'descripcion' => $tarea['descripcion_tarea'],
        'costo' => $tarea['costo'],
        'fecha' => date('d/m/Y', strtotime($tarea['fecha_solicitud'])),
        'tipo' => $tarea['nombre_tipo'],
        'status' => $tarea['status_id'] // ✅ Ahora se incluye el estado
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al consultar la tarea: ' . $e->getMessage()]);
}
?>
