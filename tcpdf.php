<?php
require_once('public/lib/tcpdf/tcpdf.php'); // Asegúrate de incluir correctamente TCPDF

// Crear nuevo documento PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Configurar información del documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Nicola Asuni');
$pdf->SetTitle('TCPDF Example 001');
$pdf->SetSubject('TCPDF Tutorial');
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// Deshabilitar el encabezado y pie predeterminados
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Configurar márgenes
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

// Añadir una página
$pdf->AddPage();

// Agregar una imagen al encabezado
$imgFile = 'public/img/feature.jpg'; // Ruta de la imagen que deseas añadir
if (file_exists($imgFile)) {
    $pdf->Image($imgFile, 10, 10, 60, 20, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
} else {
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'Error: La imagen no se encuentra en la ruta especificada.', 0, 1, 'C');
}

// Establecer un título debajo del encabezado
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Ln(25); // Espacio después de la imagen
$pdf->Cell(0, 10, 'Bienvenido al tutorial de TCPDF', 0, 1, 'C');

// Contenido del reporte
$html = <<<EOD
<p>Este es un ejemplo de contenido dentro del reporte generado con TCPDF.</p>
<p>Usamos una imagen en el encabezado para hacerlo más atractivo y profesional.</p>
<p>¡Puedes personalizar el contenido según tus necesidades!</p>
EOD;

// Imprimir el contenido como HTML
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// Generar el archivo PDF
$pdf->Output('reporte_con_encabezado.pdf', 'I');
?>