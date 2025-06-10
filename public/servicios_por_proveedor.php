<?php
if (isset($_POST['id_proveedor'])) {
    $idProveedor = $_POST['id_proveedor'];

    try {
        $pdo = new PDO("mysql:host=localhost;dbname=bd_tamanaco;charset=utf8", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "
            SELECT s.id_servicio, s.nombre_servicio, s.descripcion
            FROM proveedor_servicio ps
            INNER JOIN servicio s ON ps.id_servicio = s.id_servicio
            WHERE ps.id_proveedor = :id 
              AND ps.status_id IN (1)
              AND s.id_status = 1
            ORDER BY s.nombre_servicio ASC
        ";

        $stmt = $pdo->prepare($sql); // Retyped to remove potential invisible characters
        $stmt->execute(['id' => $idProveedor]);
        $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($servicios);
    } catch (PDOException $e) {
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
}
?>
