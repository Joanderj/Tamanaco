<?php
// Conexión a la base de datos
include("db_connection.php"); // tu archivo de conexión

$sql = "SELECT p.id_persona, p.primer_nombre, p.segundo_nombre, p.primer_apellido, p.segundo_apellido, p.correo_electronico
        FROM personas p
        JOIN usuarios u ON p.id_persona = u.id_persona
        WHERE u.id_perfil = 4 AND p.id_status = 1
        ORDER BY p.estado_id ASC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$personas = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $personas[] = [
        'id' => $row['id_persona'],
        'nombre' => trim($row['primer_nombre'] . ' ' . $row['segundo_nombre'] . ' ' . $row['primer_apellido'] . ' ' . $row['segundo_apellido']),
        'correo' => $row['correo_electronico']
    ];
}

echo json_encode($personas);
?>
