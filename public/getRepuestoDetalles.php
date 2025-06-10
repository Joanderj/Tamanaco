<?php
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

if ($conexion->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Error de conexión: " . $conexion->connect_error]);
    exit();
}

$idRepuesto = intval($_GET['id_repuesto']);

$sqlRepuesto = "SELECT r.nombre_repuesto, r.date_created 
                FROM repuesto r 
                WHERE r.id_repuesto = ?";
$stmt = $conexion->prepare($sqlRepuesto);
$stmt->bind_param("i", $idRepuesto);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "Repuesto no encontrado"]);
    exit();
}

$repuesto = $result->fetch_assoc();

$sqlClasificaciones = "SELECT detalle_especificacion AS detalle, valor_especificacion AS valor 
                       FROM especificaciones_repuestos 
                       WHERE id_repuesto = ?";
$stmtClasificaciones = $conexion->prepare($sqlClasificaciones);
$stmtClasificaciones->bind_param("i", $idRepuesto);
$stmtClasificaciones->execute();
$resultClasificaciones = $stmtClasificaciones->get_result();

$clasificaciones = [];
while ($row = $resultClasificaciones->fetch_assoc()) {
    $clasificaciones[] = $row;
}

// Devolver datos como JSON
echo json_encode([
    "nombreRepuesto" => $repuesto["nombre_repuesto"],
    "fechaCreacion" => $repuesto["date_created"],
    "clasificaciones" => $clasificaciones,
]);

$stmt->close();
$stmtClasificaciones->close();
$conexion->close();
?>