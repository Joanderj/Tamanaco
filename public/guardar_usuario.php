<?php
session_start();

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Variables para los mensajes
$mensaje_error = "";

// Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar y validar los datos enviados
    $id_persona = intval($_POST['empleado']);
    $id_perfil = intval($_POST['rol']);
    $usuario = $conexion->real_escape_string(trim($_POST['usuario']));
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT); // 🔒 Encriptación segura
    $id_status = 1; // Estado automático
    $fecha_creacion = date('Y-m-d H:i:s');

    // Validar campos requeridos
    if (empty($id_persona) || empty($id_perfil) || empty($usuario) || empty($_POST['password'])) {
        $mensaje_error = "Por favor, complete todos los campos.";
    } else {
        // Subida de imagen (opcional)
        $nombre_imagen = null;
        $url_imagen = null;

        if (isset($_FILES['nombre_imagen']) && $_FILES['nombre_imagen']['error'] === UPLOAD_ERR_OK) {
            $carpeta_destino = __DIR__ . '/servidor_img/perfil/';

            // Crear la carpeta si no existe
            if (!is_dir($carpeta_destino)) {
                mkdir($carpeta_destino, 0777, true);
            }

            // Definir nombre único para la imagen
            $nombre_imagen_original = $_FILES['nombre_imagen']['name'];
            $extension = pathinfo($nombre_imagen_original, PATHINFO_EXTENSION);
            $nombre_imagen = uniqid() . '.' . $extension;
            $ruta_imagen = $carpeta_destino . $nombre_imagen;

            // 🚀 Comprobación de errores al mover el archivo
            if (!move_uploaded_file($_FILES['nombre_imagen']['tmp_name'], $ruta_imagen)) {
                $mensaje_error = "Error al subir la imagen.";
            } else {
                $url_imagen = 'servidor_img/perfil/' . $nombre_imagen;
            }
        }

        // Verificar si el usuario ya existe dentro del mismo perfil
        $queryVerificar = $conexion->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = ? AND id_perfil = ?");
        $queryVerificar->bind_param("si", $usuario, $id_perfil);
        $queryVerificar->execute();
        $queryVerificar->bind_result($existe);
        $queryVerificar->fetch();
        $queryVerificar->close();

        if ($existe > 0) {
            $mensaje_error = "El usuario ya existe en este perfil.";
        }

        // Insertar usuario en la tabla si no hay errores
        if (empty($mensaje_error)) {
            $queryInsertar = $conexion->prepare("INSERT INTO usuarios (id_persona, id_perfil, usuario, clave, nombre_imagen, url, id_status, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $queryInsertar->bind_param("iissssis", $id_persona, $id_perfil, $usuario, $password, $nombre_imagen, $url_imagen, $id_status, $fecha_creacion);

            if ($queryInsertar->execute()) {
                $_SESSION['mensaje_exito'] = "✅ Usuario registrado correctamente.";
                header("Location: usuario.php");
                exit();
            } else {
                $mensaje_error = "Error al guardar usuario: " . $queryInsertar->error;
            }

            $queryInsertar->close();
        }
    }
}

// Manejo de error y redirección
if (!empty($mensaje_error)) {
    $_SESSION['mensaje_error'] = "❌ " . $mensaje_error;
    header("Location: formulario_guardar_usuario.php");
    exit();
}

// Cerrar conexión
$conexion->close();
?>