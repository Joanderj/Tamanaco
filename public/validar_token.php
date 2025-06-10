<?php
session_start();
$conn = new mysqli("localhost", "root", "", "bd_tamanaco");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$id_usuario = $_SESSION['id_usuario'] ?? null;
if (!$id_usuario) {
    $_SESSION['error'] = "Sesión expirada.";
    header("Location: verificacion.php");
    exit();
}

$codigo = strtoupper(implode('', [
    $_POST['digit1'] ?? '', $_POST['digit2'] ?? '', $_POST['digit3'] ?? '',
    $_POST['digit4'] ?? '', $_POST['digit5'] ?? '', $_POST['digit6'] ?? ''
]));

// Validar código
$query = "SELECT token_recuperacion, expiracion_token FROM usuarios WHERE id_usuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data || strtoupper($data['token_recuperacion']) !== $codigo) {
    $_SESSION['error'] = "Código incorrecto.";
    header("Location: recuperar_token.php"); 
    exit();
}

if (strtotime($data['expiracion_token']) < time()) {
    $_SESSION['error'] = "El código ha expirado.";
    header("Location: recuperar_token.php");
    exit();
}

// Código válido
// Aquí podrías redirigir al usuario a un formulario de cambio de contraseña
header("Location: nueva_contrasena.php");
exit();
