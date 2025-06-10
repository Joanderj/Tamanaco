<?php
session_start(); // Iniciar la sesión para usar variables de sesión

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Conexión a la base de datos
    $conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
    if ($conexion->connect_error) {
        die("Error en la conexión: " . $conexion->connect_error);
    }

    // Capturar datos del formulario y manejar valores nulos
    $nombre = !empty($_POST['nombre']) ? $_POST['nombre'] : null; // Nombre de la empresa
    $numerorif = !empty($_POST['numerorif']) ? $_POST['numerorif'] : null; // Número de RIF de la empresa
    $letrarif = !empty($_POST['letrarif']) ? $_POST['letrarif'] : null; // Letra del RIF de la empresa
    $pais = !empty($_POST['pais']) ? $_POST['pais'] : null; // País de la empresa
    $estado = !empty($_POST['estado']) ? $_POST['estado'] : null; // Estado de la empresa
    $direccion = !empty($_POST['direccion']) ? $_POST['direccion'] : null; // Dirección de la empresa
    $tipo = !empty($_POST['tipo']) ? $_POST['tipo'] : null; // Tipo de la empresa

    $id_empresa = 1;
    $id_status = 1;
    $nombre_imagen = null;
    $url_imagen = null;

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
        
        $carpeta_destino = 'servidor_img/home/';
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

    $conexion->begin_transaction();
    
    try {
        // Verificar si la empresa ya existe
        $stmt_check = $conexion->prepare("SELECT id_empresa FROM empresa WHERE id_empresa = ?");
        if (!$stmt_check) {
            throw new Exception("Error al preparar la consulta de verificación: " . $conexion->error);
        }
        $stmt_check->bind_param("i", $id_empresa);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            // Actualizar empresa existente
            $stmt = $conexion->prepare("UPDATE empresa SET nombre=?, numero_rif=?, rif=?, ubicacion_pais_id=?, ubicacion_estado_id=?, direccion=?, tipo_empresa=?, url=?, nombre_imagen=?, status_id=? WHERE id_empresa=?");
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta de actualización: " . $conexion->error);
            }
            $stmt->bind_param("ssssissssii", $nombre, $numerorif, $letrarif, $pais, $estado, $direccion, $tipo, $url_imagen, $nombre_imagen, $id_status, $id_empresa);
            $action = "actualizada";
        } else {
            // Insertar nueva empresa
            $stmt = $conexion->prepare("INSERT INTO empresa (id_empresa, nombre, numero_rif, rif, pais_id_pais, estado_id_estado, direccion, tipo_empresa, status_id, url, nombre_imagen) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta de inserción: " . $conexion->error);
            }
            $stmt->bind_param("issssississ", $id_empresa, $nombre, $numerorif, $letrarif, $pais, $estado, $direccion, $tipo, $id_status, $url_imagen, $nombre_imagen);
            $action = "registrada";
        }

        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }

        $conexion->commit();
        $_SESSION['mensaje'] = "Empresa $action correctamente con ID: $id_empresa.";
        
    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['error'] = "Error: " . $e->getMessage();
    } finally {
        if (isset($stmt_check)) $stmt_check->close();
        if (isset($stmt)) $stmt->close();
        $conexion->close();
    }
    
    header("Location: datos_empresa.php");
    exit();
}
?>

