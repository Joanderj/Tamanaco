<?php
include 'db_connection.php';
header('Content-Type: application/json');

$id_solicitud = $_POST['id_solicitud'] ?? null;
$accion = $_POST['accion'] ?? null;
$tipo = $_POST['tipo'] ?? null;

if (!$id_solicitud || !$accion || !$tipo) {
  echo json_encode(['error' => 'Datos incompletos']);
  exit;
}

try {
  // 1. Verificar tipo de solicitud
  $stmt = $conn->prepare("SELECT id_tipo_solicitud FROM solicitudes WHERE id_solicitud = :id LIMIT 1");
  $stmt->execute([':id' => $id_solicitud]);
  $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$solicitud) {
    echo json_encode(['error' => 'Solicitud no encontrada']);
    exit;
  }

  $tipo_solicitud = (int) $solicitud['id_tipo_solicitud'];

  // Acción solo si es aceptar
  if ($accion !== 'aceptar') {
    echo json_encode(['error' => 'Acción no válida']);
    exit;
  }

  if ($tipo_solicitud === 1) {
    // ---------------------- //
    // TIPO 1: MANTENIMIENTO //
    // ---------------------- //

    // 2. Actualizar tarea(s) a status 1
    $stmt = $conn->prepare("UPDATE tareas SET status_id = 1 WHERE id_solicitud = :id");
    $stmt->execute([':id' => $id_solicitud]);

    // 3. Cambiar solicitud a status 4
    $stmt = $conn->prepare("UPDATE solicitudes SET status_id = 4 WHERE id_solicitud = ?");
    $stmt->execute([$id_solicitud]);

    // 4. Obtener id_tarea para mensaje (opcional)
    $stmt = $conn->prepare("SELECT id_tarea FROM tareas WHERE id_solicitud = :id LIMIT 1");
    $stmt->execute([':id' => $id_solicitud]);
    $id_tarea = $stmt->fetchColumn();

    // 5. Notificación
    $noti = $conn->prepare("INSERT INTO notificaciones (id_perfil, tipo_notificacion, mensaje, id_status) VALUES (?, ?, ?, ?)");
    $noti->execute([
      2,
      'mantenimiento',
      "El mantenimiento con ID de tarea $id_tarea ha sido aprobado.",
      2
    ]);

    echo json_encode(['success' => true, 'mensaje' => 'Mantenimiento aprobado, solicitud en status 4 y notificación enviada.']);
    exit;

  } elseif ($tipo_solicitud === 2) {
    // --------------- //
    // TIPO 2: COMPRA  //
    // --------------- //

    // 2. Obtener tareas relacionadas
    $stmt = $conn->prepare("SELECT id_tarea FROM solicitudes_tareas WHERE id_solicitud = :id");
    $stmt->execute([':id' => $id_solicitud]);
    $tareas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!$tareas) {
      echo json_encode(['error' => 'No hay tareas asociadas']);
      exit;
    }

    // 3. Obtener ID compra
    $stmt = $conn->prepare("SELECT id_compra FROM compras WHERE id_solicitud = :id");
    $stmt->execute([':id' => $id_solicitud]);
    $compra = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$compra) {
      echo json_encode(['error' => 'No se encontró la compra']);
      exit;
    }

    $id_compra = $compra['id_compra'];

    // 4. Obtener insumos de la compra
    $herramientas = $conn->prepare("SELECT id_herramienta FROM compra_herramienta WHERE id_compra = ?");
    $herramientas->execute([$id_compra]);
    $herramientas = $herramientas->fetchAll(PDO::FETCH_COLUMN);

    $productos = $conn->prepare("SELECT id_producto FROM compra_producto WHERE id_compra = ?");
    $productos->execute([$id_compra]);
    $productos = $productos->fetchAll(PDO::FETCH_COLUMN);

    $repuestos = $conn->prepare("SELECT id_repuesto FROM compra_repuesto WHERE id_compra = ?");
    $repuestos->execute([$id_compra]);
    $repuestos = $repuestos->fetchAll(PDO::FETCH_COLUMN);

    // 5. Actualizar insumos en tareas SOLO si están en status 5
    foreach ($tareas as $tarea) {
      if (!empty($herramientas)) {
        $in = str_repeat('?,', count($herramientas) - 1) . '?';
        $sql = "UPDATE herramienta_tarea SET status_id = 25 WHERE tarea_id = ? AND herramienta_id IN ($in) AND status_id = 5";
        $stmt = $conn->prepare($sql);
        $stmt->execute(array_merge([$tarea], $herramientas));
      }

      if (!empty($productos)) {
        $in = str_repeat('?,', count($productos) - 1) . '?';
        $sql = "UPDATE producto_tarea SET status_id = 25 WHERE tarea_id = ? AND id_producto IN ($in) AND status_id = 5";
        $stmt = $conn->prepare($sql);
        $stmt->execute(array_merge([$tarea], $productos));
      }

      if (!empty($repuestos)) {
        $in = str_repeat('?,', count($repuestos) - 1) . '?';
        $sql = "UPDATE repuesto_tarea SET status_id = 25 WHERE tarea_id = ? AND repuesto_id IN ($in) AND status_id = 5";
        $stmt = $conn->prepare($sql);
        $stmt->execute(array_merge([$tarea], $repuestos));
      }
    }

    // 6. Cambiar solicitud a status 4
    $stmt = $conn->prepare("UPDATE solicitudes SET id_status = 4 WHERE id_solicitud = ?");
    $stmt->execute([$id_solicitud]);

    // 7. Notificación general por la compra
    $noti = $conn->prepare("INSERT INTO notificaciones (id_perfil, tipo_notificacion, mensaje, id_status) VALUES (?, ?, ?, ?)");
    $noti->execute([
      2,
      'compra',
      "La compra con insumos ha sido aprobada para la solicitud $id_solicitud.",
      2
    ]);

    echo json_encode(['success' => true, 'mensaje' => 'Compra procesada, solicitud a status 4 y notificación enviada.']);
    exit;

  } else {
    echo json_encode(['error' => 'Tipo de solicitud no manejado']);
    exit;
  }

} catch (Exception $e) {
  echo json_encode(['error' => 'Error interno: ' . $e->getMessage()]);
  exit;
}
