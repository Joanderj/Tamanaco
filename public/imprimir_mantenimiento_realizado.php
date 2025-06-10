<?php
require_once('lib/tcpdf/tcpdf.php');

// Obtener datos
$id_tarea = $_GET['id'] ?? 54;
$conexion = new PDO("mysql:host=localhost;dbname=bd_tamanaco", "root", "");
$conexion->exec("SET NAMES utf8");

$consulta = $conexion->prepare("
    SELECT t.*, tm.nombre_tipo AS tipo_mantenimiento, mu.CodigoUnico AS codigo_maquina,
           m.nombre_maquina, m.nombre_imagen AS imagen_maquina, m.url AS url_maquina,
           ma.nombre_marca, mo.nombre_modelo, s.nombre_sede AS ubicacion_sede,
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
$consulta->bindParam(':id_tarea', $id_tarea, PDO::PARAM_INT);
$consulta->execute();
$tarea = $consulta->fetch(PDO::FETCH_ASSOC);
// Crear PDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
$pdf->SetCreator('TAMANACO INDUSTRIAL');
$pdf->SetAuthor('Sistema de Mantenimiento');
$pdf->SetTitle('Constancia de Mantenimiento');
$pdf->SetMargins(20, 20, 20);
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddPage();

// ------------------ ENCABEZADO MODERNO ------------------
$pdf->Image('img/encabezado.png', 10, 10, 190, 25);
$pdf->SetDrawColor(0, 102, 204);
$pdf->SetLineWidth(1);
$pdf->Ln(32);
$pdf->SetY(40);
$pdf->SetFont('helvetica', 'B', 18);
$pdf->SetTextColor(0, 51, 102);
$pdf->Cell(0, 10, 'CONSTANCIA DE MANTENIMIENTO', 0, 1, 'C', false, '', 1);
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(40, 40, 40);
$pdf->Cell(0, 7, 'TAMANACO INDUSTRIAL, C.A.', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell(0, 6, 'Fecha de emisión: ' . date('d/m/Y H:i'), 0, 1, 'C');
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(0, 102, 204);
$pdf->Cell(0, 8, 'Comprobante Nº: MNT-' . str_pad($tarea['id_tarea'], 6, '0', STR_PAD_LEFT), 0, 1, 'C');
$pdf->SetDrawColor(0, 102, 204);
$pdf->SetLineWidth(0.7);
$pdf->Ln(2);
$pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
$pdf->Ln(10);
$pdf->SetTextColor(0, 0, 0);


// ------------------ DATOS Y IMAGEN JUNTOS ------------------
// --- DATOS Y IMAGEN EN DISEÑO PROFESIONAL ---

// Definir posición inicial
$startY = $pdf->GetY();
$cellHeight = 38;

// Imagen de la máquina (si existe)
if (!empty($tarea['url_maquina']) && file_exists($tarea['url_maquina'])) {
    $pdf->SetXY(20, $startY);
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->SetLineWidth(0.2);
    // Marco para la imagen
    $pdf->Rect(20, $startY, 50, $cellHeight, 'D');
    $pdf->Image($tarea['url_maquina'], 22, $startY + 2, 46, $cellHeight - 4, '', '', '', false, 300, '', false, false, 0, false, false, false);
} else {
    // Marco vacío si no hay imagen
    $pdf->SetXY(20, $startY);
    $pdf->SetDrawColor(220, 220, 220);
    $pdf->SetLineWidth(0.2);
    $pdf->Rect(20, $startY, 50, $cellHeight, 'D');
    $pdf->SetFont('helvetica', 'I', 9);
    $pdf->SetTextColor(180, 180, 180);
    $pdf->SetXY(20, $startY + 15);
    $pdf->Cell(50, 8, 'Sin imagen', 0, 0, 'C');
}

// Datos principales en fondo suave
$pdf->SetXY(75, $startY);
$pdf->SetFillColor(245, 250, 255);
$pdf->SetDrawColor(200, 200, 200);
$pdf->SetLineWidth(0.2);
$pdf->MultiCell(
    105, $cellHeight,
    "Código de Tarea: " . 'T-' . str_pad($tarea['id_tarea'], 4, '0', STR_PAD_LEFT) . "\n" .
    "Nombre de la Tarea: " . htmlspecialchars($tarea['titulo_tarea']) . "\n" .
    "Tipo de Mantenimiento: " . htmlspecialchars($tarea['tipo_mantenimiento']) . "\n" .
    "Prioridad: " . ucfirst(htmlspecialchars($tarea['prioridad'])) . "\n" .
    "Inicio: " . htmlspecialchars($tarea['fecha_inicio'] . ' ' . $tarea['hora_inicio']) . "\n" .
    "Fin: " . htmlspecialchars($tarea['fecha_hora_finalizacion']) . "",
    1, 'L', true, 1, '', '', true, 0, false, true, $cellHeight, 'M', true
);

// Línea divisoria inferior
$pdf->SetDrawColor(0, 102, 204);
$pdf->SetLineWidth(0.5);
$pdf->Line(20, $startY + $cellHeight + 2, 190, $startY + $cellHeight + 2);
$pdf->SetY($startY + $cellHeight + 7);

// ------------------ DESCRIPCIÓN JUSTIFICADA ------------------
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(0, 51, 102);
$pdf->Cell(0, 8, 'DESCRIPCIÓN DE LA TAREA', 0, 1, 'C');
$pdf->SetDrawColor(0, 102, 204);
$pdf->SetLineWidth(0.5);
$pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
$pdf->Ln(4);
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(40, 40, 40);
$pdf->SetFillColor(240, 248, 255);
$pdf->MultiCell(0, 7, strip_tags($tarea['descripcion_tarea']), 0, 'J', true);
$pdf->Ln(7);

// ------------------ MÁQUINA / EQUIPO ------------------
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(0, 51, 102);
$pdf->Cell(0, 8, 'DETALLES DEL EQUIPO INTERVENIDO', 0, 1, 'C');
$pdf->SetDrawColor(0, 102, 204);
$pdf->SetLineWidth(0.5);
$pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
$pdf->Ln(4);
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(40, 40, 40);
$pdf->SetFillColor(245, 245, 245);

$detalleEquipo = 
    "Código Único: " . ($tarea['codigo_maquina'] ?: 'N/A') . "\n" .
    "Nombre del Equipo: " . ($tarea['nombre_maquina'] ?: 'N/A') . "\n" .
    "Marca: " . ($tarea['nombre_marca'] ?: 'N/A') . "\n" .
    "Modelo: " . ($tarea['nombre_modelo'] ?: 'N/A') . "\n" .
    "Ubicación: " . ($tarea['ubicacion_sede'] ?: 'N/A');

$pdf->MultiCell(0, 7, $detalleEquipo, 0, 'L', true);
$pdf->Ln(7);

// ------------------ TIEMPOS Y ESTADO ------------------
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(0, 51, 102);
$pdf->Cell(0, 8, 'TIEMPOS Y ESTADO DE LA TAREA', 0, 1, 'C');
$pdf->SetDrawColor(0, 102, 204);
$pdf->SetLineWidth(0.5);
$pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
$pdf->Ln(4);
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(40, 40, 40);
$pdf->SetFillColor(240, 255, 240);
$pdf->MultiCell(0, 7,
    "Tiempo Programado: " . $tarea['tiempo_programado'] . " minutos\n" .
    "Tiempo Real (paro máquina): " . $tarea['tiempo_paro_programado'] . "\n" .
    "Estado Actual: " . $tarea['estado_tarea'],
0, 'L', true);
$pdf->Ln(7);
// ------------------ PERSONAL TÉCNICO ASIGNADO ------------------
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(0, 51, 102);
$pdf->Cell(0, 10, 'PERSONAL TÉCNICO ASIGNADO', 0, 1, 'C');
$pdf->SetDrawColor(0, 102, 204);
$pdf->SetLineWidth(0.5);
$pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
$pdf->Ln(4);
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(40, 40, 40);
$pdf->SetFillColor(235, 245, 255);
$pdf->MultiCell(0, 7, $tarea['responsables_asignados'] ?: 'No especificados', 0, 'L', true);
$pdf->Ln(8);

// ------------------ OBSERVACIONES Y VALIDACIONES ------------------
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(0, 51, 102);
$pdf->Cell(0, 10, 'OBSERVACIONES Y VALIDACIONES', 0, 1, 'C');
$pdf->SetDrawColor(0, 102, 204);
$pdf->SetLineWidth(0.5);
$pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
$pdf->Ln(4);
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(40, 40, 40);
$pdf->SetFillColor(250, 250, 250);
$pdf->MultiCell(0, 7, "Análisis Técnico:\n\n______________________________________________________________", 0, 'L', true);
$pdf->Ln(2);
$pdf->MultiCell(0, 7, "Observaciones:\n" . ($tarea['observacion'] ?? '') . "\n\n______________________________________________________________", 0, 'L', true);
$pdf->Ln(2);
$pdf->MultiCell(0, 7, "Normas Aplicadas:\n\n______________________________________________________________", 0, 'L', true);
$pdf->Ln(10);

// ------------------ FIRMAS Y VALIDACIÓN ------------------
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(0, 51, 102);
$pdf->Cell(0, 8, 'VALIDACIÓN Y CONFORMIDAD', 0, 1, 'C');
$pdf->SetDrawColor(0, 102, 204);
$pdf->SetLineWidth(0.5);
$pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
$pdf->Ln(6);
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(40, 40, 40);
$pdf->Cell(85, 8, 'Firma del Supervisor Responsable:', 0, 0, 'L');
$pdf->Cell(0, 8, 'Firma del Técnico:', 0, 1, 'L');
$pdf->Ln(10);
$pdf->SetDrawColor(160, 160, 160);
$pdf->Line(25, $pdf->GetY(), 95, $pdf->GetY());
$pdf->Line(115, $pdf->GetY(), 185, $pdf->GetY());
$pdf->Ln(2);
$pdf->SetFont('helvetica', 'I', 9);
$pdf->SetTextColor(120, 120, 120);
$pdf->Cell(85, 6, 'Nombre y Firma', 0, 0, 'C');
$pdf->Cell(0, 6, 'Nombre y Firma', 0, 1, 'C');
$pdf->Ln(8);
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(40, 40, 40);
$pdf->Cell(0, 8, 'Fecha de Validación: ____ / ____ / ______', 0, 1, 'L');
$pdf->Ln(8);

// ------------------ PIE DE PÁGINA ------------------
$pdf->SetTextColor(100, 100, 100);
$pdf->SetFont('helvetica', 'I', 9);
$pdf->SetDrawColor(0, 102, 204);
$pdf->SetLineWidth(0.5);
$pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
$pdf->Ln(2);
$pdf->MultiCell(0, 8, 'Documento generado automáticamente por el Sistema de Gestión de Mantenimiento de TAMANACO INDUSTRIAL C.A. | Contacto: soporte@tamanaco.com | Tel: +58 212 0000000', 0, 'C');

// Salida
$pdf->Output('constancia_mantenimiento.pdf', 'I');
?>
