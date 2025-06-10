<?php
include 'db_connection.php';

$id = $_GET['id_solicitud'] ?? null;
if (!$id) {
  echo json_encode(['error' => 'Solicitud no especificada']);
  exit;
}

$stmt = $conn->prepare("SELECT * FROM compras WHERE id_solicitud = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($data ?: ['error' => 'No se encontraron datos de compra']);
