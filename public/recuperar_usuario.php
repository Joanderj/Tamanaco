<?php
session_start();

// Verificar si hay un mensaje de error o si se envió un perfil
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['perfil'])) {
        $error_message = "Debe seleccionar un perfil antes de continuar.";
    } else {
        $id_perfil = $_POST['perfil'];
        $_SESSION['id_perfil'] = $id_perfil;

        // Conexión a la base de datos
        $conn = new mysqli("localhost", "root", "", "bd_tamanaco");
        if ($conn->connect_error) {
            die("Error de conexión: " . $conn->connect_error);
        }
 
        // Obtener el nombre del perfil
        $query = "SELECT nombre_perfil FROM perfiles WHERE id_perfil = ? AND id_status = 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_perfil);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $nombre_perfil = $row['nombre_perfil'];
            $_SESSION['perfil'] = $nombre_perfil; // Guardar perfil en sesión
        } else {
            $error_message = "El perfil seleccionado no es válido o está en mantenimiento.";
        }

        // Cerrar conexión
        $stmt->close();
        $conn->close();
    }
} else {
    // Si es la primera visita o si ya se seleccionó un perfil
    $id_perfil = isset($_SESSION['id_perfil']) ? $_SESSION['id_perfil'] : null;
    $nombre_perfil = isset($_SESSION['perfil']) ? $_SESSION['perfil'] : "Seleccione un perfil para continuar";
    unset($_SESSION['error_message']); // Limpiar mensaje de error
}
?>