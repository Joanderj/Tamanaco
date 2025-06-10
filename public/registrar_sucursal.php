<?php
// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "bd_tamanaco");

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener datos del formulario
$sucursal = $_POST['nombre'];
$pais = $_POST['pais'];
$estado = $_POST['estado'];
$direccion = $_POST['direccion'];
$sede = !empty($_POST['sede']) ? $_POST['sede'] : null;
$id_status = 1; // ID de status fijo

// Insertar datos en la tabla sucursal
$sql = "INSERT INTO sucursal (nombre, id_status, pais_id_pais,estado_id_estado,direccion)
        VALUES (?, ?, ?, ?, ? )";
$stmt = $conn->prepare($sql);
$stmt->bind_param("siiis", $sucursal, $id_status, $pais,$estado,$direccion);

$sucursal_id = $conn->insert_id;

if ($stmt->execute()) {
    echo "Sucursal registrada exitosamente.";
} else {
    echo "Error: " . $stmt->error;
}

// Cerrar conexión
$stmt->close();
$conn->close();
?>