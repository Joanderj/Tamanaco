<?php
session_start();
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();

        // Datos base de la actividad
        $id_tarea = intval($_POST['id_tarea'] ?? 0);
        $descripcion_actividad = trim($_POST['descripcion_actividad'] ?? '');
        $fecha_realizar = $_POST['fecha_realizacion'] ?? '';
        $tiempo_invertido = intval($_POST['tiempo_invertido'] ?? 0);
        $minutos_invertidos = intval($_POST['minutos_invertidos'] ?? 0);
        $hora_finalizacion = $_POST['hora_finalizacion'] ?? '';

        if ($id_tarea > 0 && $descripcion_actividad && $fecha_realizar && $hora_finalizacion) {
            // Guardar la actividad principal
            $stmt = $conn->prepare("INSERT INTO actividades (tarea_id, descripcion_actividad, fecha_realizar, tiempo_invertido, minutos_invertidos, hora_finalizacion) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id_tarea, $descripcion_actividad, $fecha_realizar, $tiempo_invertido, $minutos_invertidos, $hora_finalizacion]);
            $actividad_id = $conn->lastInsertId();

            // Guardar responsables
            if (!empty($_POST['responsables_seleccionados'])) {
                $responsables = $_POST['responsables_seleccionados'];
                if (!is_array($responsables)) {
                    $responsables = [$responsables];
                }
                $stmtResp = $conn->prepare("INSERT INTO responsable (persona_id, actividad_id) VALUES (?, ?)");
                foreach ($responsables as $persona_id) {
                    $stmtResp->execute([intval($persona_id), $actividad_id]);
                }
            }

            // Guardar repuestos
            if (!empty($_POST['repuestos']) && is_array($_POST['repuestos'])) {
                $stmtRep = $conn->prepare("INSERT INTO repuesto_actividad (repuesto_id, actividad_id, cantidad, status_id) VALUES (?, ?, ?, ?)");
                foreach ($_POST['repuestos'] as $r) {
                    $stmtRep->execute([
                        intval($r['id']),
                        $actividad_id,
                        floatval($r['cantidad']),
                        intval($r['status_id'])
                    ]);
                }
            }

            // Guardar herramientas pendientes
            if (!empty($_POST['herramientas_pendientes']) && is_array($_POST['herramientas_pendientes'])) {
                $stmtHerr = $conn->prepare("INSERT INTO herramienta_actividad (herramienta_id, id_actividad, tarea_id, fecha_actividad, cantidad, status_id) VALUES (?, ?, ?, ?, ?, ?)");
                foreach ($_POST['herramientas_pendientes'] as $h) {
                    $stmtHerr->execute([
                        intval($h['id']),
                        $actividad_id,
                        $id_tarea,
                        $fecha_realizar,
                        floatval($h['cantidad']),
                        intval($h['status_id'])
                    ]);
                }
            }

            // Guardar productos pendientes
            if (!empty($_POST['productos_pendientes']) && is_array($_POST['productos_pendientes'])) {
                $stmtProd = $conn->prepare("INSERT INTO producto_actividad (producto_id, actividad_id, cantidad, status_id) VALUES (?, ?, ?, ?)");
                foreach ($_POST['productos_pendientes'] as $p) {
                    $stmtProd->execute([
                        intval($p['id']),
                        $actividad_id,
                        floatval($p['cantidad']),
                        intval($p['status_id'])
                    ]);
                }
            }

            $conn->commit();
            $_SESSION['mensaje_exito'] = "Actividad y recursos guardados correctamente.";
            header("Location: formulario_guardar_actividad.php?id=$id_tarea&msg=guardado");
            exit();

        } else {
            throw new Exception("Por favor, completa todos los campos obligatorios.");
        }

    } catch (Exception $e) {
        $conn->rollBack();
        echo "❌ Error al guardar la actividad: " . $e->getMessage();
    }
} else {
    header("Location: formulario_guardar_actividad.php");
    exit();
}
