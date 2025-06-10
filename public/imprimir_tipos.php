<?php
require_once('lib/tcpdf/tcpdf.php'); // Asegúrate de incluir correctamente TCPDF

// Crear una nueva instancia de TCPDF
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Reporte de Tipos');

// Configurar márgenes y página
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();

// Agregar el logo en formato JPG al encabezado
$pdf->Image('img/encabezado.png', 10, 10, 190, 25);
$pdf->SetDrawColor(0, 102, 204);
$pdf->SetLineWidth(1);
$pdf->Ln(32);

// Espacio debajo del logo
$pdf->Ln(20); // Ajustar espacio para separar el logo del contenido

// Establecer el título debajo de la línea decorativa
$pdf->SetFont('helvetica', 'B', 20);
$pdf->SetTextColor(0, 102, 204); // Azul
$pdf->Ln(10); // Espacio después de la línea
$pdf->Cell(0, 10, 'Listado de Tipos', 0, 1, 'C');
$pdf->Ln(5);

// Conexión a la base de datos
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Consulta para obtener los tipos
$sql = "SELECT id_tipo, nombre_tipo FROM tipo";
$resultado = $conexion->query($sql);

// Verificar si se obtienen resultados
if ($resultado->num_rows > 0) {
    // Crear la tabla con estilo personalizado
    $tbl = '<table border="1" cellpadding="6" style="border-collapse:collapse;">
                <thead>
                    <tr style="background-color:#0066CC; color:#FFFFFF;">
                        <th width="20%" align="center">ID</th>
                        <th width="80%" align="center">Nombre</th>
                    </tr>
                </thead>
                <tbody>';
    
    while ($fila = $resultado->fetch_assoc()) {
        $tbl .= '<tr>
                    <td align="center">' . $fila['id_tipo'] . '</td>
                    <td>' . $fila['nombre_tipo'] . '</td>
                 </tr>';
    }
    $tbl .= '</tbody></table>';
    
    // Escribir la tabla en el PDF
    $pdf->writeHTML($tbl, true, false, false, false, '');
} else {
    $pdf->SetFont('helvetica', '', 12);
    $pdf->SetTextColor(255, 0, 0); // Rojo para mensajes de error
    $pdf->Cell(0, 10, 'No se encontraron tipos en la base de datos.', 0, 1, 'C');
}

// Cerrar conexión
$conexion->close();

// Salida del archivo PDF
$pdf->Output('listado_de_tipos.pdf', 'I');
?>
