<?php
include 'db_connection.php';
date_default_timezone_set('America/Caracas');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_maquina_unica = $_POST['id_maquina_unica'] ?? null;
    $id_tarea = $_POST['id_tarea'] ?? null;

    try {
        if (!$id_maquina_unica || !$id_tarea) {
            throw new Exception("Parámetros faltantes");
        }

        // Verificar que la tarea sigue en estatus 5 (en progreso)
        $stmt = $conn->prepare("SELECT status_id FROM tareas WHERE id_tarea = ?");
        $stmt->execute([$id_tarea]);
        $status_tarea = $stmt->fetchColumn();

        if ((int)$status_tarea !== 5) {
            throw new Exception("Solo se pueden parar máquinas con tareas en progreso");
        }

        $conn->beginTransaction();

        // Cambiar estado de la máquina a parada (1)
        $update = $conn->prepare("UPDATE maquina_unica SET id_status = 13, FechaUltimaActualizacion = NOW() WHERE id_maquina_unica = ?");
        $update->execute([$id_maquina_unica]);

        // Registrar evento en historial
        $insert = $conn->prepare("INSERT INTO estado_maquina_mantenimiento (id_maquina_unica, id_status, fecha_hora, id_tarea) VALUES (?, 1, NOW(), ?)");
        $insert->execute([$id_maquina_unica, $id_tarea]);

        $conn->commit();
        header("Location: estado_maquina.php?msg=parada_ok");
    } catch (Exception $e) {
        $conn->rollBack();
        echo "Error al parar la máquina: " . $e->getMessage();
    }
}
