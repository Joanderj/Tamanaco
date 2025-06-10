<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

// Comprobar conexión
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

// Obtener el id de la máquina
$id_maquina = isset($_GET['id_maquina']) ? intval($_GET['id_maquina']) : 0;

// Consultar los componentes asociados a la máquina
$query = "
    SELECT r.id_repuesto, r.nombre_repuesto 
    FROM maquina_repuesto mr 
    JOIN repuesto r ON mr.id_repuesto = r.id_repuesto 
    WHERE mr.id_maquina = ?
";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $id_maquina);
$stmt->execute();
$result = $stmt->get_result();

// Crear un array para almacenar los componentes
$componentes = [];
while ($row = $result->fetch_assoc()) {
    $componentes[] = [
        'id_repuesto' => htmlspecialchars($row["id_repuesto"]),
        'nombre_repuesto' => htmlspecialchars($row["nombre_repuesto"])
    ];
}

// Cerrar conexión
$stmt->close();
$conexion->close();

// Devolver los componentes en formato JSON
header('Content-Type: application/json');
echo json_encode($componentes);
?>
