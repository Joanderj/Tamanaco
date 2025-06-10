<?php
ob_start();
require_once('lib/tcpdf/tcpdf.php');

// Conexión a la base de datos
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Consulta para obtener todas las herramientas
$sql = "SELECT 
            h.id_herramienta,
            h.nombre_herramienta,
            m.nombre_marca,
            mo.nombre_modelo,
            t.nombre_tipo,
            s.nombre_status,
            h.date_created,
            h.nombre_imagen
        FROM herramientas h
        LEFT JOIN marca m ON h.id_marca = m.id_marca
        LEFT JOIN modelo mo ON h.id_modelo = mo.id_modelo
        LEFT JOIN tipo t ON h.id_tipo = t.id_tipo
        LEFT JOIN status s ON h.id_status = s.id_status
        ORDER BY h.nombre_herramienta ASC";

$resultado = $conexion->query($sql);
if ($resultado->num_rows == 0) {
    die("No hay herramientas registradas.");
}

// Crear PDF
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Reporte de Todas las Herramientas');
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 10);

// Estilo tabla
$estiloTabla = 'style="border-collapse: collapse;"';

while ($herramienta = $resultado->fetch_assoc()) {
    $pdf->AddPage();

    // Ruta de imagen
    $rutaImagen = 'servidor_img/herramientas/' . $herramienta['nombre_imagen'];
    $tieneImagen = file_exists($rutaImagen);

    // === Encabezado ===
    $html = '<table width="100%" border="1" cellpadding="5" ' . $estiloTabla . '>
    <tr>
        <td width="40%" align="center">';
    $logoPath = 'img/logo2.jpg';
    $html .= (file_exists($logoPath)) ? '<img src="' . $logoPath . '" width="100">' : 'LOGO NO DISPONIBLE';
    $html .= '</td>
        <td width="60%" align="center" style="font-size:14px; font-weight:bold;">' . htmlspecialchars($herramienta['nombre_herramienta']) . '</td>
    </tr>
    </table>';
    $pdf->writeHTML($html, false, false, false, false, '');

    // === Detalles ===
    $html = '<table width="100%" border="1" cellpadding="5" ' . $estiloTabla . '>
    <tr>
        <td width="40%" align="center">';
    $html .= $tieneImagen ? '<img src="' . $rutaImagen . '" width="120">' : 'Imagen no disponible';
    $html .= '</td>
        <td width="60%">
            <table width="100%" cellpadding="5" ' . $estiloTabla . '>
                <tr>
                    <td><b>ID Herramienta:</b><br>' . $herramienta['id_herramienta'] . '</td>
                    <td><b>Status:</b><br>' . htmlspecialchars($herramienta['nombre_status']) . '</td>
                </tr>
                <tr>
                    <td><b>Marca:</b><br>' . htmlspecialchars($herramienta['nombre_marca']) . '</td>
                    <td><b>Modelo:</b><br>' . htmlspecialchars($herramienta['nombre_modelo']) . '</td>
                </tr>
                <tr>
                    <td><b>Tipo:</b><br>' . htmlspecialchars($herramienta['nombre_tipo']) . '</td>
                </tr>
            </table>
        </td>
    </tr>
    </table>';
    $pdf->writeHTML($html, false, false, false, false, '');

    // === Fecha ===
    $pdf->Ln(5);
    $pdf->Write(0, 'Fecha de registro: ' . date('d/m/Y', strtotime($herramienta['date_created'])));
}

$conexion->close();
$pdf->Output('reporte_todas_herramientas.pdf', 'I');
?>
