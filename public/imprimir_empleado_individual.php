<?php
require_once('lib/tcpdf/tcpdf.php');

if (!isset($_GET['id_persona'])) {
    die('ID de empleado no proporcionado.');
}
$id_persona = intval($_GET['id_persona']);

// Conexión
$conn = new mysqli('localhost', 'root', '', 'bd_tamanaco');
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Consulta
$sql = "SELECT 
            p.*, 
            c.nombre_cargo, 
            s.nombre_status, 
            pais.paisnombre, 
            estado.estadonombre
        FROM personas p
        LEFT JOIN cargo c ON p.id_cargo = c.id_cargo
        LEFT JOIN status s ON p.id_status = s.id_status
        LEFT JOIN pais pais ON p.pais_id = pais.id
        LEFT JOIN estado estado ON p.estado_id = estado.id
        WHERE p.id_persona = $id_persona";
$result = $conn->query($sql);
if (!$result || $result->num_rows === 0) {
    die('Empleado no encontrado.');
}
$empleado = $result->fetch_assoc();
$conn->close();

// PDF
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Perfil Profesional');
$pdf->SetMargins(20, 20, 20);
$pdf->AddPage();

// Logo + encabezado
$logo = 'img/logo2.jpg';
if (file_exists($logo)) {
    $pdf->Image($logo, 15, 10, 25);
}
$pdf->SetXY(50, 12);
$pdf->SetFont('helvetica', 'B', 20);
$nombreCompleto = $empleado['primer_nombre'] . ' ' . $empleado['segundo_nombre'] . ' ' . $empleado['primer_apellido'] . ' ' . $empleado['segundo_apellido'];
$pdf->Cell(0, 10, strtoupper($nombreCompleto), 0, 1, 'L');

$pdf->SetX(50);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 8, $empleado['nombre_cargo'] ?? 'Cargo no asignado', 0, 1, 'L');

$pdf->Ln(10);

// Contenido
$pdf->SetFont('helvetica', '', 10);
$html = '
<style>
    h2 { font-size: 14pt; color: #003366; margin-top: 15px; }
    .label { font-weight: bold; color: #333; width: 40%; display: inline-block; }
    .value { color: #000; }
    .section { margin-top: 10px; }
</style>

<h2>Datos Personales</h2>
<div class="section">
    <span class="label">Cédula:</span> <span class="value">' . $empleado['nacionalidad'] . '-' . $empleado['cedula'] . '</span><br>
    <span class="label">Fecha de nacimiento:</span> <span class="value">' . $empleado['fecha_nacimiento'] . '</span><br>
    <span class="label">Edad:</span> <span class="value">' . $empleado['edad'] . ' años</span><br>
    <span class="label">Género:</span> <span class="value">' . $empleado['genero'] . '</span>
</div>

<h2>Información de Contacto</h2>
<div class="section">
    <span class="label">Correo electrónico:</span> <span class="value">' . $empleado['correo_electronico'] . '</span><br>
    <span class="label">Teléfono:</span> <span class="value">' . $empleado['telefono'] . '</span><br>

</div>

<h2>Ubicación</h2>
<div class="section">
    <span class="label">País:</span> <span class="value">' . ($empleado['paisnombre'] ?? 'No especificado') . '</span><br>
    <span class="label">Estado:</span> <span class="value">' . ($empleado['estadonombre'] ?? 'No especificado') . '</span><br>
        <span class="label">Dirección:</span> <span class="value">' . nl2br($empleado['direccion']) . '</span>
</div>

<h2>Otros Detalles</h2>
<div class="section">
    <span class="label">Estatus:</span> <span class="value">' . $empleado['nombre_status'] . '</span><br>
    <span class="label">Fecha de ingreso:</span> <span class="value">' . $empleado['fecha_creacion'] . '</span>
</div>
';

$pdf->writeHTML($html, true, false, true, false, '');

// Pie
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'I', 9);
$pdf->Cell(0, 10, 'Documento generado el ' . date('d/m/Y H:i:s'), 0, 0, 'R');

$pdf->Output('perfil_empleado_' . $id_persona . '.pdf', 'I');
?>
