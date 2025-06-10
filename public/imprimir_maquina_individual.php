<?php
require_once('lib/tcpdf/tcpdf.php');

$id = isset($_GET['id_maquina']) ? intval($_GET['id_maquina']) : 0;
if ($id <= 0) {
    die('ID inválido');
}

$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$sql = "SELECT 
            m.id_maquina, 
            m.nombre_maquina, 
            m.descripcion_funcionamiento, 
            m.elaborada_por, 
            ma.nombre_marca, 
            mo.nombre_modelo, 
            t.nombre_tipo, 
            m.sugerencia_mantenimiento, 
            s.nombre_status, 
            m.nombre_imagen, 
            m.url, 
            m.date_created 
        FROM maquina m
        LEFT JOIN marca ma ON m.id_marca = ma.id_marca
        LEFT JOIN modelo mo ON m.id_modelo = mo.id_modelo
        LEFT JOIN tipo t ON m.id_tipo = t.id_tipo
        LEFT JOIN status s ON m.id_status = s.id_status
        WHERE m.id_maquina = $id";

$resultado = $conexion->query($sql);
if ($resultado->num_rows == 0) {
    die("Máquina no encontrada");
}
$maquina = $resultado->fetch_assoc();

$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Reporte de Máquina');
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();

// === Ruta absoluta para imagen ===
$imagenLocal = 'servidor_img/maquina/' . $maquina['nombre_imagen'];
$rutaImagen = file_exists($imagenLocal) ? $imagenLocal : '';

// === Estilo para unir tablas ===
$estiloTabla = 'style="border-collapse: collapse;"';

// === TABLA 1: LOGO + DESCRIPCIÓN ===
$html = '<table width="100%" cellpadding="5" cellspacing="0" border="1" ' . $estiloTabla . '>
<tr>
    <td width="40%" align="center">';
$logoPath = 'img/logo2.jpg';
if (file_exists($logoPath)) {
    $html .= '<img src="' . $logoPath . '" width="100">';
} else {
    $html .= 'LOGO NO DISPONIBLE';
}
$html .= '</td>
    <td width="60%" align="center" style="font-size:14px; font-weight:bold;">DESCRIPCIÓN DE LA MÁQUINA</td>
</tr>
</table>';

$pdf->writeHTML($html, false, false, false, false, '');

// === TABLA 2: IMAGEN + DATOS ===
$html = '<table width="100%" cellpadding="5" cellspacing="0" border="1" ' . $estiloTabla . '>
<tr>
    <td width="40%" align="center">';
if (!empty($rutaImagen)) {
    $html .= '<img src="' . $rutaImagen . '" width="150">';
} else {
    $html .= 'Sin imagen disponible';
}
$html .= '</td>
    <td width="60%">
        <table width="100%" cellpadding="5" cellspacing="0" style="border-collapse: collapse;" ' . $estiloTabla . '>
            <tr>
                <td width="50%"><b>Nombre:</b><br>' . htmlspecialchars($maquina['nombre_maquina']) . '</td>
                <td width="50%"><b>Tipo:</b><br>' . htmlspecialchars($maquina['nombre_tipo']) . '</td>
            </tr>
            <tr>
                <td><b>Marca:</b><br>' . htmlspecialchars($maquina['nombre_marca']) . '</td>
                <td><b>Modelo:</b><br>' . htmlspecialchars($maquina['nombre_modelo']) . '</td>
            </tr>
        </table>
    </td>
</tr>
</table>';

$pdf->writeHTML($html, false, false, false, false, '');

// === TABLA 3: DESCRIPCIÓN FUNCIONAMIENTO ===
$html = '<table width="100%" cellpadding="6" cellspacing="0" border="1" ' . $estiloTabla . '>
<tr>
    <td>
        <b>Descripción de Funcionamiento:</b><br>' . strip_tags($maquina['descripcion_funcionamiento']) . '
    </td>
</tr>
</table>';

$pdf->writeHTML($html, false, false, false, false, '');
// === CONSULTAR ESPECIFICACIONES ===
$sqlEsp = "SELECT nombre_especificacion, descripcion_especificacion FROM especificaciones_maquina WHERE id_maquina = $id";
$resEsp = $conexion->query($sqlEsp);
if ($resEsp && $resEsp->num_rows > 0) {
    $html = '<h4>Especificaciones</h4>';
    $html .= '<table width="100%" cellpadding="5" cellspacing="0" border="1" ' . $estiloTabla . '>
    <tr style="background-color:#f0f0f0; font-weight:bold;">
        <td width="30%">Especificación</td>
        <td width="70%">Descripción</td>
    </tr>';
    while ($esp = $resEsp->fetch_assoc()) {
        $html .= '<tr>
            <td>' . htmlspecialchars($esp['nombre_especificacion']) . '</td>
            <td>' . htmlspecialchars($esp['descripcion_especificacion']) . '</td>
        </tr>';
    }
    $html .= '</table>';
    $pdf->writeHTML($html, true, false, false, false, '');
}

// === CONSULTAR CARACTERÍSTICAS ===
$sqlCar = "SELECT nombre_caracteristica, descripcion_caracteristica FROM caracteristicas_maquina WHERE id_maquina = $id";
$resCar = $conexion->query($sqlCar);
if ($resCar && $resCar->num_rows > 0) {
    $html = '<h4>Características</h4>';
    $html .= '<table width="100%" cellpadding="5" cellspacing="0" border="1" ' . $estiloTabla . '>
    <tr style="background-color:#f0f0f0; font-weight:bold;">
        <td width="30%">Característica</td>
        <td width="70%">Descripción</td>
    </tr>';
    while ($car = $resCar->fetch_assoc()) {
        $html .= '<tr>
            <td>' . htmlspecialchars($car['nombre_caracteristica']) . '</td>
            <td>' . htmlspecialchars($car['descripcion_caracteristica']) . '</td>
        </tr>';
    }
    $html .= '</table>';
    $pdf->writeHTML($html, true, false, false, false, '');
}

$conexion->close();
$pdf->Output('reporte_maquina_' . $maquina['id_maquina'] . '.pdf', 'I');
?>
