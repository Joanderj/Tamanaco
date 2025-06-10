<?php
require_once('lib/tcpdf/tcpdf.php');
include 'db_connection.php';

// Configuración inicial PDF
$pdf = new TCPDF();
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->AddPage();

// Encabezado gráfico
$pdf->Image('img/encabezado.png', 10, 10, 190, 25);
$pdf->SetDrawColor(0, 102, 204);
$pdf->SetLineWidth(1);
$pdf->Line(10, 37, 200, 37);
$pdf->Ln(32);

// Título
$pdf->SetFont('helvetica', 'B', 18);
$pdf->SetTextColor(0, 51, 102);
$pdf->Cell(0, 12, 'Informe Mensual de Mantenimiento', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 13);
$pdf->Cell(0, 8, 'Planta Tamanaco', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 11);
setlocale(LC_TIME, 'es_ES.UTF-8');
$mes = strftime('%B de %Y');
$pdf->Cell(0, 8, 'Mes: ' . ucfirst($mes), 0, 1, 'C');
$pdf->Ln(10);

// ----------- ESTADÍSTICAS DEL MES (con mysqli) ----------------
$mes_actual = date('Y-m');
$total_mes = $completadas_mes = $ejecucion_mes = $pendientes_mes = 0;
$preventivos_mes = $correctivos_mes = $porcentaje_completado = 0;

if ($conn) {
    $query = "
        SELECT tipo_mantenimiento_id, status_id, COUNT(*) AS total 
        FROM tareas 
        WHERE DATE_FORMAT(fecha_inicio, '%Y-%m') = :mes 
        GROUP BY tipo_mantenimiento_id, status_id
    ";
    $stmt = $conn->prepare($query);
    $stmt->execute([':mes' => $mes_actual]);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $row) {
        $tipo = $row['tipo_mantenimiento_id'];
        $status = $row['status_id'];
        $cantidad = $row['total'];

        $total_mes += $cantidad;
        if ($status == 7) $completadas_mes += $cantidad;
        if ($status == 5) $ejecucion_mes += $cantidad;
        if ($status == 1) $pendientes_mes += $cantidad;

        if ($tipo == 1) $preventivos_mes += $cantidad;
        if ($tipo == 2) $correctivos_mes += $cantidad;
    }

    if ($total_mes > 0) {
        $porcentaje_completado = round(($completadas_mes / $total_mes) * 100, 1);
    }
}

// Mostrar Estadísticas
$pdf->SetFont('helvetica', 'B', 13);
$pdf->SetFillColor(0, 102, 204);
$pdf->SetTextColor(255);
$pdf->Cell(0, 10, 'Estadísticas Generales del Mes', 0, 1, 'L', true);
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(40, 40, 40);
$pdf->SetFillColor(245, 245, 245);

$estadisticas = [
    "Completadas" => $completadas_mes,
    "En Ejecución" => $ejecucion_mes,
    "Pendientes" => $pendientes_mes,
    "Preventivos" => $preventivos_mes,
    "Correctivos" => $correctivos_mes
];

// Dibuja barras
$max_val = max(1, max($estadisticas));
$bar_width = 90;
$bar_height = 8;
$startX = 30;
$startY = $pdf->GetY() + 4;
$color_map = [
    "Completadas" => [46, 204, 113],
    "En Ejecución" => [241, 196, 15],
    "Pendientes" => [231, 76, 60],
    "Preventivos" => [52, 152, 219],
    "Correctivos" => [155, 89, 182]
];

foreach ($estadisticas as $label => $valor) {
    $pdf->SetXY($startX, $startY);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(40, $bar_height, $label, 0, 0, 'L', false);

    // Barra
    $bar_len = ($valor / $max_val) * $bar_width;
    $color = $color_map[$label];
    $pdf->SetFillColor($color[0], $color[1], $color[2]);
    $pdf->Rect($startX + 42, $startY, $bar_len, $bar_height, 'F');

    // Valor numérico
    $pdf->SetTextColor(40, 40, 40);
    $pdf->SetXY($startX + 42 + $bar_width + 4, $startY);
    $pdf->Cell(15, $bar_height, $valor, 0, 1, 'L', false);

    $startY += $bar_height + 3;
}
$pdf->Ln(10);
//
// -------------- CONSULTAR TAREAS DEL MES (usando PDO) ------------------
//
$stmt = $conn->prepare("SELECT * FROM tareas WHERE DATE_FORMAT(fecha_inicio, '%Y-%m') = :mes");
$stmt->execute([':mes' => $mes_actual]);
$tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recuento por tipo
$total = count($tareas);
$completadas = $pendientes = $tiempo_total = $costo_total = $preventivas = $correctivas = 0;
foreach ($tareas as $t) {
    if ($t['status_id'] == 7) $completadas++;
    elseif ($t['status_id'] == 1 || $t['status_id'] == 5) $pendientes++;

    $tiempo_total += floatval($t['tiempo_programado'] ?? 0);
    $costo_total += floatval($t['costo'] ?? 0);

    if ($t['tipo_mantenimiento_id'] == 1) $preventivas++;
    elseif ($t['tipo_mantenimiento_id'] == 2) $correctivas++;
}

