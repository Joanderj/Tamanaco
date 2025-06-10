<?php
session_start();
require_once 'db_connection.php';

// Verifica si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nueva = trim($_POST['password'] ?? '');
    $confirmar = trim($_POST['confirm_password'] ?? '');

    if ($nueva === '' || $confirmar === '') {
        die("Ambos campos son obligatorios.");
    }

    if ($nueva !== $confirmar) {
        die("Las contraseñas no coinciden.");
    }

    // Crea el hash seguro de la contraseña
    $passwordHash = password_hash($nueva, PASSWORD_DEFAULT);
    $idUsuario = $_SESSION['id_usuario'];

    try {
        $stmt = $conn->prepare("UPDATE usuarios SET clave = :clave WHERE id_usuario = :id");
        $stmt->bindParam(':clave', $passwordHash);
        $stmt->bindParam(':id', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();

        echo "<script>alert('Contraseña actualizada exitosamente'); window.location.href = 'perfil.php';</script>";
        exit();

    } catch (PDOException $e) {
        die("Error al actualizar la contraseña: " . $e->getMessage());
    }
} else {
    die("Acceso denegado.");
}
?>
