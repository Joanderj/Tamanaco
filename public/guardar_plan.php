<?php
include 'db_connection.php';

function crearInventarioSiNoExiste($conn, $tabla, $campo, $id, $almacen = 1) {
    $check = $conn->prepare("SELECT COUNT(*) FROM $tabla WHERE $campo = ? AND id_almacen = ?");
    $check->execute([$id, $almacen]);
    if ($check->fetchColumn() == 0) {
        switch ($tabla) {
            case 'inventario_repuesto':
                $stmt = $conn->prepare("INSERT INTO inventario_repuesto (id_repuesto, cantidad, id_almacen, stock_minimo, stock_maximo, punto_reorden) VALUES (?, 0, ?, 10, 20, 15)");
                break;
            case 'inventario_producto':
                $stmt = $conn->prepare("INSERT INTO inventario_producto (id_producto, cantidad, id_almacen, stock_minimo, stock_maximo, punto_reorden) VALUES (?, 0, ?, 10, 20, 15)");
                break;
            case 'inventario_herramientas':
                $stmt = $conn->prepare("INSERT INTO inventario_herramientas (herramienta_id, cantidad, id_almacen, stock_minimo, stock_maximo, punto_reorden, status_id) VALUES (?, 0, ?, 10, 20, 15, 1)");
                break;
        }
        $stmt->execute([$id, $almacen]);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido.']);
    exit;
}

try {
    $conn->beginTransaction();
    $fechaAsignacion = date('Y-m-d H:i:s');

    $titulo = $_POST['titulo_mantenimiento'] ?? 'Tarea generada por plan';
    $descripcion = $_POST['descripcion_tarea'] ?? '';

    $fecha_inicio = $_POST['fecha_inicio'] ?? date('Y-m-d');
    $hora_inicio = $_POST['hora_inicio'] ?? '00:00:00';
    $duracion_dias = intval($_POST['duracion_dias'] ?? 1);

    $fecha_fin_dt = new DateTime($fecha_inicio);
    $fecha_fin_dt->modify("+$duracion_dias days");
    $fecha_fin = $fecha_fin_dt->format('Y-m-d');
    $hora_fin = $_POST['hora_fin'] ?? '00:00:00';

    $tiempo_programado = (intval($_POST['horas_mantenimiento'] ?? 0) * 60) + intval($_POST['minutos_mantenimiento'] ?? 0);
    $tiempo_paro = (intval($_POST['horas_parada'] ?? 0) * 60) + intval($_POST['minutos_parada'] ?? 0);

    // --- PLAN ---
    $stmtPlan = $conn->prepare("INSERT INTO planes (
        nombre_plan, descripcion_plan, trigger_opcion, frecuencia, tipo_frecuencia,
        dia_mes, semana_mes, dia_semana, proveedor_id, fecha_asociacion, costo_aprox, duracion
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)");

    $stmtPlan->execute([
        $titulo,
        $descripcion,
        $_POST['trigger'] ?? null,
        $_POST['frecuencia'] ?? null,
        $_POST['tipo_frecuencia'] ?? null,
        $_POST['dia_mes'] ?? null,
        $_POST['semana_mes'] ?? null,
        $_POST['dia_semana'] ?? null,
        $_POST['proveedor_id'] ?? null,
        floatval($_POST['costo_mantenimiento'] ?? 0),
        $duracion_dias
    ]);

    $id_plan = $conn->lastInsertId();

    // --- TAREA ---
    $stmtTarea = $conn->prepare("INSERT INTO tareas (
        titulo_tarea, descripcion_tarea, tipo_mantenimiento_id, fecha_inicio, hora_inicio,
        fecha_fin, hora_fin, categoria_mantenimiento, costo, tiempo_programado, tiempo_paro_maquina,
        status_id, proveedor_id, id_importancia, id_maquina_unica, id_servicio, id_sede, id_plan
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?, ?)");

    $stmtTarea->execute([
        $titulo,
        $descripcion,
        $_POST['tipo_mantenimiento'] ?? 1,
        $fecha_inicio,
        $hora_inicio,
        $fecha_fin,
        $hora_fin,
        $_POST['categoria_mantenimiento'] ?? '',
        floatval($_POST['costo_mantenimiento'] ?? 0),
        $tiempo_programado,
        $tiempo_paro,
        $_POST['proveedor_id'] ?? null,
        $_POST['id_importancia'] ?? null,
        $_POST['id_maquina_unica'] ?? null,
        $_POST['servicio'] ?? null,
        $_POST['id_sede'] ?: null,
        $id_plan
    ]);

    $id_tarea = $conn->lastInsertId();

    // --- PLAN_EJECUCIONES ---
    $conn->prepare("INSERT INTO plan_ejecuciones (id_plan, id_tarea, fecha_ejecucion) VALUES (?, ?, NOW())")
        ->execute([$id_plan, $id_tarea]);

    // --- RESPONSABLES ---
    if (!empty($_POST['responsables_seleccionados'])) {
        $responsables = explode(',', $_POST['responsables_seleccionados']);
        $stmtResp = $conn->prepare("INSERT INTO responsable (persona_id, tarea_id) VALUES (?, ?)");
        foreach ($responsables as $persona_id) {
            $persona_id = intval($persona_id);
            if ($persona_id > 0) {
                $stmtResp->execute([$persona_id, $id_tarea]);
            }
        }
    }

    // --- REPUESTOS ---
    $stmtRepuesto = $conn->prepare("INSERT INTO repuesto_tarea (repuesto_id, tarea_id, cantidad, costo, status_id) VALUES (?, ?, ?, ?, ?)");
    $stmtRepuestoPlan = $conn->prepare("INSERT INTO repuesto_plan (repuesto_id, plan_id, cantidad, costo, costo_aprox, status_id, proveedor_id, fecha_asociacion) VALUES (?, ?, ?, 0, 0, ?, ?, NOW())");

    foreach ($_POST['repuestos'] ?? [] as $r) {
        $id = intval($r['id'] ?? 0);
        $cantidad = intval($r['cantidad'] ?? 0);
        $status_id = intval($r['status_id'] ?? 25);
        if ($id && $cantidad > 0) {
            crearInventarioSiNoExiste($conn, 'inventario_repuesto', 'id_repuesto', $id);
            $stmtRepuesto->execute([$id, $id_tarea, $cantidad, 0, $status_id]);
            $stmtRepuestoPlan->execute([$id, $id_plan, $cantidad, $status_id, $_POST['proveedor_id'] ?? null]);
        }
    }

    // --- HERRAMIENTAS ---
    $stmtHerrTarea = $conn->prepare("INSERT INTO herramienta_tarea (herramienta_id, cantidad, tarea_id, fecha_asignacion, status_id) VALUES (?, ?, ?, ?, ?)");
    $stmtHerrPlan = $conn->prepare("INSERT INTO herramienta_plan (herramienta_id, plan_id, fecha_asociacion, status_id, cantidad) VALUES (?, ?, NOW(), ?, ?)");

    foreach (['herramientas_planificadas' => 25, 'herramientas_pendientes' => 26] as $clave => $estado) {
        foreach ($_POST[$clave] ?? [] as $h) {
            $id = intval($h['id'] ?? 0);
            $cantidad = intval($h['cantidad'] ?? 0);
            $status_id = intval($h['status_id'] ?? $estado);
            if ($id && $cantidad > 0) {
                crearInventarioSiNoExiste($conn, 'inventario_herramientas', 'herramienta_id', $id);
                $stmtHerrTarea->execute([$id, $cantidad, $id_tarea, $fechaAsignacion, $status_id]);
                $stmtHerrPlan->execute([$id, $id_plan, $status_id, $cantidad]);
            }
        }
    }

    // --- PRODUCTOS ---
    $stmtProdTarea = $conn->prepare("INSERT INTO producto_tarea (producto_id, tarea_id, cantidad, status_id) VALUES (?, ?, ?, ?)");
    $stmtProdPlan = $conn->prepare("INSERT INTO producto_plan (producto_id, plan_id, cantidad, costo, status_id) VALUES (?, ?, ?, 0, ?)");

    foreach (['productos_planificados' => 25, 'productos_pendientes' => 26] as $clave => $estado) {
        foreach ($_POST[$clave] ?? [] as $p) {
            $id = intval($p['id'] ?? 0);
            $cantidad = intval($p['cantidad'] ?? 0);
            $status_id = intval($p['status_id'] ?? $estado);
            if ($id && $cantidad > 0) {
                crearInventarioSiNoExiste($conn, 'inventario_producto', 'id_producto', $id);
                $stmtProdTarea->execute([$id, $id_tarea, $cantidad, $status_id]);
                $stmtProdPlan->execute([$id, $id_plan, $cantidad, $status_id]);
            }
        }
    }

    // --- CALENDARIO ---
    $stmtCal = $conn->prepare("INSERT INTO calendario (titulo, fecha_inicio, fecha_fin, tarea_id, tipo_evento_id, status_id) VALUES (?, ?, ?, ?, 1, 1)");
    $stmtCal->execute([
        $titulo,
        $fecha_inicio . ' ' . $hora_inicio,
        $fecha_fin . ' ' . $hora_fin,
        $id_tarea
    ]);

    $conn->commit();
    header('Location: planes.php');
    exit;
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
