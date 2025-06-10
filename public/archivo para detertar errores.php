<?php
// Mostrar todo lo que llega

echo "<h2>📦 Dump de php://input (crudo):</h2><pre>";
$input = file_get_contents("php://input");
echo htmlspecialchars($input);
echo "</pre>";

// Intentar decodificar JSON
echo "<h2>🧩 Decodificando JSON:</h2><pre>";
$data = json_decode($input, true);
if ($data !== null) {
    print_r($data);
} else {
    echo "⚠️ No se pudo decodificar JSON.\n";
}
echo "</pre>";

// Mostrar $_POST
echo "<h2>📝 Contenido de \$_POST:</h2><pre>";
print_r($_POST);
echo "</pre>";

// Mostrar $_GET
echo "<h2>🔎 Contenido de \$_GET:</h2><pre>";
print_r($_GET);
echo "</pre>";

// Verificar si hay repuestos directamente en $_POST
echo "<h2>🧪 Resultado de repuestos:</h2><pre>";

if (isset($_POST['repuestos']) && is_array($_POST['repuestos'])) {
    echo "✅ Repuestos recibidos desde \$_POST:\n";
    print_r($_POST['repuestos']);
} else {
    echo "❌ No se recibieron repuestos.";
}

echo "</pre>";
?>