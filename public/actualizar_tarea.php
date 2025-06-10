<?php
include 'db_connection.php'; // Conexión a la BD
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 1. Recoger los datos del formulario
        $id_tarea = $_POST['id_tarea'] ?? null;
        $tipo_mantenimiento = $_POST['tipo_mantenimiento'] ?? null;
        $id_importancia = $_POST['id_importancia'] ?? null;
        $titulo = trim($_POST['titulo_mantenimiento'] ?? '');
        $descripcion = trim($_POST['descripcion_tarea'] ?? '');
        $fecha_inicio = $_POST['fecha_inicio'] ?? null;
        $hora_inicio = $_POST['hora_inicio'] ?? null;
        $fecha_fin = $_POST['fecha_fin'] ?? null;
        $hora_fin = $_POST['hora_fin'] ?? null;
        $horas_mantenimiento = $_POST['horas_mantenimiento'] ?? 0;
        $minutos_mantenimiento = $_POST['minutos_mantenimiento'] ?? 0;
        $horas_parada = $_POST['horas_parada'] ?? 0;
        $minutos_parada = $_POST['minutos_parada'] ?? 0;

        if (!$id_tarea || !$tipo_mantenimiento || !$id_importancia || !$titulo) {
            echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios']);
            exit;
        }

        // 2. Calcular tiempo programado y de paro como minutos totales
        $tiempo_programado = ($horas_mantenimiento * 60) + $minutos_mantenimiento;
        $tiempo_paro_maquina = ($horas_parada * 60) + $minutos_parada;

        // 3. Preparar y ejecutar la consulta
        $sql = "UPDATE tareas SET 
                    tipo_mantenimiento_id = :tipo,
                    id_importancia = :importancia,
                    titulo_tarea = :titulo,
                    descripcion_tarea = :descripcion,
                    fecha_inicio = :fecha_inicio,
                    hora_inicio = :hora_inicio,
                    fecha_fin = :fecha_fin,
                    hora_fin = :hora_fin,
                    tiempo_programado = :tiempo_programado,
                    tiempo_paro_maquina = :tiempo_paro
                WHERE id_tarea = :id_tarea";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':tipo', $tipo_mantenimiento);
        $stmt->bindParam(':importancia', $id_importancia);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':fecha_inicio', $fecha_inicio);
        $stmt->bindParam(':hora_inicio', $hora_inicio);
        $stmt->bindParam(':fecha_fin', $fecha_fin);
        $stmt->bindParam(':hora_fin', $hora_fin);
        $stmt->bindParam(':tiempo_programado', $tiempo_programado);
        $stmt->bindParam(':tiempo_paro', $tiempo_paro_maquina);
        $stmt->bindParam(':id_tarea', $id_tarea);

        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Tarea actualizada exitosamente.']);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
}
?>
