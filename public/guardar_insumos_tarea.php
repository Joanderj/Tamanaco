<?php
include 'db_connection.php';
header('Content-Type: application/json');

$id_tarea = $_POST['id_tarea'] ?? null;
$usuario_id = 1;
$fechaAsignacion = date('Y-m-d');

if (!$id_tarea) {
    echo json_encode(['success' => false, 'error' => 'ID de tarea no especificado']);
    exit;
}

try {
    $conn->beginTransaction();

    // Función para crear inventario si no existe
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

    // Función para crear el insumo si no existe
    function crearInsumoSiNoExiste($conn, $tabla, $campo_id, $nombre) {
        $check = $conn->prepare("SELECT $campo_id FROM $tabla WHERE nombre_$tabla = ? LIMIT 1");
        $check->execute([$nombre]);
        $existente = $check->fetchColumn();
        if ($existente) return $existente;

        $stmt = $conn->prepare("INSERT INTO $tabla (nombre_$tabla) VALUES (?)");
        $stmt->execute([$nombre]);
        return $conn->lastInsertId();
    }

    // ===================== REPUESTOS =====================
    if (isset($_POST['repuestos']) && is_array($_POST['repuestos'])) {
        $stmtInsert = $conn->prepare("INSERT INTO repuesto_tarea (repuesto_id, tarea_id, cantidad, costo, status_id) VALUES (?, ?, ?, ?, ?)");
        $stmtMov = $conn->prepare("INSERT INTO movimiento_repuesto (id_repuesto, id_almacen_origen, cantidad, fecha_movimiento, descripcion, id_tipo_movimiento) VALUES (?, ?, ?, ?, ?, 2)");

        foreach ($_POST['repuestos'] as $r) {
            $nombre = trim($r['nombre'] ?? '');
            $cantidad = intval($r['cantidad'] ?? 0);
            $costo = floatval($r['costo'] ?? 0);
            $status_id = intval($r['status_id'] ?? 25);
            $almacen = intval($r['id_almacen'] ?? 1);

            if ($nombre !== '' && $cantidad > 0) {
                $id = crearInsumoSiNoExiste($conn, 'repuesto', 'id_repuesto', $nombre);
                crearInventarioSiNoExiste($conn, 'inventario_repuesto', 'id_repuesto', $id, $almacen);

                $stmtInsert->execute([$id, $id_tarea, $cantidad, $costo, $status_id]);

                if ($status_id == 25) {
                    $conn->prepare("UPDATE inventario_repuesto SET cantidad = cantidad - ? WHERE id_repuesto = ? AND id_almacen = ?")
                         ->execute([$cantidad, $id, $almacen]);
                    $stmtMov->execute([$id, $almacen, $cantidad, $fechaAsignacion, 'Salida por planificación de tarea']);
                }
            }
        }
    }

    // ===================== PRODUCTOS =====================
    if (isset($_POST['productos']) && is_array($_POST['productos'])) {
        $stmtInsert = $conn->prepare("INSERT INTO producto_tarea (producto_id, tarea_id, cantidad, status_id) VALUES (?, ?, ?, ?)");
        $stmtMov = $conn->prepare("INSERT INTO movimiento_producto (id_producto, id_almacen_origen, cantidad, fecha_movimiento, descripcion, id_tipo_movimiento) VALUES (?, ?, ?, ?, ?, 2)");

        foreach ($_POST['productos'] as $p) {
            $nombre = trim($p['nombre'] ?? '');
            $cantidad = intval($p['cantidad'] ?? 0);
            $status_id = intval($p['status_id'] ?? 25);
            $almacen = intval($p['id_almacen'] ?? 1);

            if ($nombre !== '' && $cantidad > 0) {
                $id = crearInsumoSiNoExiste($conn, 'producto', 'id_producto', $nombre);
                crearInventarioSiNoExiste($conn, 'inventario_producto', 'id_producto', $id, $almacen);

                $stmtInsert->execute([$id, $id_tarea, $cantidad, $status_id]);

                if ($status_id == 25) {
                    $conn->prepare("UPDATE inventario_producto SET cantidad = cantidad - ? WHERE id_producto = ? AND id_almacen = ?")
                         ->execute([$cantidad, $id, $almacen]);
                    $stmtMov->execute([$id, $almacen, $cantidad, $fechaAsignacion, 'Salida por planificación de tarea']);
                }
            }
        }
    }

    // ===================== HERRAMIENTAS =====================
    if (isset($_POST['herramientas']) && is_array($_POST['herramientas'])) {
        $stmtInsert = $conn->prepare("INSERT INTO herramienta_tarea (herramienta_id, cantidad, tarea_id, fecha_asignacion, status_id) VALUES (?, ?, ?, ?, ?)");
        $stmtMov = $conn->prepare("INSERT INTO movimiento_herramientas (herramienta_id, usuario_id, tipo_movimiento, fecha_movimiento, status_id) VALUES (?, ?, 2, ?, ?)");

        foreach ($_POST['herramientas'] as $h) {
            $nombre = trim($h['nombre'] ?? '');
            $cantidad = intval($h['cantidad'] ?? 0);
            $status_id = intval($h['status_id'] ?? 25);
            $almacen = intval($h['id_almacen'] ?? 1);

            if ($nombre !== '' && $cantidad > 0) {
                $id = crearInsumoSiNoExiste($conn, 'herramienta', 'id_herramienta', $nombre);
                crearInventarioSiNoExiste($conn, 'inventario_herramientas', 'herramienta_id', $id, $almacen);

                $stmtInsert->execute([$id, $cantidad, $id_tarea, $fechaAsignacion, $status_id]);

                if ($status_id == 25) {
                    $conn->prepare("UPDATE inventario_herramientas SET cantidad = cantidad - ? WHERE herramienta_id = ? AND id_almacen = ?")
                         ->execute([$cantidad, $id, $almacen]);
                    $stmtMov->execute([$id, $usuario_id, $fechaAsignacion, $status_id]);
                }
            }
        }
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Insumos registrados correctamente.']);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
