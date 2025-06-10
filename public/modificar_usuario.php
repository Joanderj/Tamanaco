<?php
require_once 'db_connection.php'; // Asegúrate de tener tu conexión a la BD

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_usuario = $_POST["id_usuario"];
    $usuario = $_POST["usuario"];
    $id_perfil = $_POST["id_perfil"];
    $id_status = $_POST["id_status"];
    $url = $_POST["url"] ?? null;
    $clave = trim($_POST["password"]);

    // Reiniciar intentos fallidos y de bloqueo
    $intento_fallidos = 0;
    $intento_bloqueo = 0;

    // Imagen (si se sube una nueva)
    $nombre_imagen = null;
    if (isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] === 0) {
        $nombre_imagen = time() . '_' . basename($_FILES["imagen"]["name"]);
        $ruta_destino = "imagenes/usuarios/" . $nombre_imagen;
        move_uploaded_file($_FILES["imagen"]["tmp_name"], $ruta_destino);
    }

    // Armar sentencia SQL dinámica
    $sql = "UPDATE usuarios SET 
                usuario = ?,
                id_perfil = ?,
                id_status = ?,
                intento_fallidos = ?,
                intento_bloqueo = ?";

    $parametros = [$usuario, $id_perfil, $id_status, $intento_fallidos, $intento_bloqueo];

    // Si se subió imagen
    if ($nombre_imagen) {
        $sql .= ", nombre_imagen = ?";
        $parametros[] = $nombre_imagen;
    }

    // Si se proporcionó una nueva URL
    if (!empty($url)) {
        $sql .= ", url = ?";
        $parametros[] = $url;
    }

    // Si se proporciona una nueva contraseña
    if (!empty($clave)) {
        $clave_hash = password_hash($clave, PASSWORD_DEFAULT);
        $sql .= ", clave = ?";
        $parametros[] = $clave_hash;
    }

    $sql .= " WHERE id_usuario = ?";
    $parametros[] = $id_usuario;

    // Ejecutar sentencia
    $stmt = $conn->prepare($sql);
    if ($stmt->execute($parametros)) {
        echo "<script>alert('Usuario actualizado correctamente'); window.location.href = 'usuario.php';</script>";
    } else {
        echo "<script>alert('Error al actualizar el usuario'); window.history.back();</script>";
    }
} else {
    echo "Acceso no permitido.";
}
?>
