<?php
require_once('lib/tcpdf/tcpdf.php');
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
$conexion->set_charset("utf8");

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

$id_plan = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Obtener el plan
$plan_sql = "SELECT * FROM planes WHERE id_plan = $id_plan";
$plan_result = $conexion->query($plan_sql);
$plan = $plan_result->fetch_assoc();

// Obtener tareas ejecutadas y su info
$ejecuciones_sql = "
    SELECT 
        pe.*, 
        t.*, 
        tm.nombre_tipo AS tipo_mantenimiento, 
        mu.CodigoUnico AS codigo_maquina,
        m.nombre_maquina, 
        m.nombre_imagen AS imagen_maquina, 
        m.url AS url_maquina,
        ma.nombre_marca, 
        mo.nombre_modelo, 
        s.nombre_sede AS ubicacion_sede,
        e.nombre_status AS estado_tarea, 
        i.nivel AS prioridad,
        GROUP_CONCAT(CONCAT(p.primer_nombre, ' ', p.primer_apellido) SEPARATOR ', ') AS responsables_asignados
    FROM plan_ejecuciones pe
    JOIN tareas t ON pe.id_tarea = t.id_tarea
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
    WHERE pe.id_plan = $id_plan
    GROUP BY t.id_tarea, pe.id_ejecucion
";
$ejecuciones_result = $conexion->query($ejecuciones_sql);

// Obtener repuestos usados por tarea ejecutada
$repuestos_por_tarea = [];
if ($ejecuciones_result && $ejecuciones_result->num_rows > 0) {
    // Recolectar todos los id_tarea ejecutados
    $tarea_ids = [];
    $ejecuciones_result->data_seek(0);
    while ($row = $ejecuciones_result->fetch_assoc()) {
        $tarea_ids[] = intval($row['id_tarea']);
    }
    // Volver a poner el puntero al inicio para el bucle principal
    $ejecuciones_result->data_seek(0);

    if (count($tarea_ids) > 0) {
        $ids_str = implode(',', $tarea_ids);
        $sql_repuestos = "
            SELECT rt.*, r.nombre_repuesto, r.url AS url_repuesto, r.nombre_imagen AS imagen_repuesto
            FROM repuesto_tarea rt
            JOIN repuesto r ON rt.repuesto_id = r.id_repuesto
            WHERE rt.tarea_id IN ($ids_str)
        ";
        $res_repuestos = $conexion->query($sql_repuestos);
        if ($res_repuestos) {
            while ($rep = $res_repuestos->fetch_assoc()) {
                $tid = $rep['tarea_id'];
                if (!isset($repuestos_por_tarea[$tid])) $repuestos_por_tarea[$tid] = [];
                $repuestos_por_tarea[$tid][] = $rep;
            }
        }
    }
    // Dejar el puntero al inicio para el bucle principal
    $ejecuciones_result->data_seek(0);
}

