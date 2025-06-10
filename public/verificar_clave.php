<?php
include 'db_connection.php';

$clave = $_POST['clave'];
$id = $_POST['id'];

try {
    $stmt = $conn->prepare("SELECT clave FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$id]);
    $hash = $stmt->fetchColumn();

    if ($hash && password_verify($clave, $hash)) {
        echo "ok";
    } else {
        echo "error";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>