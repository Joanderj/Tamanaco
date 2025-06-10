<?php
header('Content-Type: application/json');
require_once 'db_connection.php'; // Asegúrate de que $conn es una instancia válida de PDO

try {
    if (!isset($_GET['id_herramienta']) || empty($_GET['id_herramienta'])) {
        throw new Exception("ID de herramienta no especificado");
    }

    $idHerramienta = intval($_GET['id_herramienta']);

    // Obtener datos principales de la herramienta
    $stmt = $conn->prepare("SELECT nombre_herramienta, descripcion, nombre_imagen, date_created FROM herramientas WHERE id_herramienta = :id");
    $stmt->bindParam(':id', $idHerramienta, PDO::PARAM_INT);
    $stmt->execute();

    $herramienta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$herramienta) {
        throw new Exception("Herramienta no encontrada");
    }

    // Preparar respuesta
    $response = [
        'nombreHerramienta' => $herramienta['nombre_herramienta'],
        'descripcion' => $herramienta['descripcion'],
        'imagen' => $herramienta['nombre_imagen'], // puedes concatenar con la carpeta si es necesario
        'fechaCreacion' => $herramienta['date_created'],
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>
