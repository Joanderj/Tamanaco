<?php
session_start();
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$mensaje_error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo_maquina = trim($_POST['codigo_maquina']);
    $nombre_maquina = trim($_POST['nombre_maquina']);
    $descripcion_funcionamiento = trim($_POST['descripcion_funcionamiento']);
    $elaborada_por = trim($_POST['elaborada_por']);
    $id_marca = intval($_POST['marca']);
    $id_modelo = intval($_POST['modelo']);
    $id_tipo = intval($_POST['tipo']);
    $color = trim($_POST['color']);
    $sugerencia_mantenimiento = trim($_POST['sugerencia_mantenimiento']);
    $id_status = 1;
    $date_created = date('Y-m-d H:i:s');
    $usuario_id = isset($_SESSION['usuario_id']) ? intval($_SESSION['usuario_id']) : null;

    if (empty($codigo_maquina) || empty($nombre_maquina) || !$id_marca || !$id_modelo || !$id_tipo || empty($sugerencia_mantenimiento)) {
        $mensaje_error = "Por favor, complete todos los campos obligatorios.";
    } else {
        $nombre_imagen = null;
        $url_imagen = null;

        // Cargar imagen
        if (isset($_FILES['nombre_imagen']) && $_FILES['nombre_imagen']['error'] === UPLOAD_ERR_OK) {
            $carpeta_destino = 'servidor_img/maquina/';
            if (!is_dir($carpeta_destino)) {
                mkdir($carpeta_destino, 0777, true);
            }

            $nombre_imagen_original = $_FILES['nombre_imagen']['name'];
            $extension = pathinfo($nombre_imagen_original, PATHINFO_EXTENSION);
            $nombre_base = pathinfo($nombre_imagen_original, PATHINFO_FILENAME);
            $nombre_imagen = uniqid() . '_' . $nombre_base . '.' . $extension;
            $ruta_imagen = $carpeta_destino . $nombre_imagen;

            if (!move_uploaded_file($_FILES['nombre_imagen']['tmp_name'], $ruta_imagen)) {
                $mensaje_error = "Error al subir la imagen.";
            } else {
                $url_imagen = "../public/" . $ruta_imagen;
            }
        }

        if (empty($mensaje_error)) {
            $conexion->begin_transaction();

            try {
                // Insertar máquina
                $sql_maquina = "INSERT INTO maquina (
                    codigo_maquina, nombre_maquina, descripcion_funcionamiento, elaborada_por,
                    id_marca, id_modelo, id_tipo, sugerencia_mantenimiento,
                    nombre_imagen, url, color, id_status, date_created
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt_maquina = $conexion->prepare($sql_maquina);
                $stmt_maquina->bind_param(
                    'ssssiiisssssi',
                    $codigo_maquina,
                    $nombre_maquina,
                    $descripcion_funcionamiento,
                    $elaborada_por,
                    $id_marca,
                    $id_modelo,
                    $id_tipo,
                    $sugerencia_mantenimiento,
                    $nombre_imagen,
                    $url_imagen,
                    $color,
                    $id_status,
                    $date_created
                );
                $stmt_maquina->execute();

                $id_maquina = $stmt_maquina->insert_id;

                // Características
                if (!empty($_POST['nombres_caracteristica']) && !empty($_POST['descripciones_caracteristica'])) {
                    foreach ($_POST['nombres_caracteristica'] as $index => $nombre) {
                        $descripcion = $_POST['descripciones_caracteristica'][$index];
                        if (!empty(trim($nombre)) || !empty(trim($descripcion))) {
                            $stmt = $conexion->prepare("INSERT INTO caracteristicas_maquina (id_maquina, nombre_caracteristica, descripcion_caracteristica, fecha_creacion) VALUES (?, ?, ?, ?)");
                            $stmt->bind_param("isss", $id_maquina, $nombre, $descripcion, $date_created);
                            $stmt->execute();
                        }
                    }
                }

                // Especificaciones
                if (!empty($_POST['nombres_especificacion']) && !empty($_POST['descripciones_especificacion'])) {
                    foreach ($_POST['nombres_especificacion'] as $index => $nombre) {
                        $descripcion = $_POST['descripciones_especificacion'][$index];
                        if (!empty(trim($nombre)) || !empty(trim($descripcion))) {
                            $stmt = $conexion->prepare("INSERT INTO especificaciones_maquina (id_maquina, nombre_especificacion, descripcion_especificacion, fecha_creacion) VALUES (?, ?, ?, ?)");
                            $stmt->bind_param("isss", $id_maquina, $nombre, $descripcion, $date_created);
                            $stmt->execute();
                        }
                    }
                }

                // Registrar actividad
                if ($usuario_id !== null) {
                    $descripcion_actividad = "Registró una nueva máquina: $nombre_maquina (Código: $codigo_maquina)";
                    $stmt = $conexion->prepare("INSERT INTO actividad (id_usuario, descripcion, fecha_hora) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $usuario_id, $descripcion_actividad, $date_created);
                    $stmt->execute();
                }

                $conexion->commit();
                $_SESSION['mensaje_exito'] = "La máquina y sus datos se guardaron correctamente.";
                header("Location: maquina.php");
                exit();
            } catch (Exception $e) {
                $conexion->rollback();
                $mensaje_error = "Error al registrar la máquina: " . $e->getMessage();
            }
        }
    }
}

if (!empty($mensaje_error)) {
    $_SESSION['mensaje_error'] = $mensaje_error;
    header("Location: formulario_guardar_maquina.php");
    exit();
}

$conexion->close();
?>
