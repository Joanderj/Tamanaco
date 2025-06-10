<?php
header('Content-Type: application/json');
require_once 'db_connection.php'; // Asegúrate de que `$conn` es una instancia de PDO

$id = intval($_GET['id_persona'] ?? 0);

// Validar ID antes de ejecutar la consulta
if ($id <= 0) {
    echo json_encode(['error' => 'ID no válido']);
    exit;
}

// Consulta optimizada con PDO
$sql = "SELECT 
            p.*, 
            c.nombre_cargo, 
            s.nombre_status,
            pais.paisnombre AS pais,
            estado.estadonombre AS estado
        FROM personas p
        LEFT JOIN cargo c ON p.id_cargo = c.id_cargo
        LEFT JOIN status s ON p.id_status = s.id_status
        LEFT JOIN pais pais ON p.pais_id = pais.id
        LEFT JOIN estado estado ON p.estado_id = estado.id
        WHERE p.id_persona = :id";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$data = $stmt->fetch(PDO::FETCH_ASSOC);

// Respuesta JSON segura
echo json_encode($data ?: ['error' => 'No encontrado']);
?>