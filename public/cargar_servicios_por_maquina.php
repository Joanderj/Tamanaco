<?php
header('Content-Type: application/json');

if (!isset($_POST['id_maquina_unica'])) {
    echo json_encode(['error' => 'Parámetro no recibido']);
    exit;
}

$idMaquinaUnica = intval($_POST['id_maquina_unica']);

try {
    $pdo = new PDO("mysql:host=localhost;dbname=bd_tamanaco;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Paso 1: Verificar que exista la máquina única
    $stmt = $pdo->prepare("SELECT id_maquina_unica FROM maquina_unica WHERE id_maquina_unica = ?");
    $stmt->execute([$idMaquinaUnica]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(['error' => 'Máquina única no encontrada']);
        exit;
    }

    // Paso 2: Servicios asignados directamente a la máquina (por id_maquina)
    $stmt1 = $pdo->prepare("
        SELECT DISTINCT s.id_servicio, s.nombre_servicio, s.descripcion, 'maquina' AS fuente
        FROM servicio s
        INNER JOIN servicio_maquina sm ON s.id_servicio = sm.id_servicio
        INNER JOIN maquina_unica mu ON sm.id_maquina = mu.id_maquina
        WHERE mu.id_maquina_unica = ? AND s.id_status = 1
    ");
    $stmt1->execute([$idMaquinaUnica]);
    $serviciosDirectos = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    // Paso 3: Servicios de piezas, si la pieza (repuesto) está asociada a esta máquina única
    $stmt2 = $pdo->prepare("
        SELECT DISTINCT s.id_servicio, s.nombre_servicio, s.descripcion, 'repuesto' AS fuente
        FROM servicio s
        INNER JOIN servicio_piezas sp ON s.id_servicio = sp.id_servicio
        WHERE s.id_status = 1 AND EXISTS (
            SELECT 1 FROM maquina_repuesto mr
            WHERE mr.id_maquina = ? 
              AND mr.id_repuesto = sp.id_repuesto
              AND mr.id_status = 1
        )
    ");
    $stmt2->execute([$idMaquinaUnica]); // Aquí usamos directamente id_maquina_unica porque es el FK real
    $serviciosRepuestos = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // Paso 4: Unificar servicios sin duplicados
    $serviciosUnicos = [];
    foreach (array_merge($serviciosDirectos, $serviciosRepuestos) as $s) {
        $serviciosUnicos[$s['id_servicio']] = $s;
    }

    echo json_encode(array_values($serviciosUnicos));

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
