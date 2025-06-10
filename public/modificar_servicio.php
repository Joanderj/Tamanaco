<?php
require_once 'db_connection.php'; // conexión PDO en $conn

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();

        $id_servicio = $_POST['id_servicio'];
        $nombre_servicio = $_POST['nombre_servicio'];
        $descripcion = $_POST['descripcion'];
        $tiempo_programado = $_POST['tiempo_programado'] ?? null;
        $tiempo_paro_maquina = $_POST['tiempo_paro_maquina'] ?? null;

        // Actualizar servicio principal
        $stmt = $conn->prepare("UPDATE servicio SET 
            nombre_servicio = ?, 
            descripcion = ?, 
            tiempo_programado = ?, 
            tiempo_paro_maquina = ? 
            WHERE id_servicio = ?");
        $stmt->execute([$nombre_servicio, $descripcion, $tiempo_programado, $tiempo_paro_maquina, $id_servicio]);

        // Limpiar relaciones anteriores
        $conn->prepare("DELETE FROM servicio_maquina WHERE id_servicio = ?")->execute([$id_servicio]);
        $conn->prepare("DELETE FROM servicio_piezas WHERE id_servicio = ?")->execute([$id_servicio]);
        $conn->prepare("DELETE FROM servicio_repuesto WHERE id_servicio = ?")->execute([$id_servicio]);
        $conn->prepare("DELETE FROM servicio_herramienta WHERE id_servicio = ?")->execute([$id_servicio]);
        $conn->prepare("DELETE FROM servicio_producto WHERE id_servicio = ?")->execute([$id_servicio]);
        $conn->prepare("DELETE FROM proveedor_servicio WHERE id_servicio = ?")->execute([$id_servicio]);

        // --- 1. MÁQUINA asociada ---
        if (!empty($_POST['maquina']) && is_numeric($_POST['maquina'])) {
            $stmtMaquina = $conn->prepare("INSERT INTO servicio_maquina (id_servicio, id_maquina) VALUES (?, ?)");
            $stmtMaquina->execute([$id_servicio, $_POST['maquina']]);
        }

        // --- 2. PIEZA (si aplica) ---
        if (!empty($_POST['pieza']) && is_numeric($_POST['pieza'])) {
            $stmtPieza = $conn->prepare("INSERT INTO servicio_piezas (id_servicio, id_repuesto) VALUES (?, ?)");
            $stmtPieza->execute([$id_servicio, $_POST['pieza']]);
        }

        // --- 3. PRODUCTOS consumidos ---
        if (!empty($_POST['productos']) && is_array($_POST['productos'])) {
            $stmtProducto = $conn->prepare("INSERT INTO servicio_producto (id_servicio, id_producto, cantidad) VALUES (?, ?, ?)");
            foreach ($_POST['productos'] as $id_producto => $p) {
                if (!empty($id_producto)) {
                    $cantidad = isset($p['cantidad']) ? $p['cantidad'] : 0;
                    $stmtProducto->execute([$id_servicio, $id_producto, $cantidad]);
                }
            }
        }

        // --- 4. HERRAMIENTAS utilizadas ---
        if (!empty($_POST['herramientas']) && is_array($_POST['herramientas'])) {
            $stmtHerramienta = $conn->prepare("INSERT INTO servicio_herramienta (id_servicio, id_herramienta, cantidad) VALUES (?, ?, ?)");
            foreach ($_POST['herramientas'] as $id_herramienta => $h) {
                if (!empty($id_herramienta)) {
                    $cantidad = isset($h['cantidad']) ? $h['cantidad'] : 0;
                    $stmtHerramienta->execute([$id_servicio, $id_herramienta, $cantidad]);
                }
            }
        }

        // --- 3. REPUESTOS que se van a cambiar (secundarios) ---
if (!empty($_POST['repuestos']) && is_array($_POST['repuestos'])) {
    $stmtRepuesto = $conn->prepare("INSERT INTO servicio_repuesto (id_servicio, id_repuesto, cantidad) VALUES (?, ?, ?)");
    foreach ($_POST['repuestos'] as $id_repuesto => $r) {
        if (!empty($id_repuesto)) {
            $cantidad = isset($r['cantidad']) ? $r['cantidad'] : 0;
            $stmtRepuesto->execute([$id_servicio, $id_repuesto, $cantidad]);
        }
    }
}

        // --- 5. PROVEEDORES (uno o varios) ---
        if (!empty($_POST['proveedor_id'])) {
            $proveedores = is_array($_POST['proveedor_id']) ? $_POST['proveedor_id'] : [$_POST['proveedor_id']];
            $stmtProveedor = $conn->prepare("INSERT INTO proveedor_servicio (id_proveedor, id_servicio, status_id) VALUES (?, ?, 1)");
            foreach ($proveedores as $pid) {
                if (is_numeric($pid)) {
                    $stmtProveedor->execute([$pid, $id_servicio]);
                }
            }
        }

        $conn->commit();
        echo "<script>alert('Servicio modificado correctamente'); window.location.href='servicio.php';</script>";

    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Error al modificar el servicio: " . $e->getMessage());
        echo "<script>alert('Error al modificar el servicio'); history.back();</script>";
    }
}
?>
