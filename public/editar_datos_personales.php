<?php
session_start(); // Iniciar la sesión para usar variables de sesión

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: iniciar_sesion.php");
    exit();
}

// Conexión a la base de datos
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');

// Verificar conexión
if ($conexion->connect_error) {
    die("Error en la conexión: " . $conexion->connect_error);
}

// Obtener datos del usuario y la persona asociada
$sql = "SELECT u.*, p.* FROM usuarios u 
        JOIN personas p ON u.id_persona = p.id_persona 
        WHERE u.usuario = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
$perfil = $result->fetch_assoc();
$stmt->close();

if (!$perfil) {
    $_SESSION['error'] = "No se encontraron datos para el usuario.";
    header("Location: datos_personales.php");
    exit();
}

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = $_POST['cedula'] ?? $perfil['cedula'];
    $nacionalidad = $_POST['nacionalidad'] ?? $perfil['nacionalidad'];
    $nombre1 = $_POST['nombre1'] ?? $perfil['primer_nombre'];
    $nombre2 = $_POST['nombre2'] ?? $perfil['segundo_nombre'];
    $apellido1 = $_POST['apellido1'] ?? $perfil['primer_apellido'];
    $apellido2 = $_POST['apellido2'] ?? $perfil['segundo_apellido'];
    $correo = $_POST['correo'] ?? $perfil['correo_electronico'];
    $telefono = $_POST['telefono'] ?? $perfil['telefono'];
    $nacimiento = $_POST['nacimiento'] ?? $perfil['fecha_nacimiento'];
    $edad = $_POST['edad'] ?? $perfil['edad'];
    $genero = $_POST['genero'] ?? $perfil['genero'];
    $direccion = $_POST['direccion'] ?? $perfil['direccion'];

    // Inicializar variables para imagen
    $nombre_imagen = $perfil['nombre_imagen']; // Mantener el nombre de imagen actual
    $url_imagen = $perfil['url']; // Mantener la URL actual

    // Procesamiento de imagen
    if (isset($_FILES['nombre_imagen']) && $_FILES['nombre_imagen']['error'] == UPLOAD_ERR_OK) {
        $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
        $nombre_imagen_original = $_FILES['nombre_imagen']['name'];
        $extension = strtolower(pathinfo($nombre_imagen_original, PATHINFO_EXTENSION));
        
        if (!in_array($extension, $extensiones_permitidas)) {
            $_SESSION['error'] = "Tipo de archivo no permitido. Solo se aceptan: " . implode(', ', $extensiones_permitidas);
            header("Location: datos_empresa.php");
            exit();
        }
        
        $carpeta_destino = 'servidor_img/perfil/';
        if (!is_dir($carpeta_destino)) {
            mkdir($carpeta_destino, 0777, true);
        }
        
        $nombre_imagen = uniqid() . '_' . basename($nombre_imagen_original);
        $ruta_imagen = $carpeta_destino . $nombre_imagen;
        
        if (!move_uploaded_file($_FILES['nombre_imagen']['tmp_name'], $ruta_imagen)) {
            $_SESSION['error'] = "Error al subir la imagen.";
            header("Location: datos_empresa.php");
            exit();
        }
        
        $url_imagen = $ruta_imagen; // Usar ruta relativa en lugar de "../public/"
    }

    // Actualización de datos en ambas tablas con transacción
    $conexion->begin_transaction();
    try {
        // Actualizar datos en `personas`
        $stmt = $conexion->prepare("UPDATE personas SET 
            cedula=?, nacionalidad=?, primer_nombre=?, segundo_nombre=?, 
            primer_apellido=?, segundo_apellido=?, correo_electronico=?, telefono=?, 
            fecha_nacimiento=?, edad=?, genero=?, direccion=? 
            WHERE id_persona=?");
        
        $stmt->bind_param("ssssssssssssi", $cedula, $nacionalidad, $nombre1, $nombre2, 
            $apellido1, $apellido2, $correo, $telefono, $nacimiento, $edad, $genero, 
            $direccion, $perfil['id_persona']);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al actualizar personas: " . $stmt->error);
        }

        // Actualizar imagen en `usuarios`
        $stmt = $conexion->prepare("UPDATE usuarios SET nombre_imagen=?, url=? WHERE id_usuario=?");
        $stmt->bind_param("ssi", $nombre_imagen, $url_imagen, $perfil['id_usuario']);

        if (!$stmt->execute()) {
            throw new Exception("Error al actualizar imagen en usuarios: " . $stmt->error);
        }

        // Registrar actividad en `registro_actividades`
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $dispositivo = $_SERVER['HTTP_USER_AGENT'];
        $accion = 'Edito';
        $actividad = 'Datos Personales';
        $modulo = 'Perfil';

        $stmt = $conexion->prepare("INSERT INTO registro_actividades (id_usuario, accion, actividad, modulo, ip_address, dispositivo) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $perfil['id_usuario'], $accion, $actividad, $modulo, $ip_address, $dispositivo);

        if (!$stmt->execute()) {
            throw new Exception("Error al registrar actividad: " . $stmt->error);
        }

        // Confirmar transacción
        $conexion->commit();
$_SESSION['mensaje'] = "Perfil actualizado correctamente. Cédula: " . $cedula;

        header("Location: datos_personales.php");
        exit();
    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['error'] = "Error: {$e->getMessage()}";
        header("Location: datos_personales.php");
        exit();
    } finally {
        $stmt->close();
        $conexion->close();
    }
}
?>
