<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido.']);
    exit;
}

try {
    if (!$conn) throw new Exception("Error en la conexión con la base de datos.");

    $conn->beginTransaction();

    $requiredFields = ['titulo_mantenimiento', 'tipo_mantenimiento', 'descripcion_tarea'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("El campo '$field' es obligatorio.");
        }
    }

    $titulo = trim($_POST['titulo_mantenimiento']);
    $descripcion = trim($_POST['descripcion_tarea']);
    $tipo_mantenimiento = intval($_POST['tipo_mantenimiento']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $hora_inicio = $_POST['hora_inicio'];
    $fecha_fin = $_POST['fecha_fin'] ?? null;
    $hora_fin = $_POST['hora_fin'] ?? null;
    $categoria = $_POST['categoria_mantenimiento'] ?? '';
    $costo = is_numeric($_POST['costo_mantenimiento'] ?? null) ? floatval($_POST['costo_mantenimiento']) : 0.0;
    $proveedor = !empty($_POST['proveedor']) ? intval($_POST['proveedor']) : null;
    $status = $proveedor ? 26 : 1;
    $importancia = !empty($_POST['id_importancia']) ? intval($_POST['id_importancia']) : null;
    $id_maquina_unica = !empty($_POST['id_maquina_unica']) ? intval($_POST['id_maquina_unica']) : null;
    $id_servicio = !empty($_POST['servicio']) ? intval($_POST['servicio']) : null;
    $id_sede = !empty($_POST['id_sede']) ? intval($_POST['id_sede']) : null;

    $usuario_id = 1; // puedes obtenerlo dinámicamente si usas sesiones
    $fechaAsignacion = date('Y-m-d');
    $id_solicitud = null;

    if ($proveedor && $costo > 0) {
        $stmtSolicitud = $conn->prepare("INSERT INTO solicitudes (id_tipo_solicitud, id_usuario, fecha_solicitud, id_status, id_perfil) VALUES (1, NULL, NOW(), 1, 3)");
        $stmtSolicitud->execute();
        $id_solicitud = $conn->lastInsertId();
    }

    $horas_programadas = intval($_POST['horas_mantenimiento'] ?? 0);
    $minutos_programados = intval($_POST['minutos_mantenimiento'] ?? 0);
    $tiempo_programado = ($horas_programadas * 60) + $minutos_programados;

    $horas_paro = intval($_POST['horas_parada'] ?? 0);
    $minutos_paro = intval($_POST['minutos_parada'] ?? 0);
    $tiempo_paro = ($horas_paro * 60) + $minutos_paro;

    $stmt = $conn->prepare("INSERT INTO tareas (
        titulo_tarea, descripcion_tarea, tipo_mantenimiento_id, fecha_inicio,
        hora_inicio, fecha_fin, hora_fin, categoria_mantenimiento, costo,
        tiempo_programado, tiempo_paro_maquina, status_id, proveedor_id,
        id_importancia, id_maquina_unica, id_servicio, id_sede, id_solicitud
    ) VALUES (
        :titulo, :descripcion, :tipo_mantenimiento, :fecha_inicio,
        :hora_inicio, :fecha_fin, :hora_fin, :categoria, :costo,
        :tiempo_programado, :tiempo_paro, :status, :proveedor,
        :importancia, :id_maquina_unica, :id_servicio, :id_sede, :id_solicitud
    )");

    $stmt->execute([
        ':titulo' => $titulo,
        ':descripcion' => $descripcion,
        ':tipo_mantenimiento' => $tipo_mantenimiento,
        ':fecha_inicio' => $fecha_inicio,
        ':hora_inicio' => $hora_inicio,
        ':fecha_fin' => $fecha_fin,
        ':hora_fin' => $hora_fin,
        ':categoria' => $categoria,
        ':costo' => $costo,
        ':tiempo_programado' => $tiempo_programado,
        ':tiempo_paro' => $tiempo_paro,
        ':status' => $status,
        ':proveedor' => $proveedor,
        ':importancia' => $importancia,
        ':id_maquina_unica' => $id_maquina_unica,
        ':id_servicio' => $id_servicio,
        ':id_sede' => $id_sede,
        ':id_solicitud' => $id_solicitud
    ]);

    $id_tarea = $conn->lastInsertId();

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

    // RESPONSABLES
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

    // REPUESTOS
    if (isset($_POST['repuestos']) && is_array($_POST['repuestos'])) {
        $stmtRepuesto = $conn->prepare("INSERT INTO repuesto_tarea (repuesto_id, tarea_id, cantidad, costo, status_id) VALUES (?, ?, ?, ?, ?)");
        $stmtMov = $conn->prepare("INSERT INTO movimiento_repuesto (id_repuesto, id_almacen_origen, cantidad, fecha_movimiento, descripcion, id_tipo_movimiento) VALUES (?, ?, ?, ?, ?, 2)");
        foreach ($_POST['repuestos'] as $r) {
            $id = intval($r['id'] ?? 0);
            $cantidad = intval($r['cantidad'] ?? 0);
            $costo = floatval($r['costo'] ?? 0.0);
            $status_id = intval($r['status_id'] ?? 25);
            $almacen = intval($r['id_almacen'] ?? 1);
            if ($id && $cantidad > 0) {
                crearInventarioSiNoExiste($conn, 'inventario_repuesto', 'id_repuesto', $id, $almacen);
                $stmtRepuesto->execute([$id, $id_tarea, $cantidad, $costo, $status_id]);
                if ($status_id == 25) {
                    $conn->prepare("UPDATE inventario_repuesto SET cantidad = cantidad - ? WHERE id_repuesto = ? AND id_almacen = ?")->execute([$cantidad, $id, $almacen]);
                    $stmtMov->execute([$id, $almacen, $cantidad, $fechaAsignacion, 'Salida por planificación de tarea']);
                }
            }
        }
    }

    // HERRAMIENTAS
    $stmtHerramienta = $conn->prepare("INSERT INTO herramienta_tarea (herramienta_id, cantidad, tarea_id, fecha_asignacion, status_id) VALUES (?, ?, ?, ?, ?)");
    $stmtMovH = $conn->prepare("INSERT INTO movimiento_herramientas (herramienta_id, usuario_id, tipo_movimiento, fecha_movimiento, status_id) VALUES (?, ?, 2, ?, ?)");
    foreach (['herramientas_planificadas' => 25, 'herramientas_pendientes' => 26] as $clave => $estado) {
        if (isset($_POST[$clave]) && is_array($_POST[$clave])) {
            foreach ($_POST[$clave] as $h) {
                $id = intval($h['id'] ?? 0);
                $cantidad = intval($h['cantidad'] ?? 0);
                $status_id = intval($h['status_id'] ?? $estado);
                if ($id && $cantidad > 0) {
                    crearInventarioSiNoExiste($conn, 'inventario_herramientas', 'herramienta_id', $id);
                    $stmtHerramienta->execute([$id, $cantidad, $id_tarea, $fechaAsignacion, $status_id]);
                    if ($status_id == 25) {
                        $conn->prepare("UPDATE inventario_herramientas SET cantidad = cantidad - ? WHERE herramienta_id = ?")->execute([$cantidad, $id]);
                        $stmtMovH->execute([$id, $usuario_id, $fechaAsignacion, $status_id]);
                    }
                }
            }
        }
    }

    // PRODUCTOS
    $stmtProducto = $conn->prepare("INSERT INTO producto_tarea (producto_id, tarea_id, cantidad, status_id) VALUES (?, ?, ?, ?)");
    $stmtMovP = $conn->prepare("INSERT INTO movimiento_producto (id_producto, id_almacen_origen, cantidad, fecha_movimiento, descripcion, id_tipo_movimiento) VALUES (?, ?, ?, ?, ?, 2)");
    foreach (['productos_planificados' => 25, 'productos_pendientes' => 26] as $clave => $estado) {
        if (isset($_POST[$clave]) && is_array($_POST[$clave])) {
            foreach ($_POST[$clave] as $p) {
                $id = intval($p['id'] ?? 0);
                $cantidad = intval($p['cantidad'] ?? 0);
                $status_id = intval($p['status_id'] ?? $estado);
                $almacen = intval($p['id_almacen'] ?? 1);
                if ($id && $cantidad > 0) {
                    crearInventarioSiNoExiste($conn, 'inventario_producto', 'id_producto', $id, $almacen);
                    $stmtProducto->execute([$id, $id_tarea, $cantidad, $status_id]);
                    if ($status_id == 25) {
                        $conn->prepare("UPDATE inventario_producto SET cantidad = cantidad - ? WHERE id_producto = ? AND id_almacen = ?")->execute([$cantidad, $id, $almacen]);
                        $stmtMovP->execute([$id, $almacen, $cantidad, $fechaAsignacion, 'Salida por planificación de tarea']);
                    }
                }
            }
        }
    }

    // CALENDARIO
    $stmtCal = $conn->prepare("INSERT INTO calendario (titulo, fecha_inicio, fecha_fin, tarea_id, tipo_evento_id, status_id) VALUES (?, ?, ?, ?, 1, 1)");
    $stmtCal->execute([
        $titulo,
        $fecha_inicio . ' ' . $hora_inicio,
        $fecha_fin ? ($fecha_fin . ' ' . $hora_fin) : null,
        $id_tarea
    ]);

    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Tarea registrada exitosamente.']);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
if (isset($id_tarea) && $id_tarea > 0) {
    header("Location: formulario_guardar_actividad.php?id=$id_tarea&msg=guardado");
    exit;
}