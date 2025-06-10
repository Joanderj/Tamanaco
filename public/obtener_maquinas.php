<?php
$host = "localhost";
$dbname = "bd_tamanaco";
$user = "root";
$password = "";

header("Content-Type: application/json");

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $id_sede = isset($_GET['id_sede']) && $_GET['id_sede'] !== '' ? (int)$_GET['id_sede'] : null;

    $sql = "
        SELECT 
            mu.id_maquina_unica,
            mu.CodigoUnico,
            mu.Almacen,
            mu.id_sede,
            m.id_maquina,
            m.nombre_maquina,
            m.descripcion_funcionamiento,
            m.elaborada_por,
            m.nombre_imagen,
            m.url,
            marca.nombre_marca,
            modelo.nombre_modelo,
            modelo.año,
            tipo.nombre_tipo
        FROM maquina_unica mu
        JOIN maquina m ON m.id_maquina = mu.id_maquina
        JOIN marca ON m.id_marca = marca.id_marca
        JOIN modelo ON m.id_modelo = modelo.id_modelo
        JOIN tipo ON m.id_tipo = tipo.id_tipo
        WHERE mu.id_status = 1
        " . ($id_sede ? "AND mu.id_sede = :id_sede" : "") . "
        ORDER BY m.nombre_maquina ASC
    ";

    $stmt = $pdo->prepare($sql);
    if ($id_sede) {
        $stmt->bindParam(':id_sede', $id_sede, PDO::PARAM_INT);
    }
    $stmt->execute();

    $maquinas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($maquinas);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
