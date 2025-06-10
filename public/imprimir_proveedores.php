<?php
require_once('lib/tcpdf/tcpdf.php'); // Asegúrate de incluir correctamente TCPDF

// Crear una nueva instancia de TCPDF
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Listado de Proveedores');

// Configurar márgenes y página
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();

// Agregar el logo en formato JPG al encabezado
// Encabezado gráfico
$pdf->Image('img/encabezado.png', 10, 10, 190, 25);
$pdf->SetDrawColor(0, 102, 204);
$pdf->SetLineWidth(1);
$pdf->Ln(32);



// Conexión a la base de datos
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Consulta para obtener los proveedores con sus respectivos país y estado
$sql = "SELECT p.id_proveedor, p.nombre_proveedor, p.telefono, p.email, p.direccion, 
               pa.paisnombre AS nombre_pais, e.estadonombre AS nombre_estado
        FROM proveedor p
        LEFT JOIN pais pa ON p.id_pais = pa.id
        LEFT JOIN estado e ON p.id_estado = e.id";

$resultado = $conexion->query($sql);

// Verificar si se obtienen resultados
if ($resultado->num_rows > 0) {
    // Crear la tabla con estilo personalizado
    $tbl = '<table border="1" cellpadding="6" style="border-collapse:collapse;">
                <thead>
                    <tr style="background-color:#0066CC; color:#FFFFFF;">
                        <th width="5%" align="center">ID</th>
                        <th width="25%" align="center">Nombre</th>
                        <th width="15%" align="center">Teléfono</th>
                        <th width="25%" align="center">Email</th>
                        <th width="30%" align="center">Dirección</th>
                    </tr>
                </thead>
                <tbody>';
    
    while ($fila = $resultado->fetch_assoc()) {
        $tbl .= '<tr>
                    <td width="5%" align="center">' . $fila['id_proveedor'] . '</td>
                    <td width="25%" align="center">' . $fila['nombre_proveedor'] . '</td>
                    <td width="15%" align="center">' . $fila['telefono'] . '</td>
                    <td width="25%" align="center">' . $fila['email'] . '</td>
                    <td width="30%" align="center">' . $fila['nombre_pais'] . ' - ' . $fila['nombre_estado'] . ' - ' . $fila['direccion'] . '</td>
                 </tr>';
    }
    $tbl .= '</tbody></table>';
    
    // Escribir la tabla en el PDF
    $pdf->writeHTML($tbl, true, false, false, false, '');
} else {
    $pdf->SetFont('helvetica', '', 12);
    $pdf->SetTextColor(255, 0, 0); // Rojo para mensajes de error
    $pdf->Cell(0, 10, 'No se encontraron proveedores en la base de datos.', 0, 1, 'C');
}

// Cerrar conexión
$conexion->close();

// Salida del archivo PDF
$pdf->Output('listado_proveedores.pdf', 'I');
?>
