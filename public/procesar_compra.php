<?php
header('Content-Type: application/json');
require_once 'db_connection.php';

if (!function_exists('conectarBD')) {
    function conectarBD() {
        $host = 'localhost';
        $dbname = 'bd_tamanaco';
        $username = 'root';
        $password = '';
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            return false;
        }
    }
}

$pdo = conectarBD();
if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'No se pudo conectar a la base de datos.']);
    exit;
}

function asegurarCodigoExiste($pdo, $codigo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM codigo WHERE codigo = ?");
    $stmt->execute([$codigo]);
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO codigo (codigo, fecha_creacion) VALUES (?, NOW())");
        $stmt->execute([$codigo]);
    }
}

try {
    $pdo->beginTransaction();

    $id_usuario = intval($_POST['id_usuario'] ?? 0);
    $id_tarea = intval($_POST['id_tarea'] ?? 0);
    if (!$id_usuario || !$id_tarea) throw new Exception("ID de usuario o tarea no válido.");

    $proveedores_data = [];

    foreach ($_POST['nombre_herramienta'] ?? [] as $i => $nombre) {
        $id_proveedor = intval($_POST['proveedor_herramienta'][$i] ?? 0);
        $precio = floatval($_POST['precio_herramienta'][$i] ?? 0);
        $cantidad = intval($_POST['cantidad_herramienta'][$i] ?? 0);
        if ($id_proveedor && $precio > 0 && $cantidad > 0) {
            $proveedores_data[$id_proveedor]['herramientas'][] = [
                'nombre' => $nombre,
                'precio' => $precio,
                'cantidad' => $cantidad
            ];
        }
    }

    foreach ($_POST['nombre_repuesto'] ?? [] as $i => $nombre) {
        $id_proveedor = intval($_POST['proveedor_repuesto'][$i] ?? 0);
        $precio = floatval($_POST['precio_repuesto'][$i] ?? 0);
        $cantidad = intval($_POST['cantidad_repuesto'][$i] ?? 0);
        if ($id_proveedor && $precio > 0 && $cantidad > 0) {
            $proveedores_data[$id_proveedor]['repuestos'][] = [
                'nombre' => $nombre,
                'precio' => $precio,
                'cantidad' => $cantidad
            ];
        }
    }

    foreach ($_POST['nombre_producto'] ?? [] as $i => $nombre) {
        $id_proveedor = intval($_POST['proveedor_producto'][$i] ?? 0);
        $precio = floatval($_POST['precio_producto'][$i] ?? 0);
        $cantidad = intval($_POST['cantidad_producto'][$i] ?? 0);
        if ($id_proveedor && $precio > 0 && $cantidad > 0) {
            $proveedores_data[$id_proveedor]['productos'][] = [
                'nombre' => $nombre,
                'precio' => $precio,
                'cantidad' => $cantidad
            ];
        }
    }

    if (empty($proveedores_data)) throw new Exception("No se encontraron ítems válidos con proveedor y precio.");

    foreach ($proveedores_data as $id_proveedor => $datos) {
        $stmt = $pdo->query("SELECT MAX(id) + 1 AS next_id FROM codigo");
        $codigo_id = $stmt->fetchColumn() ?? 1;
        $codigo_compra = "COMP-" . str_pad($codigo_id, 3, "0", STR_PAD_LEFT);

        asegurarCodigoExiste($pdo, $codigo_compra);

        $stmt = $pdo->prepare("INSERT INTO solicitudes (id_tipo_solicitud, id_usuario, fecha_solicitud, id_status, id_perfil)
                               VALUES (2, ?, NOW(), 26, 3)");
        $stmt->execute([$id_usuario]);
        $id_solicitud = $pdo->lastInsertId();

        $pdo->prepare("INSERT INTO solicitudes_tareas (id_solicitud, id_tarea) VALUES (?, ?)")
            ->execute([$id_solicitud, $id_tarea]);

        $total_productos = 0;
        $total_precio = 0;

        foreach (['herramientas', 'repuestos', 'productos'] as $tipo) {
            if (!isset($datos[$tipo])) continue;
            foreach ($datos[$tipo] as $item) {
                $total_productos++;
                $total_precio += $item['precio'] * $item['cantidad'];
            }
        }

        $stmt = $pdo->prepare("INSERT INTO compras (codigo_compra, id_solicitud, id_proveedor, id_usuario_solicitante, id_status, total_productos, total_precio, fecha_compra)
                               VALUES (?, ?, ?, ?, 1, ?, ?, NOW())");
        $stmt->execute([$codigo_compra, $id_solicitud, $id_proveedor, $id_usuario, $total_productos, $total_precio]);
        $id_compra = $pdo->lastInsertId();

        foreach ($datos['herramientas'] ?? [] as $i => $herr) {
            $stmt = $pdo->prepare("SELECT id_herramienta FROM herramientas WHERE nombre_herramienta = ? LIMIT 1");
            $stmt->execute([$herr['nombre']]);
            $id_herr = $stmt->fetchColumn();
            if (!$id_herr) throw new Exception("Herramienta '{$herr['nombre']}' no encontrada.");
            $codigo = "HERR-" . str_pad($id_herr, 3, "0", STR_PAD_LEFT);
            asegurarCodigoExiste($pdo, $codigo);

            $pdo->prepare("INSERT INTO compra_herramienta (codigo_herramienta, id_compra, id_herramienta, cantidad_total, cantidad_individual, precio_unitario, precio_total)
                           VALUES (?, ?, ?, ?, ?, ?, ?)")
                ->execute([$codigo, $id_compra, $id_herr, $herr['cantidad'], $herr['cantidad'], $herr['precio'], $herr['cantidad'] * $herr['precio']]);
        }

        foreach ($datos['repuestos'] ?? [] as $i => $rep) {
            $stmt = $pdo->prepare("SELECT id_repuesto FROM repuesto WHERE nombre_repuesto = ? LIMIT 1");
            $stmt->execute([$rep['nombre']]);
            $id_rep = $stmt->fetchColumn();
            if (!$id_rep) throw new Exception("Repuesto '{$rep['nombre']}' no encontrado.");
            $codigo = "REP-" . str_pad($id_rep, 3, "0", STR_PAD_LEFT);
            asegurarCodigoExiste($pdo, $codigo);

            $pdo->prepare("INSERT INTO compra_repuesto (codigo_repuesto, id_compra, id_repuesto, cantidad_total, cantidad_individual, precio_unitario, precio_total)
                           VALUES (?, ?, ?, ?, ?, ?, ?)")
                ->execute([$codigo, $id_compra, $id_rep, $rep['cantidad'], $rep['cantidad'], $rep['precio'], $rep['cantidad'] * $rep['precio']]);
        }

        foreach ($datos['productos'] ?? [] as $i => $prod) {
            $stmt = $pdo->prepare("SELECT id_producto FROM producto WHERE nombre_producto = ? LIMIT 1");
            $stmt->execute([$prod['nombre']]);
            $id_prod = $stmt->fetchColumn();
            if (!$id_prod) throw new Exception("Producto '{$prod['nombre']}' no encontrado.");
            $codigo = "PROD-" . str_pad($id_prod, 3, "0", STR_PAD_LEFT);
            asegurarCodigoExiste($pdo, $codigo);

            $pdo->prepare("INSERT INTO compra_producto (codigo_producto, id_compra, id_producto, cantidad_total, cantidad_individual, precio_unitario, precio_total)
                           VALUES (?, ?, ?, ?, ?, ?, ?)")
                ->execute([$codigo, $id_compra, $id_prod, $prod['cantidad'], $prod['cantidad'], $prod['precio'], $prod['cantidad'] * $prod['precio']]);
        }
    }

    $tablas = ['herramienta_tarea', 'repuesto_tarea', 'producto_tarea'];
    foreach ($tablas as $tabla) {
        $pdo->prepare("UPDATE $tabla SET status_id = 5 WHERE tarea_id = ? AND status_id = 26")
            ->execute([$id_tarea]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Compras generadas correctamente por proveedor.']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
