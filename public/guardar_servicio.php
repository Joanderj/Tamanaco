<?php
require_once 'db_connection.php'; // conexión PDO en $conn

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();

        $nombre_servicio = $_POST['nombre_servicio'];
        $descripcion = $_POST['descripcion'];
        $tiempo_programado = $_POST['tiempo_programado'] ?? null;
        $tiempo_paro_maquina = $_POST['tiempo_paro_maquina'] ?? null;

        // Insertar el servicio principal
        $stmt = $conn->prepare("INSERT INTO servicio 
            (nombre_servicio, descripcion, id_status, date_created, tiempo_programado, tiempo_paro_maquina)
            VALUES (?, ?, 1, NOW(), ?, ?)");
        $stmt->execute([$nombre_servicio, $descripcion, $tiempo_programado, $tiempo_paro_maquina]);
        $id_servicio = $conn->lastInsertId();

// --- 1. PIEZA (pieza a la que se le hace el servicio) ---
if (isset($_POST['pieza']) && is_numeric($_POST['pieza'])) {
    $pieza_repuesto_id = intval($_POST['pieza']); // Siempre sanitiza

    if ($pieza_repuesto_id > 0) {
        try {
            $stmtPieza = $conn->prepare("INSERT INTO servicio_piezas (id_servicio, id_repuesto) VALUES (?, ?)");
            $stmtPieza->execute([$id_servicio, $pieza_repuesto_id]);
        } catch (PDOException $e) {
            // Manejo de errores, útil en producción
            error_log("Error al insertar pieza en servicio_piezas: " . $e->getMessage());
            echo "<div class='text-red-600'>Error al asociar la pieza al servicio.</div>";
        }
    }
}



// ---2. MÁQUINA (una sola máquina seleccionada) ---
if (!empty($_POST['maquina'])) {
    $id_maquina = $_POST['maquina'];
    $stmtMaquina = $conn->prepare("INSERT INTO servicio_maquina (id_servicio, id_maquina) VALUES (?, ?)");
    $stmtMaquina->execute([$id_servicio, $id_maquina]);
}



        // --- 3. REPUESTOS que se van a cambiar (secundarios) ---
        if (!empty($_POST['repuestos']) && is_array($_POST['repuestos'])) {
            $stmtRepuesto = $conn->prepare("INSERT INTO servicio_repuesto (id_servicio, id_repuesto, cantidad) VALUES (?, ?, ?)");
            foreach ($_POST['repuestos'] as $r) {
                if (!empty($r['id'])) {
                    $stmtRepuesto->execute([$id_servicio, $r['id'], $r['cantidad'] ?? 0]);
                }
            }
        }

        // --- 4. HERRAMIENTAS utilizadas ---
        if (!empty($_POST['herramientas']) && is_array($_POST['herramientas'])) {
            $stmtHerramienta = $conn->prepare("INSERT INTO servicio_herramienta (id_servicio, id_herramienta, cantidad) VALUES (?, ?, ?)");
            foreach ($_POST['herramientas'] as $h) {
                if (!empty($h['id'])) {
                    $stmtHerramienta->execute([$id_servicio, $h['id'], $h['cantidad'] ?? 0]);
                }
            }
        }

        // --- 5. PRODUCTOS consumidos ---
        if (!empty($_POST['proveedor_id']) && is_array($_POST['proveedor_id'])) {
    foreach ($_POST['proveedor_id'] as $proveedor_id) {
        if (is_numeric($proveedor_id)) {
            $stmtProveedor = $conn->prepare("INSERT INTO proveedor_servicio (id_proveedor, id_servicio, status_id) VALUES (?, ?, 1)");
            $stmtProveedor->execute([$proveedor_id, $id_servicio]);
        }
    }
}
        // --- 6. VINCULAR A PROVEEDOR (si fue seleccionado) ---
if (!empty($_POST['proveedor_id']) && is_numeric($_POST['proveedor_id'])) {
    $proveedor_id = intval($_POST['proveedor_id']);

    $stmtProveedor = $conn->prepare("INSERT INTO proveedor_servicio (id_proveedor, id_servicio, status_id) VALUES (?, ?, ?)");
    $stmtProveedor->execute([$proveedor_id, $id_servicio, 1]); // 1 = activo por defecto
}


        $conn->commit();
        echo "<script>alert('Servicio registrado exitosamente'); window.location.href='servicio.php';</script>";

    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Error al registrar servicio: " . $e->getMessage());
        echo "<script>alert('Error al registrar el servicio'); history.back();</script>";
    }
}
?>
