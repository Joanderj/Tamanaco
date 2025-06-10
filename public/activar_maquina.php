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

        $conn->beginTransaction();

        // Buscar el último momento en que la máquina fue parada (status 13)
        $stmt = $conn->prepare("SELECT fecha_hora FROM estado_maquina_mantenimiento 
                                WHERE id_maquina_unica = ? AND id_tarea = ? AND id_status = 13
                                ORDER BY fecha_hora DESC LIMIT 1");
        $stmt->execute([$id_maquina_unica, $id_tarea]);
        $fecha_paro = $stmt->fetchColumn();

        if (!$fecha_paro) {
            throw new Exception("No hay registro previo de parada para esta tarea.");
        }

        // Calcular duración del paro
        $fecha_inicio = new DateTime($fecha_paro);
        $fecha_actual = new DateTime();
        $diferencia = $fecha_inicio->diff($fecha_actual);
        $horas = $diferencia->h + ($diferencia->days * 24);
        $minutos = $diferencia->i;

        // Formato dinámico legible (ej: "1h 30min")
        $tiempo_formateado = '';
        if ($horas > 0) {
            $tiempo_formateado .= $horas . 'h ';
        }
        if ($minutos > 0 || $horas == 0) {
            $tiempo_formateado .= $minutos . 'min';
        }
        $tiempo_formateado = trim($tiempo_formateado);

        // Reemplazar tiempo de paro en la tarea (ya no se suma, se sobreescribe)
        $updateTarea = $conn->prepare("UPDATE tareas SET tiempo_paro_programado = ? WHERE id_tarea = ?");
        $updateTarea->execute([$tiempo_formateado, $id_tarea]);

        // Cambiar estado de la máquina a "Disponible" (1)
        $updateMaquina = $conn->prepare("UPDATE maquina_unica SET id_status = 1, FechaUltimaActualizacion = NOW() WHERE id_maquina_unica = ?");
        $updateMaquina->execute([$id_maquina_unica]);

        // Registrar cambio en historial
        $insert = $conn->prepare("INSERT INTO estado_maquina_mantenimiento (id_maquina_unica, id_status, fecha_hora, id_tarea)
                                  VALUES (?, 1, NOW(), ?)");
        $insert->execute([$id_maquina_unica, $id_tarea]);

        $conn->commit();

        // Redirigir con mensaje
        header("Location: estado_maquina.php?msg=activada_ok");
    } catch (Exception $e) {
        $conn->rollBack();
        echo "Error al activar la máquina: " . $e->getMessage();
    }
}
