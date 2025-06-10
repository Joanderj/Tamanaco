<?php
header('Content-Type: application/json');

try {
    include 'db_connection.php';

    $nombre = $_POST['nombre_proveedor'];
    $telefono = $_POST['telefono'] ?? '';
    $email = $_POST['email'] ?? '';
    $direccion = $_POST['direccion'] ?? '';

    if (empty($nombre)) {
        echo json_encode(["success" => false, "error" => "El nombre del proveedor es obligatorio."]);
        exit;
    }

    $query = "INSERT INTO proveedor (nombre_proveedor, telefono, email, direccion, id_status, date_created)
              VALUES (?, ?, ?, ?, 1, NOW())";

    $stmt = $conn->prepare($query);
    $success = $stmt->execute([$nombre, $telefono, $email, $direccion]);

    echo json_encode(["success" => $success]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>
