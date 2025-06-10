<?php
require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Conexión
$conexion = new PDO("mysql:host=localhost;dbname=bd_tamanaco", "root", "");
$conexion->exec("SET NAMES utf8");

// Parámetro
$id_tarea = $_GET['id_tarea'];

// Consulta detallada
$stmt = $conexion->prepare("
    SELECT t.*, tm.nombre_tipo AS tipo_mantenimiento, mu.CodigoUnico AS codigo_maquina,
           m.nombre_maquina, ma.nombre_marca, mo.nombre_modelo, s.nombre_sede AS ubicacion_sede,
           e.nombre_status AS estado_tarea, i.nivel AS prioridad,
           GROUP_CONCAT(CONCAT(p.primer_nombre, ' ', p.primer_apellido) SEPARATOR ', ') AS responsables_asignados
    FROM tareas t
    LEFT JOIN tipo_mantenimiento tm ON t.tipo_mantenimiento_id = tm.id_tipo
    LEFT JOIN maquina_unica mu ON t.id_maquina_unica = mu.id_maquina_unica
    LEFT JOIN maquina m ON mu.id_maquina = m.id_maquina
    LEFT JOIN marca ma ON m.id_marca = ma.id_marca
    LEFT JOIN modelo mo ON m.id_modelo = mo.id_modelo
    LEFT JOIN sede s ON mu.id_sede = s.id_sede
    LEFT JOIN status e ON t.status_id = e.id_status
    LEFT JOIN prioridad i ON t.id_importancia = i.id_importancia
    LEFT JOIN responsable r ON t.id_tarea = r.tarea_id
    LEFT JOIN personas p ON r.persona_id = p.id_persona
    WHERE t.id_tarea = :id_tarea
    GROUP BY t.id_tarea
");
$stmt->bindParam(':id_tarea', $id_tarea, PDO::PARAM_INT);
$stmt->execute();
$tarea = $stmt->fetch(PDO::FETCH_ASSOC);

// Crear Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Reporte Mantenimiento');

// Títulos
$sheet->setCellValue('A1', 'Campo');
$sheet->setCellValue('B1', 'Valor');
$sheet->getStyle('A1:B1')->getFont()->setBold(true);

// Datos
$datos = [
    'ID de Tarea' => $tarea['id_tarea'],
    'Título' => $tarea['titulo_tarea'],
    'Descripción' => strip_tags($tarea['descripcion_tarea']),
    'Tipo de Mantenimiento' => $tarea['tipo_mantenimiento'],
    'Prioridad' => $tarea['prioridad'],
    'Fecha Inicio' => $tarea['fecha_inicio'] . ' ' . $tarea['hora_inicio'],
    'Fecha Fin' => $tarea['fecha_hora_finalizacion'],
    'Código Único' => $tarea['codigo_maquina'],
    'Equipo' => $tarea['nombre_maquina'],
    'Marca' => $tarea['nombre_marca'],
    'Modelo' => $tarea['nombre_modelo'],
    'Ubicación' => $tarea['ubicacion_sede'],
    'Tiempo Programado (min)' => $tarea['tiempo_programado'],
    'Tiempo Real' => $tarea['tiempo_paro_programado'],
    'Estado' => $tarea['estado_tarea'],
    'Responsables' => $tarea['responsables_asignados'],
    'Observaciones' => $tarea['observacion']
];

// Insertar datos en el Excel
$fila = 2;
foreach ($datos as $campo => $valor) {
    $sheet->setCellValue("A{$fila}", $campo);
    $sheet->setCellValue("B{$fila}", $valor ?: 'N/A');
    $fila++;
}

// Estilos básicos
$sheet->getColumnDimension('A')->setWidth(35);
$sheet->getColumnDimension('B')->setWidth(70);
$sheet->getStyle("A1:B" . ($fila - 1))->getAlignment()->setWrapText(true);
$sheet->getStyle("A1:B" . ($fila - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

// Salida directa
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="constancia_mantenimiento.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
