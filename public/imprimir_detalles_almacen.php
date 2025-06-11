<?php
require_once('lib/tcpdf/tcpdf.php'); // Asegúrate de incluir correctamente TCPDF

// Obtener la almacen desde la URL
$almacen_id = $_GET["almacen"];

// Crear una nueva instancia de TCPDF
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Reporte de almacen');

// Configurar márgenes y agregar una nueva página
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();

// Agregar el logo en formato JPG al encabezado
$imgFile = 'img/logo2.jpg'; // Ruta del archivo JPG
if (file_exists($imgFile)) {
    // Configurar posición y tamaño del logo (centrado horizontalmente)
    $pageWidth = $pdf->getPageWidth(); // Obtener el ancho de la página
    $logoWidth = 120; // Ancho del logo
    $logoHeight = 15; // Alto del logo
    $logoX = ($pageWidth - $logoWidth) / 2; // Calcular posición X para centrar

    $pdf->Image($imgFile, $logoX, 15, $logoWidth, $logoHeight, 'JPG', '', '', true, 300, '', false, false, 0, false, false, false);
} else {
    // Mostrar mensaje de error si el archivo JPG no existe
    $pdf->SetFont('helvetica', '', 10); // Cambiar tamaño a 10
    $pdf->SetTextColor(255, 0, 0); // Mensaje en rojo para destacar el error
    $pdf->Cell(0, 10, 'Error: El archivo logo.jpg no se encuentra en la ruta especificada.', 0, 1, 'C');
}

// Espacio debajo del logo
$pdf->Ln(20); // Ajustar espacio para separar el logo del contenido

// Establecer el título del documento
$pdf->SetFont('helvetica', 'B', 16); // Cambiar tamaño a 16
$pdf->SetTextColor(0, 102, 204); // Azul
$pdf->Ln(10); // Espacio después de la línea
$pdf->Cell(0, 5, 'Información del almacen', 0, 1, 'C');
$pdf->Ln(5);

// Conexión a la base de datos
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Consulta para obtener la información de la almacen
$sql = "SELECT al.id_almacen, al.nombre as almacen, s.nombre_sede, st.nombre_status as status FROM almacen al
JOIN sede s ON s.id_sede = al.id_sede
JOIN status st ON st.id_status = al.id_status
WHERE al.id_almacen = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $almacen_id);
$stmt->execute();
$resultado = $stmt->get_result();

// Verificar si se obtienen resultados
if ($resultado->num_rows > 0) {
    // Obtener datos de la almacen
    $almacen = $resultado->fetch_assoc();
    $pdf->SetFont('helvetica', '', 12);
    
    // Crear tabla para la información de la almacen
    $pdf->Cell(40, 10, 'ID almacen:', 1);
    $pdf->Cell(0, 10, $almacen['id_almacen'], 1, 1);
    
    $pdf->Cell(40, 10, 'Nombre:', 1);
    $pdf->Cell(0, 10, substr($almacen['almacen'], 0, 30) . (strlen($almacen['almacen']) > 30 ? '...' : ''), 1, 1); // Truncar si es necesario
    
    $pdf->Cell(40, 10, 'Sede Asociada:', 1);
    $pdf->Cell(0, 10, substr($almacen['nombre_sede'], 0, 30) . (strlen($almacen['nombre_sede']) > 30 ? '...' : ''), 1, 1); // Truncar si es necesario
    
    $pdf->Cell(40, 10, 'Status:', 1);
    $pdf->Cell(0, 10, substr($almacen['status'], 0, 30) . (strlen($almacen['status']) > 30 ? '...' : ''), 1, 1); // Truncar si es necesario
} else {
    // Mensaje si no se encontró la almacen
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(255, 0, 0); // Rojo para mensajes de error
    $pdf->Cell(0, 10, 'No se encontró la almacen en la base de datos.', 0, 1, 'C');
}

// Espacio antes de la sección de herramienta
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'B', 16);
$pdf->SetTextColor(0, 102, 204);
$pdf->Cell(0, 10, 'Listado de Herramientas en el Inventario Asociadas al almacen', 0, 1, 'C');
$pdf->Ln(5);

// Consulta para obtener las herramienta
$sql2 = "SELECT ih.id_inventario_herramienta, h.nombre_herramienta, ih.cantidad, ih.stock_minimo, ih.stock_maximo, punto_reorden FROM inventario_herramientas ih 
JOIN herramientas h ON h.id_herramienta = ih.herramienta_id
WHERE ih.id_almacen = ?";
$stmt2 = $conexion->prepare($sql2);
$stmt2->bind_param("i", $almacen_id);
$stmt2->execute();
$resultado2 = $stmt2->get_result();

