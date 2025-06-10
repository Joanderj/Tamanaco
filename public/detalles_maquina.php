<?php 
session_start();  

if (!isset($_SESSION['username'])) {
    header("Location: iniciar_sesion.php");
    exit();
}  

$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}  

$maquina = isset($_GET["id_maquina"]) ? intval($_GET["id_maquina"]) : 0;
$sede = isset($_GET["id_sede"]) ? intval($_GET["id_sede"]) : 0;

if ($maquina <= 0 || $sede <= 0) {
    die("Parámetros inválidos.");
}


$sql_detalles = "
    SELECT 
        mu.codigounico, 
        m.nombre_maquina,
        m.elaborada_por, 
        se.nombre_sede, 
        s.nombre_status,
        GROUP_CONCAT(r.nombre_repuesto SEPARATOR ', ') AS repuestos,
        m.url AS url_imagen, 
        m.nombre_imagen,
        mar.nombre_marca,
        mo.nombre_modelo
    FROM maquina_unica mu
    JOIN maquina m ON mu.id_maquina = m.id_maquina
    JOIN sede se ON mu.id_sede = se.id_sede
    JOIN status s ON mu.id_status = s.id_status
    JOIN marca mar ON m.id_marca = mar.id_marca
    JOIN modelo mo ON m.id_modelo = mo.id_modelo
    LEFT JOIN maquina_repuesto mr ON mu.id_maquina_unica = mr.id_maquina
    LEFT JOIN repuesto r ON mr.id_repuesto = r.id_repuesto
    WHERE mu.id_maquina = $maquina AND mu.id_sede = $sede
    GROUP BY mu.codigounico, m.nombre_maquina, se.nombre_sede, s.nombre_status, m.url, m.nombre_imagen, mar.nombre_marca, mo.nombre_modelo, m.elaborada_por
";

$result_detalles = $conexion->query($sql_detalles);

$maquinas = [];
if ($result_detalles && $result_detalles->num_rows > 0) {
    while ($row = $result_detalles->fetch_assoc()) {
        $maquinas[] = $row;
    }
}

$conexion->close();
$primera_maquina = $maquinas[0] ?? null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Detalles de Todas las Máquinas</title>
    <link href="../public/css/tailwind.min.css" rel="stylesheet">
    <link href="../public/lib/fontawesome-free-6.7.2-web/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/flatpickr.min.css">
    <link rel="stylesheet" href="../public/css/all.min.css">
    <link rel="stylesheet" href="../public/css/main.min.css">
    <script src="../public/js/chart.js"></script>
</head>
<body class="bg-blue-50">

<div class="container mx-auto p-6">

    <h1 class="text-3xl font-bold mb-6 text-blue-800 border-b-4 border-blue-600 pb-2">
        Detalles de Todas las Máquinas
    </h1>

    <?php if ($primera_maquina): ?>
    <section class="mb-8 p-6 bg-white rounded shadow-md flex items-center space-x-6">
        <img src="<?= htmlspecialchars($primera_maquina['url_imagen']) ?>" 
             alt="Imagen de <?= htmlspecialchars($primera_maquina['nombre_maquina']) ?>" 
             class="h-24 w-24 object-cover rounded border border-blue-400" />
        <div>
            <h2 class="text-xl font-semibold text-blue-700 flex items-center space-x-2">
                <i class="fas fa-tags text-green-500"></i>
                <span><?= htmlspecialchars($primera_maquina['nombre_maquina']) ?></span>
            </h2>
            <p class="text-blue-500 flex items-center space-x-2 mt-1">
                <i class="fas fa-id-badge text-indigo-500"></i>
                <span><?= htmlspecialchars($primera_maquina['codigounico']) ?></span>
            </p>

            <ul class="mt-3 space-y-1 text-gray-700 text-sm">
                <li class="flex items-center space-x-2">
                    <i class="fas fa-industry text-blue-500"></i>
                    <strong>Elaborada por:</strong>
                    <span><?= htmlspecialchars($primera_maquina['elaborada_por']) ?></span>
                </li>
                <li class="flex items-center space-x-2">
                    <i class="fas fa-industry text-blue-500"></i>
                    <strong>Marca:</strong>
                    <span><?= htmlspecialchars($primera_maquina['nombre_marca']) ?></span>
                </li>
                <li class="flex items-center space-x-2">
                    <i class="fas fa-cube text-yellow-500"></i>
                    <strong>Modelo:</strong>
                    <span><?= htmlspecialchars($primera_maquina['nombre_modelo']) ?></span>
                </li>
            </ul>
        </div>
    </section>
    <?php endif; ?>

    <?php if (count($maquinas) > 0): ?>
        <table class="min-w-full border-collapse border border-blue-300 shadow-lg bg-white rounded">
            <thead>
                <tr class="bg-blue-700 text-white">
                    <th class="border border-blue-300 px-5 py-3 text-left">Código Único</th>
                    <th class="border border-blue-300 px-5 py-3 text-left">Sede</th>
                    <th class="border border-blue-300 px-5 py-3 text-left">Repuestos</th>
                    <th class="border border-blue-300 px-5 py-3 text-left">Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($maquinas as $row): ?>
                    <tr class="bg-white hover:bg-blue-100 transition-colors duration-200">
                        <td class="border border-blue-300 px-5 py-3"><?= htmlspecialchars($row['codigounico']) ?></td>
                        <td class="border border-blue-300 px-5 py-3"><?= htmlspecialchars($row['nombre_sede']) ?></td>
                        <td class="border border-blue-300 px-5 py-3"><?= htmlspecialchars($row['repuestos'] ?: 'Sin repuestos') ?></td>
                        <td class="border border-blue-300 px-5 py-3"><?= htmlspecialchars($row['nombre_status']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-center text-blue-700 text-lg mt-8">No se encontraron máquinas únicas.</p>
    <?php endif; ?>

    <div class="flex justify-center mt-8">
        <button onclick="window.location.href='inicio.php'" class="bg-blue-600 hover:bg-blue-700 transition-colors text-white font-semibold px-6 py-3 rounded shadow">
            Cerrar
        </button>
    </div>
</div>

</body>
</html>
