<?php
// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bd_tamanaco";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener parámetros de la solicitud
$id_solicitud = isset($_GET['id_solicitud']) ? intval($_GET['id_solicitud']) : 0;
$id_maquina = isset($_GET['id_maquina']) ? intval($_GET['id_maquina']) : 0;

// Consulta SQL
$sql = "
    SELECT 
        so.id_tipo_solicitud, 
        so.fecha_solicitud, 
        so.fecha_recibido,
        ts.nombre_tipo, 
        hrr.*,
        ma.*,
        al.nombre as origen,
        des.nombre as destino,
        ca.*,
        mo.*
    FROM 
        solicitudes so
    JOIN 
        movimiento_maquina ma ON ma.id_solicitud = so.id_solicitud
    JOIN 
        tipos_solicitudes ts ON so.id_tipo_solicitud = ts.id_tipo_solicitud  
    JOIN maquina_unica un ON un.id_maquina_unica = ma.id_maquina
    JOIN 
        maquina hrr ON hrr.id_maquina = un.id_maquina
    JOIN almacen al ON al.id_almacen = ma.id_almacen_origen
    JOIN almacen des ON des.id_almacen = ma.id_almacen_destino
    JOIN marca ca ON ca.id_marca = hrr.id_marca
    JOIN modelo mo ON mo.id_modelo = hrr.id_modelo
    JOIN tipo tp ON tp.id_tipo = hrr.id_tipo
    WHERE 
        so.id_solicitud = $id_solicitud 
        AND un.id_maquina_unica = $id_maquina
";

// Ejecutar la consulta
$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Devolver los datos en formato JSON
header('Content-Type: application/json');
echo json_encode($data);

// Cerrar la conexión
$conn->close();
?>
