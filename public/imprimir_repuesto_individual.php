<?php
ob_start();
require_once('lib/tcpdf/tcpdf.php');

$id = isset($_GET['id_repuesto']) ? intval($_GET['id_repuesto']) : 0;
if ($id <= 0) {
    die('ID de repuesto inválido');
}

// Conexión a la base de datos
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// === Obtener datos del repuesto ===
$sql = "SELECT 
            r.id_repuesto, 
            r.nombre_repuesto, 
            r.nombre_imagen,
            r.date_created,
            r.url,
            m.nombre_marca,
            mo.nombre_modelo,
            t.nombre_tipo,
            s.nombre_status
        FROM repuesto r
        LEFT JOIN marca m ON r.id_marca = m.id_marca
        LEFT JOIN modelo mo ON r.id_modelo = mo.id_modelo
        LEFT JOIN tipo t ON r.id_tipo = t.id_tipo
        LEFT JOIN status s ON r.id_status = s.id_status
        WHERE r.id_repuesto = $id";

$resultado = $conexion->query($sql);
if ($resultado->num_rows == 0) {
    die("Repuesto no encontrado");
}
$repuesto = $resultado->fetch_assoc();

// === Obtener proveedores asociados ===
$sql_proveedores = "SELECT p.nombre_proveedor, p.telefono, p.email 
                    FROM proveedor_repuesto pr 
                    INNER JOIN proveedor p ON pr.id_proveedor = p.id_proveedor 
                    WHERE pr.id_repuesto = $id";

$res_proveedores = $conexion->query($sql_proveedores);
$proveedores = [];
if ($res_proveedores && $res_proveedores->num_rows > 0) {
    while ($fila = $res_proveedores->fetch_assoc()) {
        $proveedores[] = $fila;
    }
}

// === Obtener especificaciones del repuesto ===
$sql_especificaciones = "SELECT detalle_especificacion, valor_especificacion 
                         FROM especificaciones_repuestos 
                         WHERE id_repuesto = $id";

$res_especificaciones = $conexion->query($sql_especificaciones);
$especificaciones = [];
if ($res_especificaciones && $res_especificaciones->num_rows > 0) {
    while ($fila = $res_especificaciones->fetch_assoc()) {
        $especificaciones[] = $fila;
    }
}


// === Crear PDF ===
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Reporte de Repuesto');
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();

// === Título ===
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Reporte Detallado de Repuesto', 0, 1, 'C');
$pdf->Ln(4);

// === Estilos básicos ===
$estiloTabla = 'style="border-collapse: collapse;"';
$rutaImagen = 'servidor_img/repuesto/' . $repuesto['nombre_imagen'];
$tieneImagen = file_exists($rutaImagen);

// === Encabezado con logo y nombre ===
$html = '<table width="100%" border="1" cellpadding="5" ' . $estiloTabla . '>
<tr>
    <td width="40%" align="center">';
$logoPath = 'img/logo2.jpg';
$html .= (file_exists($logoPath)) ? '<img src="' . $logoPath . '" width="100">' : 'LOGO NO DISPONIBLE';
$html .= '</td>
    <td width="60%" align="center" style="font-size:14px; font-weight:bold;">' . htmlspecialchars($repuesto['nombre_repuesto']) . '</td>
</tr>
</table>';
$pdf->writeHTML($html, false, false, false, false, '');

// === Imagen y datos del repuesto ===
$html = '<table width="100%" border="1" cellpadding="5" ' . $estiloTabla . '>
<tr>
    <td width="40%" align="center">';
$html .= $tieneImagen
    ? '<img src="' . $rutaImagen . '" width="120"><br>'
    : 'Imagen no disponible';
$html .= '</td>
    <td width="60%">
        <table width="100%" cellpadding="5" ' . $estiloTabla . '>
            <tr>
                <td><b>ID Repuesto:</b><br>' . $repuesto['id_repuesto'] . '</td>
                <td><b>Status:</b><br>' . htmlspecialchars($repuesto['nombre_status']) . '</td>
            </tr>
            <tr>
                <td><b>Marca:</b><br>' . htmlspecialchars($repuesto['nombre_marca']) . '</td>
                <td><b>Modelo:</b><br>' . htmlspecialchars($repuesto['nombre_modelo']) . '</td>
            </tr>
            <tr>
                <td colspan="2"><b>Tipo:</b><br>' . htmlspecialchars($repuesto['nombre_tipo']) . '</td>
            </tr>
          </table> 
    </td>
</tr>
</table>';
$pdf->writeHTML($html, false, false, false, false, '');

// === Especificaciones del repuesto ===
if (!empty($especificaciones)) {
    $html = '<br><h3>Especificaciones Técnicas</h3>
    <table border="1" cellpadding="5" width="100%" ' . $estiloTabla . '>
        <thead>
            <tr style="background-color:#f0f0f0;">
                <th><b>Nombre</b></th>
                <th><b>Descripción</b></th>
            </tr>
        </thead>
        <tbody>';
    foreach ($especificaciones as $esp) {
        $html .= '<tr>
                    <td>' . htmlspecialchars($esp['detalle_especificacion']) . '</td>
                    <td>' . htmlspecialchars($esp['valor_especificacion']) . '</td>
                  </tr>';
    }
    $html .= '</tbody></table>';
    $pdf->writeHTML($html, false, false, false, false, '');
}

// === Proveedores asociados ===
if (!empty($proveedores)) {
    $html = '<br><h3>Proveedores Asociados</h3>
    <table border="1" cellpadding="5" width="100%" ' . $estiloTabla . '>
        <thead>
            <tr style="background-color:#f0f0f0;">
                <th><b>Nombre</b></th>
                <th><b>Teléfono</b></th>
                <th><b>Email</b></th>
            </tr>
        </thead>
        <tbody>';
    foreach ($proveedores as $prov) {
        $html .= '<tr>
                    <td>' . htmlspecialchars($prov['nombre_proveedor']) . '</td>
                    <td>' . htmlspecialchars($prov['telefono']) . '</td>
                    <td>' . htmlspecialchars($prov['email']) . '</td>
                  </tr>';
    }
    $html .= '</tbody></table>';
} else {
    $html = '<br><p><i>No hay proveedores asociados a este repuesto.</i></p>';
}
$pdf->writeHTML($html, false, false, false, false, '');

// === Pie de página ===
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->Cell(0, 10, 'Reporte generado por el Sistema de Gestión de Repuestos - ' . date('d/m/Y H:i'), 0, 0, 'C');

$conexion->close();
$pdf->Output('reporte_repuesto_' . $repuesto['id_repuesto'] . '.pdf', 'I');
?>
