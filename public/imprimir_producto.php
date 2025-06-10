<?php
ob_start(); // Inicia el búfer de salida

require_once('lib/tcpdf/tcpdf.php');

// Conexión a la base de datos
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtener todos los productos con sus detalles
$sql = "SELECT 
            p.id_producto, 
            p.nombre_producto, 
            m.nombre_marca,
            mo.nombre_modelo,
            t.nombre_tipo,
            c.nombre_clasificacion, 
            p.unidad_medida, 
            s.nombre_status,
            p.date_created, 
            p.nombre_imagen
        FROM producto p
        LEFT JOIN marca m ON p.id_marca = m.id_marca
        LEFT JOIN modelo mo ON p.id_modelo = mo.id_modelo
        LEFT JOIN tipo t ON p.id_tipo = t.id_tipo
        LEFT JOIN clasificacion c ON p.id_clasificacion = c.id_clasificacion
        LEFT JOIN status s ON p.id_status = s.id_status";

$resultado = $conexion->query($sql);
if ($resultado->num_rows == 0) {
    die("No hay productos registrados.");
}

// Crear PDF
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Reporte de Productos');
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(TRUE, 10);

// Recorremos cada producto
while ($producto = $resultado->fetch_assoc()) {
    $pdf->AddPage();

    $rutaImagen = 'servidor_img/producto/' . $producto['nombre_imagen'];
    $tieneImagen = file_exists($rutaImagen);

    // Encabezado con logo y nombre de producto
    $html = '<table width="100%" border="1" cellpadding="5" style="border-collapse: collapse;">
    <tr>
        <td width="40%" align="center">';
    $logoPath = 'img/logo2.jpg';
    $html .= (file_exists($logoPath)) ? '<img src="' . $logoPath . '" width="100">' : 'LOGO NO DISPONIBLE';
    $html .= '</td>
        <td width="60%" align="center" style="font-size:14px; font-weight:bold;">' . htmlspecialchars($producto['nombre_producto']) . '</td>
    </tr>
    </table>';
    $pdf->writeHTML($html, false, false, false, false, '');

    // Imagen + detalles
    $html = '<table width="100%" border="1" cellpadding="5" style="border-collapse: collapse;">
    <tr>
        <td width="40%" align="center">';
    $html .= $tieneImagen ? '<img src="' . $rutaImagen . '" width="120">' : 'Imagen no disponible';
    $html .= '</td>
        <td width="60%">
            <table width="100%" cellpadding="5">
                <tr>
                    <td><b>ID Producto:</b><br>' . $producto['id_producto'] . '</td>
                    <td><b>Status:</b><br>' . htmlspecialchars($producto['nombre_status']) . '</td>
                </tr>
                <tr>
                    <td><b>Marca:</b><br>' . htmlspecialchars($producto['nombre_marca']) . '</td>
                    <td><b>Modelo:</b><br>' . htmlspecialchars($producto['nombre_modelo']) . '</td>
                </tr>
                <tr>
                    <td><b>Tipo:</b><br>' . htmlspecialchars($producto['nombre_tipo']) . '</td>
                    <td><b>Clasificación:</b><br>' . htmlspecialchars($producto['nombre_clasificacion']) . '</td>
                </tr>
                <tr>
                    <td colspan="2"><b>Unidad de medida:</b><br>' . htmlspecialchars($producto['unidad_medida']) . '</td>
                </tr>
                <tr>
                    <td colspan="2"><b>Fecha de creación:</b><br>' . $producto['date_created'] . '</td>
                </tr>
            </table>
        </td>
    </tr>
    </table>';
    $pdf->writeHTML($html, false, false, false, false, '');

    // Obtener proveedores del producto
    $id = $producto['id_producto'];
    $sqlProveedores = "SELECT pr.nombre_proveedor, pr.telefono, pr.email, pp.precio
                       FROM proveedor_producto pp
                       INNER JOIN proveedor pr ON pp.id_proveedor = pr.id_proveedor
                       WHERE pp.id_producto = $id";
    $proveedores = $conexion->query($sqlProveedores);

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
        $html .= '<p>No hay proveedores registrados para este producto.</p>';
    }
    $pdf->writeHTML($html, false, false, false, false, '');
}

$conexion->close();
ob_end_clean(); // Limpiar buffer
$pdf->Output('reporte_todos_los_productos.pdf', 'I');
?>