// --------- RESUMEN EJECUTIVO ----------------
$pdf->SetFont('helvetica', 'B', 13);
$pdf->SetFillColor(220, 230, 241);
$pdf->SetTextColor(0, 51, 102);
$pdf->Cell(0, 11, 'Resumen Ejecutivo', 0, 1, 'L', true);
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(40, 40, 40);
$pdf->SetFillColor(255, 255, 255);
$pdf->MultiCell(0, 8,
    "• Total de tareas registradas: $total\n" .
    "• Tareas completadas: $completadas\n" .
    "• Tareas pendientes: $pendientes\n" .
    "• Tiempo total programado: $tiempo_total horas\n" .
    "• Costo total estimado: $" . number_format($costo_total, 2, ',', '.') . "\n" .
    "• Mantenimiento preventivo: $preventivas (" . round(($preventivas / max(1, $total)) * 100, 1) . "%)\n" .
    "• Mantenimiento correctivo: $correctivas (" . round(($correctivas / max(1, $total)) * 100, 1) . "%)\n" .
    "• Promedio duración por tarea: " . round($tiempo_total / max(1, $total), 2) . " horas\n" .
    "• Promedio costo por tarea: $" . number_format($costo_total / max(1, $total), 2, ',', '.'),
    0, 'L', true
);
$pdf->Ln(6);

// --------- TABLA DETALLE DE TAREAS ----------------
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetFillColor(0, 102, 204);
$pdf->SetTextColor(255);
$pdf->Cell(12, 9, 'ID', 1, 0, 'C', true);
$pdf->Cell(40, 9, 'Título', 1, 0, 'C', true);
$pdf->Cell(22, 9, 'Tipo', 1, 0, 'C', true);
$pdf->Cell(24, 9, 'Inicio', 1, 0, 'C', true);
$pdf->Cell(24, 9, 'Fin', 1, 0, 'C', true);
$pdf->Cell(26, 9, 'Estado', 1, 0, 'C', true);
$pdf->Cell(30, 9, 'Costo', 1, 1, 'C', true);

$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(40, 40, 40);
$fill = false;

foreach ($tareas as $t) {
    $tipo = $t['tipo_mantenimiento_id'] == 1 ? 'Preventivo' : 'Correctivo';
    if ($t['status_id'] == 7) $estado = 'Completada';
    elseif ($t['status_id'] == 5) $estado = 'En Ejecución';
    elseif ($t['status_id'] == 1) $estado = 'Pendiente';
    else $estado = 'Otro';

    $pdf->SetFillColor($fill ? 245 : 255, $fill ? 250 : 255, $fill ? 255 : 255);

    $pdf->Cell(12, 8, $t['id_tarea'], 1, 0, 'C', true);
    $pdf->Cell(40, 8, mb_strimwidth($t['titulo_tarea'], 0, 38, '...'), 1, 0, 'L', true);
    $pdf->Cell(22, 8, $tipo, 1, 0, 'C', true);
    $pdf->Cell(24, 8, date('d/m/Y', strtotime($t['fecha_inicio'])), 1, 0, 'C', true);
    $pdf->Cell(24, 8, (!empty($t['fecha_fin']) ? date('d/m/Y', strtotime($t['fecha_fin'])) : '-'), 1, 0, 'C', true);
    $pdf->Cell(26, 8, $estado, 1, 0, 'C', true);
    $pdf->Cell(30, 8, '$' . number_format($t['costo'] ?? 0, 2, ',', '.'), 1, 1, 'R', true);

    $fill = !$fill;
}
$pdf->Ln(6);

// --------- NOTA FINAL ----------------
$pdf->SetFont('helvetica', 'I', 10);
$pdf->SetTextColor(100, 100, 100);
$pdf->MultiCell(0, 6, "Este informe refleja las operaciones de mantenimiento realizadas en la planta Tamanaco durante el presente mes. Se recomienda revisar las tareas pendientes y evaluar los planes de mantenimiento en función de su frecuencia y costo.", 0, 'L');

// --------- PIE DE PÁGINA ----------------
$pdf->SetY(-30);
$pdf->SetFont('helvetica', 'I', 9);
$pdf->SetTextColor(120, 120, 120);
$pdf->Cell(0, 8, 'Reporte generado automáticamente por el Sistema de Mantenimiento de Planta Tamanaco', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(0, 6, 'Fecha de generación: ' . date('d/m/Y H:i'), 0, 1, 'C');
$pdf->SetDrawColor(0, 102, 204);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());

// Salida final
$pdf->Output('reporte_tareas_mensual.pdf', 'I');
?>
