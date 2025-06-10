<?php
include 'db_connection.php';

function calcularTiempo($inicio, $fin) {
    $start = new DateTime($inicio);
    $end = new DateTime($fin);
    $interval = $start->diff($end);
    return $interval->format('%a días, %h horas, %i minutos');
}

function calcularProximaFecha($fechaActual, $frecuencia, $tipo) {
    $fecha = new DateTime($fechaActual);
    switch (strtolower($tipo)) {
        case 'días': $fecha->modify("+$frecuencia days"); break;
        case 'semanas': $fecha->modify("+$frecuencia weeks"); break;
        case 'meses': $fecha->modify("+$frecuencia months"); break;
        default: return null;
    }
    return $fecha->format('Y-m-d');
}

function copiarMaterialesDesdePlan($conn, $id_plan, $id_tarea, $fecha_asignacion) {
    // REPUESTOS
    $stmt = $conn->prepare("SELECT repuesto_id, cantidad FROM repuesto_plan WHERE plan_id = ?");
    $stmt->execute([$id_plan]);
    foreach ($stmt->fetchAll() as $r) {
        $conn->prepare("INSERT INTO repuesto_tarea (repuesto_id, tarea_id, cantidad, costo, status_id)
                        VALUES (?, ?, ?, 0, 26)")
             ->execute([$r['repuesto_id'], $id_tarea, $r['cantidad']]);
    }

    // PRODUCTOS
    $stmt = $conn->prepare("SELECT producto_id, cantidad FROM producto_plan WHERE plan_id = ?");
    $stmt->execute([$id_plan]);
    foreach ($stmt->fetchAll() as $p) {
        $conn->prepare("INSERT INTO producto_tarea (producto_id, tarea_id, cantidad, status_id)
                        VALUES (?, ?, ?, 26)")
             ->execute([$p['producto_id'], $id_tarea, $p['cantidad']]);
    }

    // HERRAMIENTAS
    $stmt = $conn->prepare("SELECT herramienta_id, cantidad FROM herramienta_plan WHERE plan_id = ?");
    $stmt->execute([$id_plan]);
    foreach ($stmt->fetchAll() as $h) {
        $conn->prepare("INSERT INTO herramienta_tarea (herramienta_id, cantidad, tarea_id, fecha_asignacion, status_id)
                        VALUES (?, ?, ?, ?, 26)")
             ->execute([$h['herramienta_id'], $h['cantidad'], $id_tarea, $fecha_asignacion]);
    }
}

if (!isset($_POST['id_tarea']) || !isset($_POST['observacion']) || !isset($_POST['fecha_hora_finalizacion'])) {
    echo json_encode(['success' => false, 'error' => 'Faltan datos necesarios']);
    exit;
}

$id_tarea = intval($_POST['id_tarea']);
$observacion = $_POST['observacion'];
$fecha_fin_actual = $_POST['fecha_hora_finalizacion'];

try {
    $conn->beginTransaction();

    // Obtener datos de la tarea
    $stmt = $conn->prepare("SELECT * FROM tareas WHERE id_tarea = ?");
    $stmt->execute([$id_tarea]);
    $tarea = $stmt->fetch();

    if (!$tarea) throw new Exception("Tarea no encontrada.");

    $fecha_inicio_real = $tarea['fecha_inicio'] . ' ' . $tarea['hora_inicio'];
    $fecha_fin_planificada = $tarea['fecha_fin'] . ' ' . $tarea['hora_fin'];

    // Activar máquina si estaba en estado 13
    if ($tarea['id_maquina_unica']) {
        $stmt = $conn->prepare("SELECT id_status FROM maquina_unica WHERE id_maquina_unica = ?");
        $stmt->execute([$tarea['id_maquina_unica']]);
        $maq = $stmt->fetch();
        if ($maq && $maq['id_status'] == 13) {
            $conn->prepare("UPDATE maquina_unica SET id_status = 1 WHERE id_maquina_unica = ?")
                 ->execute([$tarea['id_maquina_unica']]);
        }
    }

    // Calcular tiempos
    $tiempo_programado = calcularTiempo($fecha_inicio_real, $fecha_fin_planificada);
    $tiempo_paro = calcularTiempo($fecha_inicio_real, $fecha_fin_actual);

    // Finalizar tarea
    $conn->prepare("UPDATE tareas SET status_id = 7, fecha_hora_finalizacion = ?, observacion = ?, 
                    tiempo_programado = ?, tiempo_paro_maquina = ?, tipo_mantenimiento_id = 1
                    WHERE id_tarea = ?")
         ->execute([$fecha_fin_actual, $observacion, $tiempo_programado, $tiempo_paro, $id_tarea]);

    $mensaje_extra = "";

    // Si tiene plan, generar nueva tarea
    if ($tarea['id_plan']) {
        $stmt = $conn->prepare("SELECT * FROM planes WHERE id_plan = ?");
        $stmt->execute([$tarea['id_plan']]);
        $plan = $stmt->fetch();

        if ($plan) {
            $nueva_fecha_inicio = calcularProximaFecha($tarea['fecha_fin'], $plan['frecuencia'], $plan['tipo_frecuencia']);
            $nueva_fecha_fin = (new DateTime($nueva_fecha_inicio))->modify("+{$plan['duracion']} days")->format('Y-m-d');

            $stmtInsert = $conn->prepare("INSERT INTO tareas (
                titulo_tarea, descripcion_tarea, tipo_mantenimiento_id, fecha_inicio, hora_inicio,
                fecha_fin, hora_fin, categoria_mantenimiento, costo, tiempo_programado, tiempo_paro_maquina,
                status_id, proveedor_id, id_importancia, id_maquina_unica, id_servicio, id_sede, id_plan
            ) VALUES (?, ?, 1, ?, ?, ?, ?, ?, ?, '', '', 1, ?, ?, ?, ?, ?, ?)");

            $stmtInsert->execute([
                $tarea['titulo_tarea'],
                $tarea['descripcion_tarea'],
                $nueva_fecha_inicio,
                $tarea['hora_inicio'],
                $nueva_fecha_fin,
                $tarea['hora_fin'],
                $tarea['categoria_mantenimiento'],
                $tarea['costo'],
                $tarea['proveedor_id'],
                $tarea['id_importancia'],
                $tarea['id_maquina_unica'],
                $tarea['id_servicio'],
                $tarea['id_sede'],
                $plan['id_plan']
            ]);

            $nueva_tarea_id = $conn->lastInsertId();

            // Registrar ejecución del plan
            $conn->prepare("INSERT INTO plan_ejecuciones (id_plan, id_tarea, fecha_ejecucion) 
                            VALUES (?, ?, NOW())")
                 ->execute([$plan['id_plan'], $nueva_tarea_id]);

            // Copiar materiales con status 26
            copiarMaterialesDesdePlan($conn, $plan['id_plan'], $nueva_tarea_id, date('Y-m-d H:i:s'));

            // Registrar en calendario
            $conn->prepare("INSERT INTO calendario (
                titulo, fecha_inicio, fecha_fin, tarea_id, tipo_evento_id, status_id
            ) VALUES (?, ?, ?, ?, 1, 1)")
                 ->execute([
                    $tarea['titulo_tarea'],
                    $nueva_fecha_inicio . ' ' . $tarea['hora_inicio'],
                    $nueva_fecha_fin . ' ' . $tarea['hora_fin'],
                    $nueva_tarea_id
                 ]);

            $mensaje_extra = " | Nueva tarea generada automáticamente.";
        }
    }

    $conn->commit();
    echo json_encode([
        'success' => true,
        'msg' => 'Tarea finalizada correctamente.',
        'tiempo_programado' => $tiempo_programado,
        'tiempo_paro_maquina' => $tiempo_paro,
        'detalle' => $mensaje_extra
    ]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
