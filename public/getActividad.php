<?php
require 'db_connection.php'; // Este archivo debe instanciar la conexión PDO en $conn

if (!isset($_GET['id_registro'])) {
    echo json_encode(['error' => 'ID no proporcionado']);
    exit;
}

$id = intval($_GET['id_registro']);

$sql = "SELECT 
            ra.id_registro,
            ra.accion,
            ra.actividad,
            ra.fecha,
            ra.modulo,
            ra.ip_address,
            ra.dispositivo,
            ra.estado,
            ra.importancia,
            u.usuario,
            p.primer_nombre,
            p.segundo_nombre,
            p.primer_apellido,
            p.segundo_apellido
        FROM registro_actividades ra
        INNER JOIN usuarios u ON ra.id_usuario = u.id_usuario
        INNER JOIN personas p ON u.id_persona = p.id_persona
        WHERE ra.id_registro = :id";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if ($data) {
    echo json_encode($data);
} else {
    echo json_encode(['error' => 'Actividad no encontrada']);
}
?>