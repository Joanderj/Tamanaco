<?php
include 'db_connection.php';
date_default_timezone_set('America/Caracas');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_tarea = $_POST["id_tarea"] ?? null;
    $nuevo_status = $_POST["nuevo_status"] ?? null;

    if ($id_tarea && $nuevo_status) {
        try {
            // Iniciar transacción
            $conn->beginTransaction();

            // 1. Actualizar el estado de la tarea
            $query = "UPDATE tareas SET status_id = :nuevo_status WHERE id_tarea = :id_tarea";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':nuevo_status', $nuevo_status, PDO::PARAM_INT);
            $stmt->bindParam(':id_tarea', $id_tarea, PDO::PARAM_INT);
            $stmt->execute();

            // 2. Si el nuevo estado es "En progreso" (ID = 5)
            if ((int)$nuevo_status === 5) {
                // Obtener la máquina asociada a la tarea
                $query_maquina = "SELECT id_maquina_unica FROM tareas WHERE id_tarea = :id_tarea";
                $stmt_maquina = $conn->prepare($query_maquina);
                $stmt_maquina->bindParam(':id_tarea', $id_tarea, PDO::PARAM_INT);
                $stmt_maquina->execute();
                $result = $stmt_maquina->fetch(PDO::FETCH_ASSOC);

                if ($result && isset($result['id_maquina_unica'])) {
                    $id_maquina_unica = $result['id_maquina_unica'];

                    // 2.1 Actualizar estado de la máquina a 13 (ocupada/parada)
                    $query_update = "UPDATE maquina_unica SET id_status = 13, FechaUltimaActualizacion = NOW() WHERE id_maquina_unica = :id_maquina_unica";
                    $stmt_update = $conn->prepare($query_update);
                    $stmt_update->bindParam(':id_maquina_unica', $id_maquina_unica, PDO::PARAM_INT);
                    $stmt_update->execute();

                    // 2.2 Registrar evento en estado_maquina_mantenimiento con id_tarea
                    $query_estado = "INSERT INTO estado_maquina_mantenimiento (id_maquina_unica, id_status, fecha_hora, id_tarea)
                                     VALUES (:id_maquina_unica, 13, NOW(), :id_tarea)";
                    $stmt_estado = $conn->prepare($query_estado);
                    $stmt_estado->bindParam(':id_maquina_unica', $id_maquina_unica, PDO::PARAM_INT);
                    $stmt_estado->bindParam(':id_tarea', $id_tarea, PDO::PARAM_INT);
                    $stmt_estado->execute();
                } else {
                    throw new Exception("Máquina no encontrada para la tarea.");
                }
            }

            $conn->commit();
            echo "ok";
        } catch (Exception $e) {
            $conn->rollBack();
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Parámetros inválidos";
    }
}
?>
