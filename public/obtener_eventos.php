<?php
header('Content-Type: application/json');
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
$conexion->set_charset("utf8");

if ($conexion->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Error de conexión"]);
    exit;
}

$sql = "
SELECT 
  t.id_tarea,
  t.titulo_tarea,
  t.fecha_inicio,
  t.fecha_fin,
  t.hora_inicio,
  t.hora_fin,
  t.descripcion_tarea,
  m.nombre_maquina,
  m.color
FROM tareas t
LEFT JOIN maquina_unica mu ON t.id_maquina_unica = mu.id_maquina_unica
LEFT JOIN maquina m ON mu.id_maquina = m.id_maquina
WHERE t.status_id != 3
";

$resultado = $conexion->query($sql);
$eventos = [];

while ($fila = $resultado->fetch_assoc()) {
    $eventos[] = [
        'id' => $fila['id_tarea'],
        'title' => $fila['titulo_tarea'],
        'start' => $fila['fecha_inicio'] . 'T' . ($fila['hora_inicio'] ?? '00:00:00'),
        'end' => $fila['fecha_fin'] . 'T' . ($fila['hora_fin'] ?? '23:59:59'),
        'backgroundColor' => $fila['color'] ?? '#4B5563',
        'borderColor' => $fila['color'] ?? '#374151',
        'textColor' => '#fff',
        'extendedProps' => [
            'descripcion' => $fila['descripcion_tarea'],
            'maquina' => $fila['nombre_maquina']
        ]
    ];
}

echo json_encode($eventos);
?>