// Verificar si se obtienen resultados
if ($resultado2->num_rows > 0) {
    $pdf->SetFont('helvetica', '', 12);
    
    // Crear tabla para las herramienta
    $pdf->Cell(20, 10, 'ID', 1);
    $pdf->Cell(50, 10, 'Nombre', 1);
    $pdf->Cell(30, 10, 'Cantidad', 1);
    $pdf->Cell(30, 10, 'Stock Minimo', 1);
    $pdf->Cell(30, 10, 'Stock Maximo', 1);
    $pdf->Cell(30, 10, 'Reorden', 1);
    $pdf->Ln();

    while ($herramienta = $resultado2->fetch_assoc()) {
        // Verificar si la altura total excede el límite de la página
        if ($pdf->GetY() > 250) { // Ajusta el valor según el espacio disponible
            $pdf->AddPage(); // Agregar nueva página si se excede
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->SetTextColor(0, 102, 204);
            $pdf->Cell(0, 10, 'Listado de herramienta del Inventario Asociadas al almacen', 0, 1, 'C');
            $pdf->Ln(5);
            $pdf->Cell(20, 10, 'ID', 1);
            $pdf->Cell(50, 10, 'Nombre', 1);
            $pdf->Cell(30, 10, 'Cantidad', 1);
            $pdf->Cell(30, 10, 'Stock Minimo', 1);
            $pdf->Cell(30, 10, 'Stock Maximo', 1);
            $pdf->Cell(30, 10, 'Punto de Reorden', 1);
            $pdf->Ln();
        }
        
        $pdf->Cell(20, 10, $herramienta['id_inventario_herramienta'], 1);
        
        // Cambiar a una fuente más pequeña para los nombres
        $pdf->SetFont('helvetica', '', 10); // Fuente más pequeña
        $pdf->Cell(50, 10, substr($herramienta['nombre_herramienta'], 0, 30) . (strlen($herramienta['nombre_herramienta']) > 30 ? '...' : ''), 1); // Truncar si es necesario
        
        // Volver a la fuente normal
        $pdf->SetFont('helvetica', '', 12); // Fuente normal para los demás campos
        $pdf->Cell(30, 10, $herramienta['cantidad'], 1);
        $pdf->Cell(30, 10, $herramienta['stock_minimo'], 1);
        $pdf->Cell(30, 10, $herramienta['stock_maximo'], 1);
        $pdf->Cell(30, 10, $herramienta['punto_reorden'], 1);
        $pdf->Ln();
    }
} else {
    // Mensaje si no se encontraron herramienta
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(255, 0, 0); // Rojo para mensajes de error
    $pdf->Cell(0, 10, 'No se encontraron herramienta en la base de datos.', 0, 1, 'C');
}

// Espacio antes de la sección de producto
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'B', 16);
$pdf->SetTextColor(0, 102, 204);
$pdf->Cell(0, 10, 'Listado de productos del Inventario Asociadas al almacen', 0, 1, 'C');
$pdf->Ln(5);

// Consulta para obtener las producto
$sql3 = "SELECT ip.id_inventario_producto, p.nombre_producto, ip.cantidad, ip.stock_minimo, ip.stock_maximo, ip.punto_reorden FROM inventario_producto ip 
JOIN producto p ON p.id_producto = ip.id_producto
WHERE ip.id_almacen = ?";
$stmt3 = $conexion->prepare($sql3);
$stmt3->bind_param("i", $almacen_id);
$stmt3->execute();
$resultado3 = $stmt3->get_result();

// Verificar si se obtienen resultados
if ($resultado3->num_rows > 0) {
    $pdf->SetFont('helvetica', '', 12);
    
    // Crear tabla para las producto
    $pdf->Cell(20, 10, 'ID', 1);
    $pdf->Cell(50, 10, 'Nombre', 1);
    $pdf->Cell(30, 10, 'Cantidad', 1);
    $pdf->Cell(30, 10, 'Stock Minimo', 1);
    $pdf->Cell(30, 10, 'Stock Maximo', 1);
    $pdf->Cell(30, 10, 'Reorden', 1);
    $pdf->Ln();

    while ($producto = $resultado3->fetch_assoc()) {
        // Verificar si la altura total excede el límite de la página
        if ($pdf->GetY() > 250) { // Ajusta el valor según el espacio disponible
            $pdf->AddPage(); // Agregar nueva página si se excede
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->SetTextColor(0, 102, 204);
            $pdf->Cell(0, 10, 'Listado de producto del Inventario Asociadas al almacen', 0, 1, 'C');
            $pdf->Ln(5);
            $pdf->Cell(20, 10, 'ID', 1);
            $pdf->Cell(50, 10, 'Nombre', 1);
            $pdf->Cell(30, 10, 'Cantidad', 1);
            $pdf->Cell(30, 10, 'Stock Minimo', 1);
            $pdf->Cell(30, 10, 'Stock Maximo', 1);
            $pdf->Cell(30, 10, 'Punto de Reorden', 1);
            $pdf->Ln();
        }
        
        $pdf->Cell(20, 10, $producto['id_inventario_producto'], 1);
        
        // Cambiar a una fuente más pequeña para los nombres
        $pdf->SetFont('helvetica', '', 10); // Fuente más pequeña
        $pdf->Cell(50, 10, substr($producto['nombre_producto'], 0, 30) . (strlen($producto['nombre_producto']) > 30 ? '...' : ''), 1); // Truncar si es necesario
        
        // Volver a la fuente normal
        $pdf->SetFont('helvetica', '', 12); // Fuente normal para los demás campos
        $pdf->Cell(30, 10, $producto['cantidad'], 1);
        $pdf->Cell(30, 10, $producto['stock_minimo'], 1);
        $pdf->Cell(30, 10, $producto['stock_maximo'], 1);
        $pdf->Cell(30, 10, $producto['punto_reorden'], 1);
        $pdf->Ln();
    }
} else {
    // Mensaje si no se encontraron producto
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(255, 0, 0); // Rojo para mensajes de error
    $pdf->Cell(0, 10, 'No se encontraron producto en la base de datos.', 0, 1, 'C');
}

