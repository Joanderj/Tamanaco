<?php
require 'vendor/autoload.php'; // Cargar Dompdf

use Dompdf\Dompdf;

// Crear una instancia de Dompdf
$dompdf = new Dompdf();
$dompdf->set_option('isRemoteEnabled', true); // Habilitar acceso remoto a imágenes

// Contenido HTML con la gráfica SVG
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
        }
        h1 {
            color: #333;
        }
        .chart-container {
            margin: 20px;
            text-align: center;
        }
        svg {
            width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <h1>Reporte de Mantenimientos</h1>
    <p>Gráfica generada con datos de mantenimientos mensuales.</p>
    <div class="chart-container">
        <img class="card-img-top" src="public/img/project-2.jpg" alt="Importancia del Mantenimiento">
        <p>Esta es una gráfica de ejemplo. Puedes reemplazarla con tu propia gráfica SVG.</p>
</body>
</html>
';

// Cargar contenido HTML en Dompdf
$dompdf->loadHtml($html);

// Configurar tamaño de página
$dompdf->setPaper('A4', 'portrait');

// Renderizar el PDF
$dompdf->render();

// Mostrar el PDF en el navegador
$dompdf->stream("reporte_con_grafica.pdf", ["Attachment" => false]);
?>
