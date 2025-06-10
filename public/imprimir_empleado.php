<?php
require_once('lib/tcpdf/tcpdf.php');

// Crear PDF
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Listado de Empleados');
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();

// Agregar logo
$imgFile = 'img/logo2.jpg';
if (file_exists($imgFile)) {
    $logoWidth = 100;
    $logoHeight = 10;
    $logoX = ($pdf->getPageWidth() - $logoWidth) / 2;
    $pdf->Image($imgFile, $logoX, 10, $logoWidth, $logoHeight, 'JPG');
} else {
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(255, 0, 0);
    $pdf->Cell(0, 10, 'Error: El archivo logo2.jpg no se encuentra.', 0, 1, 'C');
}

$pdf->Ln(20);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(0, 102, 204);
$pdf->Cell(0, 10, 'Listado de Empleados', 0, 1, 'C');
$pdf->Ln(3);

// Conexión a la BD
$conn = new mysqli('localhost', 'root', '', 'bd_tamanaco');
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Consulta
$sql = "SELECT 
            e.id_persona,
            e.primer_nombre,
            e.primer_apellido,
            e.cedula,
            e.telefono,
            e.correo_electronico,
            e.direccion,
            e.fecha_nacimiento,
            TIMESTAMPDIFF(YEAR, e.fecha_nacimiento, CURDATE()) AS edad,
            p.paisnombre AS pais,
            es.estadonombre AS estado,
            c.nombre_cargo AS cargo
        FROM personas e
        LEFT JOIN pais p ON e.pais_id = p.id
        LEFT JOIN estado es ON e.estado_id = es.id
        LEFT JOIN cargo c ON e.id_cargo = c.id_cargo";
$result = $conn->query($sql);

// Tabla
if ($result && $result->num_rows > 0) {
    $tbl = '<table border="1" cellpadding="4" style="font-size:8px; border-collapse:collapse;">
            <thead>
                <tr style="background-color:#0066CC; color:#FFFFFF;">
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Cédula</th>
                    <th>F. Nac</th>
                    <th>Edad</th>
                    <th>Teléfono</th>
                    <th>Correo</th>
                    <th>Dirección</th>
                    <th>País</th>
                    <th>Estado</th>
                    <th>Cargo</th>
                </tr>
            </thead>
            <tbody>';

    while ($row = $result->fetch_assoc()) {
        $tbl .= '<tr>
                    <td align="center">' . htmlspecialchars($row['id_persona']) . '</td>
                    <td>' . htmlspecialchars($row['primer_nombre']) . '</td>
                    <td>' . htmlspecialchars($row['primer_apellido']) . '</td>
                    <td>' . htmlspecialchars($row['cedula']) . '</td>
                    <td align="center">' . htmlspecialchars($row['fecha_nacimiento']) . '</td>
                    <td align="center">' . htmlspecialchars($row['edad']) . '</td>
                    <td>' . htmlspecialchars($row['telefono']) . '</td>
                    <td>' . htmlspecialchars($row['correo_electronico']) . '</td>
                    <td>' . htmlspecialchars($row['direccion']) . '</td>
                    <td>' . htmlspecialchars($row['pais']) . '</td>
                    <td>' . htmlspecialchars($row['estado']) . '</td>
                    <td>' . htmlspecialchars($row['cargo']) . '</td>
                </tr>';
    }

    $tbl .= '</tbody></table>';
    $pdf->writeHTML($tbl, true, false, false, false, '');
} else {
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(255, 0, 0);
    $pdf->Cell(0, 10, 'No se encontraron empleados.', 0, 1, 'C');
}

$conn->close();

// Salida PDF
$pdf->Output('listado_empleados.pdf', 'I');
?>