// Crear PDF
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistema TAMANACO');
$pdf->SetTitle('Reporte de Plan');
$pdf->SetMargins(15, 20, 15);
$pdf->AddPage();
// Imagen de encabezado (arriba de todo)
$pdf->Image('img/encabezado.png', 10, 5, 190, 25);
$pdf->SetY(32); // Ajusta la posición Y para el siguiente contenido
// Membrete
$pdf->SetFont('helvetica', 'B', 18);
$pdf->Cell(0, 10, 'SISTEMA DE MANTENIMIENTO TAMANACO', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(0, 6, 'Reporte Detallado del Plan de Mantenimiento', 0, 1, 'C');
$pdf->Ln(8);

// Información del plan - Estilo profesional
$pdf->SetFont('helvetica', 'B', 13);
$pdf->SetTextColor(40, 75, 150);
$pdf->Cell(0, 8, 'INFORMACIÓN DEL PLAN', 0, 1, 'L');
$pdf->SetDrawColor(40, 75, 150);
$pdf->SetLineWidth(0.5);
$pdf->Line($pdf->GetX(), $pdf->GetY(), $pdf->GetPageWidth() - 15, $pdf->GetY());
$pdf->Ln(3);

$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(0, 0, 0);

$info = [
    ['Nombre del Plan', $plan['nombre_plan']],
    ['Descripción', strip_tags($plan['descripcion_plan'])],
    ['Frecuencia', $plan['frecuencia'] . ' ' . ucfirst($plan['tipo_frecuencia'])],
    ['Fecha de Asociación', $plan['fecha_asociacion']],
    ['Costo Aproximado', '$' . number_format($plan['costo_aprox'], 2)],
    ['Duración Estimada', $plan['duracion'] . ' horas']
];

foreach ($info as $row) {
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(50, 7, $row[0] . ':', 0, 0, 'L', false);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->MultiCell(0, 7, $row[1], 0, 'L', false, 1);
}
$pdf->Ln(6);
$status_counts = [
    'Activo' => 0,
    'En Progreso' => 0,
    'Finalizado' => 0
];

// Contar estados de las tareas ejecutadas
if ($ejecuciones_result && $ejecuciones_result->num_rows > 0) {
    $ejecuciones_result->data_seek(0);
    while ($row = $ejecuciones_result->fetch_assoc()) {
        $estado = strtolower($row['estado_tarea']);
        if (strpos($estado, 'activo') !== false || strpos($estado, 'activa') !== false) {
            $status_counts['Activo']++;
        } elseif (strpos($estado, 'progreso') !== false) {
            $status_counts['En Progreso']++;
        } elseif (strpos($estado, 'finaliz') !== false || strpos($estado, 'complet') !== false) {
            $status_counts['Finalizado']++;
        }
    }
    $ejecuciones_result->data_seek(0);
}

// Crear imagen de gráfico pastel (más grande)
$width = 180;
$height = 180;
$image = imagecreatetruecolor($width, $height);
imagesavealpha($image, true);
$transp = imagecolorallocatealpha($image, 255, 255, 255, 127);
imagefill($image, 0, 0, $transp);

$colors = [
    imagecolorallocate($image, 40, 75, 150),      // Activo (azul)
    imagecolorallocate($image, 255, 180, 60),     // En Progreso (naranja)
    imagecolorallocate($image, 60, 180, 90)       // Finalizado (verde)
];

$total = array_sum($status_counts);
if ($total > 0) {
    $start = 0;
    $i = 0;
    foreach ($status_counts as $label => $count) {
        if ($count > 0) {
            $angle = ($count / $total) * 360;
            imagefilledarc($image, $width/2, $height/2, $width-16, $height-16, $start, $start+$angle, $colors[$i], IMG_ARC_PIE);
            $start += $angle;
        }
        $i++;
    }
}

// Guardar imagen temporal
$tmp_chart = tempnam(sys_get_temp_dir(), 'chart') . '.png';
imagepng($image, $tmp_chart);
imagedestroy($image);

// Insertar gráfico en PDF (centrado y grande)
$pdf->SetFont('helvetica', 'B', 13);
$pdf->SetTextColor(40, 75, 150);
$pdf->Cell(0, 10, 'Estado de Tareas (Gráfica)', 0, 1, 'C');
$pdf->Ln(2);

// Calcular posición X centrada
$margins = $pdf->getMargins();
$pageWidth = $pdf->GetPageWidth() - $margins['left'] - $margins['right'];
$imgWidth = 90;
$imgHeight = 90;
$imgX = $margins['left'] + ($pageWidth - $imgWidth) / 2;
$imgY = $pdf->GetY();
$pdf->Image($tmp_chart, $imgX, $imgY, $imgWidth, $imgHeight, 'PNG');

// Leyenda centrada debajo de la gráfica
$pdf->SetY($imgY + $imgHeight + 6);
$pdf->SetFont('helvetica', '', 11);
$legend_labels = array_keys($status_counts);
$legend_colors = [
    [40, 75, 150],
    [255, 180, 60],
    [60, 180, 90]
];
$legendCellW = 40;
$totalLegendW = count($legend_labels) * $legendCellW;
$legendStartX = $margins['left'] + ($pageWidth - $totalLegendW) / 2;

for ($i = 0; $i < count($legend_labels); $i++) {
    $pdf->SetXY($legendStartX + $i * $legendCellW, $pdf->GetY());
    $pdf->SetFillColor($legend_colors[$i][0], $legend_colors[$i][1], $legend_colors[$i][2]);
    $pdf->Cell(8, 8, '', 0, 0, '', true);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell($legendCellW - 10, 8, $legend_labels[$i] . " ({$status_counts[$legend_labels[$i]]})", 0, 0, 'L', false);
}
$pdf->Ln(14);

// Eliminar imagen temporal
@unlink($tmp_chart);
// Sección de ejecuciones - Diseño profesional y detallado
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(40, 75, 150);
$pdf->Cell(0, 8, 'HISTORIAL DE TAREAS EJECUTADAS', 0, 1, 'L');
$pdf->SetDrawColor(40, 75, 150);
$pdf->SetLineWidth(0.4);
$pdf->Line($pdf->GetX(), $pdf->GetY(), $pdf->GetPageWidth() - 15, $pdf->GetY());
$pdf->Ln(4);

$pdf->SetTextColor(0, 0, 0);

if ($ejecuciones_result->num_rows > 0) {
    while ($fila = $ejecuciones_result->fetch_assoc()) {
        // Tarjeta visual para cada tarea
        $pdf->SetFillColor(245, 247, 255);
        $pdf->SetDrawColor(200, 210, 240);
        $pdf->SetLineWidth(0.2);
        $pdf->Cell(0, 1, '', 0, 1); // Espacio superior

        // Título de la tarea
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetFillColor(230, 235, 250);
        $pdf->Cell(0, 8, "• " . $fila['titulo_tarea'], 0, 1, 'L', true);

        // Información de la máquina asociada (estilo profesional mejorado)
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(40, 75, 150);
        $pdf->Cell(0, 7, "Máquina Asociada", 0, 1, 'L', false);

        // Tarjeta visual con fondo suave y borde
        $boxX = 18;
        $boxY = $pdf->GetY();
        $boxW = $pdf->GetPageWidth() - 36;
        $boxH = 32;
        $pdf->SetFillColor(245, 248, 255);
        $pdf->SetDrawColor(180, 200, 240);
        $pdf->SetLineWidth(0.4);
        $pdf->Rect($boxX, $boxY, $boxW, $boxH, 'DF');

        // Imagen de la máquina (izquierda)
        $imgX = $boxX + 2;
        $imgY = $boxY + 2;
        $imgW = 28;
        $imgH = $boxH - 4;
        if (!empty($fila['url_maquina']) && file_exists($fila['url_maquina'])) {
            $pdf->SetDrawColor(200, 200, 200);
            $pdf->Rect($imgX, $imgY, $imgW, $imgH, 'D');
            $pdf->Image($fila['url_maquina'], $imgX + 1, $imgY + 1, $imgW - 2, $imgH - 2, '', '', '', false, 300, '', false, false, 0, false, false, false);
        } else {
            $pdf->SetDrawColor(220, 220, 220);
            $pdf->Rect($imgX, $imgY, $imgW, $imgH, 'D');
            $pdf->SetFont('helvetica', 'I', 9);
            $pdf->SetTextColor(180, 180, 180);
            $pdf->SetXY($imgX, $imgY + ($imgH / 2) - 4);
            $pdf->Cell($imgW, 8, 'Sin imagen', 0, 0, 'C');
        }

        // Información textual (derecha de la imagen)
        $infoX = $imgX + $imgW + 4;
        $infoY = $imgY;
        $pdf->SetXY($infoX, $infoY);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(30, 30, 30);

        $maquina_info = [];
        $maquina_info[] = ['Nombre', $fila['nombre_maquina'] ?: 'No especificada'];
        if ($fila['codigo_maquina']) $maquina_info[] = ['Código', $fila['codigo_maquina']];
        if ($fila['nombre_marca']) $maquina_info[] = ['Marca', $fila['nombre_marca']];
        if ($fila['nombre_modelo']) $maquina_info[] = ['Modelo', $fila['nombre_modelo']];
        if ($fila['ubicacion_sede']) $maquina_info[] = ['Ubicación', $fila['ubicacion_sede']];

        // Guardar la posición Y inicial para el texto
        $textY = $infoY;
        foreach ($maquina_info as $info) {
            $pdf->SetXY($infoX, $textY);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(22, 6, $info[0] . ':', 0, 0, 'L', false);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 6, $info[1], 0, 1, 'L', false);
            $textY += 6;
        }

        // Ajustar Y para continuar después de la tarjeta
        $pdf->SetY($boxY + $boxH + 2);

        // Detalles de la tarea - Diseño limpio y profesional
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $detalles = [
            ['Descripción', strip_tags($fila['descripcion_tarea'])],
            ['Tipo de Mantenimiento', $fila['tipo_mantenimiento']],
            ['Prioridad', $fila['prioridad']],
            ['Estado', $fila['estado_tarea']],
            ['Responsables', $fila['responsables_asignados'] ?: 'No asignados'],
            ['Fecha de Inicio', $fila['fecha_inicio']],
            ['Fecha de Fin', $fila['fecha_fin']],
            ['Fecha de Ejecución', $fila['fecha_ejecucion']],
            ['Observación', $fila['observacion'] ?: 'N/A'],
            ['Costo Real', isset($fila['costo_real']) ? '$' . number_format($fila['costo_real'], 2) : 'N/A'],
            ['Duración Real', isset($fila['tiempo_paro_programado']) ? $fila['tiempo_paro_programado'] . ' ' : 'N/A']
        ];
        // Tarjeta de detalles con fondo suave
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetDrawColor(230, 235, 245);
        $pdf->SetLineWidth(0.2);
        $pdf->SetX(20);
        $startY = $pdf->GetY();
        $pdf->MultiCell($pdf->GetPageWidth() - 40, 1, '', 0, 'L', true, 1);
        foreach ($detalles as $d) {
            $pdf->SetX(22);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(45, 6, $d[0] . ':', 0, 0, 'L', false);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->MultiCell(0, 6, $d[1], 0, 'L', false, 1);
        }
        $pdf->Ln(2);

        // Repuestos usados en la tarea
        $tid = $fila['id_tarea'];
        if (isset($repuestos_por_tarea[$tid]) && count($repuestos_por_tarea[$tid]) > 0) {
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetTextColor(40, 75, 150);
            $pdf->Cell(0, 7, 'Repuestos Utilizados:', 0, 1, 'L');
            $pdf->SetTextColor(0, 0, 0);

            foreach ($repuestos_por_tarea[$tid] as $rep) {
            // Tarjeta de repuesto
            $repBoxX = 28;
            $repBoxY = $pdf->GetY();
            $repBoxW = $pdf->GetPageWidth() - 56;
            $repBoxH = 18;
            $pdf->SetFillColor(248, 250, 255);
            $pdf->SetDrawColor(210, 220, 240);
            $pdf->SetLineWidth(0.2);
            $pdf->Rect($repBoxX, $repBoxY, $repBoxW, $repBoxH, 'DF');

            // Imagen del repuesto
            $imgRepX = $repBoxX + 2;
            $imgRepY = $repBoxY + 2;
            $imgRepW = 14;
            $imgRepH = $repBoxH - 4;
            if (!empty($rep['url_repuesto']) && file_exists($rep['url_repuesto'])) {
                $pdf->SetDrawColor(220, 220, 220);
                $pdf->Rect($imgRepX, $imgRepY, $imgRepW, $imgRepH, 'D');
                $pdf->Image($rep['url_repuesto'], $imgRepX + 1, $imgRepY + 1, $imgRepW - 2, $imgRepH - 2, '', '', '', false, 300, '', false, false, 0, false, false, false);
            } else {
                $pdf->SetDrawColor(230, 230, 230);
                $pdf->Rect($imgRepX, $imgRepY, $imgRepW, $imgRepH, 'D');
                $pdf->SetFont('helvetica', 'I', 8);
                $pdf->SetTextColor(180, 180, 180);
                $pdf->SetXY($imgRepX, $imgRepY + ($imgRepH / 2) - 3);
                $pdf->Cell($imgRepW, 6, 'Sin imagen', 0, 0, 'C');
            }

            // Info textual del repuesto
            $pdf->SetXY($imgRepX + $imgRepW + 4, $imgRepY);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetTextColor(30, 30, 30);
            $pdf->Cell(40, 6, $rep['nombre_repuesto'], 0, 1, 'L', false);
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetTextColor(70, 70, 70);
            $pdf->SetX($imgRepX + $imgRepW + 4);
            $pdf->Cell(30, 6, 'Cantidad: ', 0, 0, 'L', false);
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Cell(0, 6, $rep['cantidad'], 0, 1, 'L', false);

            $pdf->Ln(2);
            }
        } else {
            $pdf->SetFont('helvetica', 'I', 9);
            $pdf->SetTextColor(120, 120, 120);
            $pdf->SetX(22);
            $pdf->Cell(0, 6, 'No se usaron repuestos en esta tarea.', 0, 1, 'L');
        }

        $pdf->Ln(2);
        // Línea divisoria entre tareas
        $pdf->SetDrawColor(220, 220, 220);
        $pdf->SetLineWidth(0.1);
        $pdf->Line($pdf->GetX(), $pdf->GetY(), $pdf->GetPageWidth() - 15, $pdf->GetY());
        $pdf->Ln(4);
    }
}

// Footer
$pdf->SetY(-25);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->Cell(0, 10, 'Reporte generado automáticamente por el Sistema TAMANACO', 0, 0, 'C');

// Salida
$pdf->Output('plan_'.$id_plan.'.pdf', 'I');
?>
 