// Espacio antes de la sección de repuesto
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'B', 16);
$pdf->SetTextColor(0, 102, 204);
$pdf->Cell(0, 10, 'Listado de repuestos del Inventario Asociadas al almacen', 0, 1, 'C');
$pdf->Ln(5);

// Consulta para obtener las repuesto
$sql4 = "SELECT ir.id_inventario_repuesto, r.nombre_repuesto, ir.cantidad, ir.stock_minimo, ir.stock_maximo, ir.punto_reorden FROM inventario_repuesto ir 
JOIN repuesto r ON r.id_repuesto = ir.id_repuesto
WHERE ir.id_almacen = ?";
$stmt4 = $conexion->prepare($sql4);
$stmt4->bind_param("i", $almacen_id);
$stmt4->execute();
$resultado4 = $stmt4->get_result();

// Verificar si se obtienen resultados
if ($resultado4->num_rows > 0) {
    $pdf->SetFont('helvetica', '', 12);
    
    // Crear tabla para las repuesto
    $pdf->Cell(20, 10, 'ID', 1);
    $pdf->Cell(50, 10, 'Nombre', 1);
    $pdf->Cell(30, 10, 'Cantidad', 1);
    $pdf->Cell(30, 10, 'Stock Minimo', 1);
    $pdf->Cell(30, 10, 'Stock Maximo', 1);
    $pdf->Cell(30, 10, 'Reorden', 1);
    $pdf->Ln();

    while ($repuesto = $resultado4->fetch_assoc()) {
        // Verificar si la altura total excede el límite de la página
        if ($pdf->GetY() > 250) { // Ajusta el valor según el espacio disponible
            $pdf->AddPage(); // Agregar nueva página si se excede
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->SetTextColor(0, 102, 204);
            $pdf->Cell(0, 10, 'Listado de repuesto del Inventario Asociadas al almacen', 0, 1, 'C');
            $pdf->Ln(5);
            $pdf->Cell(20, 10, 'ID', 1);
            $pdf->Cell(50, 10, 'Nombre', 1);
            $pdf->Cell(30, 10, 'Cantidad', 1);
            $pdf->Cell(30, 10, 'Stock Minimo', 1);
            $pdf->Cell(30, 10, 'Stock Maximo', 1);
            $pdf->Cell(30, 10, 'Punto de Reorden', 1);
            $pdf->Ln();
        }
        
        $pdf->Cell(20, 10, $repuesto['id_inventario_repuesto'], 1);
        
        // Cambiar a una fuente más pequeña para los nombres
        $pdf->SetFont('helvetica', '', 10); // Fuente más pequeña
        $pdf->Cell(50, 10, substr($repuesto['nombre_repuesto'], 0, 30) . (strlen($repuesto['nombre_repuesto']) > 30 ? '...' : ''), 1); // Truncar si es necesario
        
        // Volver a la fuente normal
        $pdf->SetFont('helvetica', '', 12); // Fuente normal para los demás campos
        $pdf->Cell(30, 10, $repuesto['cantidad'], 1);
        $pdf->Cell(30, 10, $repuesto['stock_minimo'], 1);
        $pdf->Cell(30, 10, $repuesto['stock_maximo'], 1);
        $pdf->Cell(30, 10, $repuesto['punto_reorden'], 1);
        $pdf->Ln();
    }
} else {
    // Mensaje si no se encontraron repuesto
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(255, 0, 0); // Rojo para mensajes de error
    $pdf->Cell(0, 10, 'No se encontraron repuesto en la base de datos.', 0, 1, 'C');
}

// Cerrar conexión
$conexion->close();

// Salida del archivo PDF
$pdf->Output('detalles_almacen.pdf', 'I');
?>
