<?php
ob_start();
require_once('lib/tcpdf/tcpdf.php');

// Conexión
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Consulta de repuestos
$sql = "SELECT 
            r.id_repuesto, 
            r.nombre_repuesto, 
            r.nombre_imagen,
            m.nombre_marca,
            mo.nombre_modelo,
            t.nombre_tipo,
            s.nombre_status
        FROM repuesto r
        LEFT JOIN marca m ON r.id_marca = m.id_marca
        LEFT JOIN modelo mo ON r.id_modelo = mo.id_modelo
        LEFT JOIN tipo t ON r.id_tipo = t.id_tipo
        LEFT JOIN status s ON r.id_status = s.id_status
        ORDER BY r.nombre_repuesto ASC";

$resultado = $conexion->query($sql);
if ($resultado->num_rows == 0) {
    die("No hay repuestos registrados.");
}

// Crear PDF
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Reporte de Repuestos');
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(TRUE, 10);

// Procesar cada repuesto
while ($repuesto = $resultado->fetch_assoc()) {
    $pdf->AddPage();

    $rutaImagen = 'servidor_img/repuesto/' . $repuesto['nombre_imagen'];
    $tieneImagen = file_exists($rutaImagen);

    // Encabezado
    $html = '<table width="100%" border="1" cellpadding="5" style="border-collapse: collapse;">
    <tr>
        <td width="40%" align="center">';
    $logoPath = 'img/logo2.jpg';
    $html .= (file_exists($logoPath)) ? '<img src="' . $logoPath . '" width="100">' : 'LOGO NO DISPONIBLE';
    $html .= '</td>
        <td width="60%" align="center" style="font-size:14px; font-weight:bold;">' . htmlspecialchars($repuesto['nombre_repuesto']) . '</td>
    </tr>
    </table>';
    $pdf->writeHTML($html, false, false, false, false, '');

    // Detalles con imagen
    $html = '<table width="100%" border="1" cellpadding="5" style="border-collapse: collapse;">
    <tr>
        <td width="40%" align="center">';
    $html .= $tieneImagen ? '<img src="' . $rutaImagen . '" width="120">' : 'Imagen no disponible';
    $html .= '</td>
        <td width="60%">
            <table width="100%" cellpadding="5">
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

    // Consulta de proveedores
    $id = $repuesto['id_repuesto'];
    $sqlProv = "SELECT p.nombre_proveedor, p.telefono, p.email, pr.precio
                FROM proveedor_repuesto pr
                INNER JOIN proveedor p ON pr.id_proveedor = p.id_proveedor
                WHERE pr.id_repuesto = $id";
    $proveedores = $conexion->query($sqlProv);

    $html = '<h4 style="margin-top:10px;">Proveedores</h4>';
    if ($proveedores->num_rows > 0) {
        $html .= '<table width="100%" border="1" cellpadding="5" style="border-collapse: collapse;">
        <tr style="font-weight:bold; background-color:#f2f2f2;">
            <th>Nombre</th>
            <th>Teléfono</th>
            <th>Email</th>
            <th>Precio</th>
        </tr>';
        while ($fila = $proveedores->fetch_assoc()) {
            $html .= '<tr>
                <td>' . htmlspecialchars($fila['nombre_proveedor']) . '</td>
                <td>' . htmlspecialchars($fila['telefono']) . '</td>
                <td>' . htmlspecialchars($fila['email']) . '</td>
                <td>' . number_format($fila['precio'], 2, ',', '.') . '</td>
            </tr>';
        }
        $html .= '</table>';
    } else {
        $html .= '<p>No hay proveedores registrados para este repuesto.</p>';
    }

    $pdf->writeHTML($html, false, false, false, false, '');
}

$conexion->close();
ob_end_clean();
$pdf->Output('reporte_todos_los_repuestos.pdf', 'I');
?>
