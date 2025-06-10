<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si se han enviado los datos necesarios
    if (isset($_POST['id_perfil'], $_POST['username'], $_POST['password'])) {
        // Capturar los datos del formulario
        $id_perfil = $_POST['id_perfil'];
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Obtener la IP del cliente y dispositivo
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $dispositivo = php_uname('s') . " " . php_uname('r');

        // Conexión a la base de datos
        $conn = new mysqli("localhost", "root", "", "bd_tamanaco");
        if ($conn->connect_error) {
            registrar_actividad($conn, null, "Intento de inicio", "Error de conexión a la base de datos", "validar_usuario", $ip_address, $dispositivo, "fallido", "alta");
            $_SESSION['error_message'] = "Error de conexión a la base de datos.";
            header("Location: iniciar_sesion.php");
            exit();
        }

        // Validar estado del perfil
        $query_perfil = "SELECT nombre_perfil FROM perfiles WHERE id_perfil = ? AND id_status = 1";
        $stmt_perfil = $conn->prepare($query_perfil);
        $stmt_perfil->bind_param("i", $id_perfil);
        $stmt_perfil->execute();
        $result_perfil = $stmt_perfil->get_result();

        if ($result_perfil->num_rows > 0) {
            $row_perfil = $result_perfil->fetch_assoc();
            $nombre_perfil = $row_perfil['nombre_perfil'];

            // Verificar si el usuario pertenece al perfil
            $query_usuario = "
                SELECT 
                    u.id_usuario, u.clave, u.intento_fallidos, u.id_status AS usuario_status, 
                    p.primer_nombre, p.primer_apellido, p.id_status AS persona_status 
                FROM 
                    usuarios u
                INNER JOIN 
                    personas p ON u.id_persona = p.id_persona
                WHERE 
                    u.usuario = ? AND u.id_perfil = ?";
            $stmt_usuario = $conn->prepare($query_usuario);
            $stmt_usuario->bind_param("si", $username, $id_perfil);
            $stmt_usuario->execute();
            $result_usuario = $stmt_usuario->get_result();

            if ($result_usuario->num_rows > 0) {
                $row_usuario = $result_usuario->fetch_assoc();
                $id_usuario = $row_usuario['id_usuario'];
                $intentos_fallidos = $row_usuario['intento_fallidos'];
                $usuario_status = $row_usuario['usuario_status'];

                // Validar estado de la persona
                if ($row_usuario['persona_status'] != 1) {
                    registrar_actividad($conn, $id_usuario, "Intento de inicio", "Usuario inactivo", "validar_usuario", $ip_address, $dispositivo, "fallido", "media");
                    $_SESSION['error_message'] = "Esta persona está inactiva en la empresa.";
                    header("Location: iniciar_sesion.php");
                    exit();
                }

                // Validar estado del usuario
                if ($usuario_status == 8) {
                    registrar_actividad($conn, $id_usuario, "Intento de inicio", "Usuario bloqueado temporalmente", "validar_usuario", $ip_address, $dispositivo, "fallido", "media");
                    $_SESSION['error_message'] = "Este usuario está bloqueado temporalmente, cambie la contraseña.";
                    header("Location: iniciar_sesion.php");
                    exit();
                }
                if ($usuario_status == 9) {
                    registrar_actividad($conn, $id_usuario, "Intento de inicio", "Usuario bloqueado permanentemente", "validar_usuario", $ip_address, $dispositivo, "fallido", "alta");
                    $_SESSION['error_message'] = "Esta persona está bloqueada, por favor contacte al administrador.";
                    header("Location: iniciar_sesion.php");
                    exit();
                }
                if ($usuario_status != 1) {
                    registrar_actividad($conn, $id_usuario, "Intento de inicio", "Usuario inactivo", "validar_usuario", $ip_address, $dispositivo, "fallido", "media");
                    $_SESSION['error_message'] = "Este usuario se encuentra inactivo.";
                    header("Location: iniciar_sesion.php");
                    exit();
                }

                // Verificar contraseña
                if (password_verify($password, $row_usuario['clave'])) {
                    // Contraseña correcta: Reiniciar intentos fallidos
                    $query_reset_intentos = "UPDATE usuarios SET intento_fallidos = 0 WHERE id_usuario = ?";
                    $stmt_reset = $conn->prepare($query_reset_intentos);
                    $stmt_reset->bind_param("i", $id_usuario);
                    $stmt_reset->execute();

                    // Guardar datos en la sesión
                    $_SESSION['id_perfil'] = $id_perfil;
                    $_SESSION['username'] = $username;
                    $_SESSION['nombre_completo'] = $row_usuario['primer_nombre'] . ' ' . $row_usuario['primer_apellido'];
                    $_SESSION['perfil'] = $nombre_perfil;
                    $_SESSION['first_login'] = true; // Marcar como primer inicio de sesión

                    // Registrar actividad exitosa
                    registrar_actividad($conn, $id_usuario, "Inicio de sesión", "Acceso permitido", "validar_usuario", $ip_address, $dispositivo, "exitoso", "media");

                    // Redirigir al dashboard
                    header("Location: dashboard.php");
                    exit();
                } else {
                    // Contraseña incorrecta
                    $intentos_fallidos++;
                    $query_incrementar_intentos = "UPDATE usuarios SET intento_fallidos = ? WHERE id_usuario = ?";
                    $stmt_incrementar = $conn->prepare($query_incrementar_intentos);
                    $stmt_incrementar->bind_param("ii", $intentos_fallidos, $id_usuario);
                    $stmt_incrementar->execute();

                    if ($intentos_fallidos >= 3) {
                        // Bloquear usuario después de 3 intentos fallidos
                        $query_bloquear_usuario = "UPDATE usuarios SET id_status = 8 WHERE id_usuario = ?";
                        $stmt_bloquear = $conn->prepare($query_bloquear_usuario);
                        $stmt_bloquear->bind_param("i", $id_usuario);
                        $stmt_bloquear->execute();

                        registrar_actividad($conn, $id_usuario, "Intento de inicio", "Usuario bloqueado tras múltiples intentos fallidos", "validar_usuario", $ip_address, $dispositivo, "fallido", "alta");
                        $_SESSION['error_message'] = "Este usuario está bloqueado temporalmente, cambie la contraseña.";
                        header("Location: iniciar_sesion.php");
                        exit();
                    }

                    registrar_actividad($conn, $id_usuario, "Intento de inicio", "Contraseña incorrecta", "validar_usuario", $ip_address, $dispositivo, "fallido", "media");
                    $_SESSION['error_message'] = "La contraseña ingresada es incorrecta.";
                    header("Location: iniciar_sesion.php");
                    exit();
                }
            } else {
                // El usuario no pertenece al perfil seleccionado
                registrar_actividad($conn, null, "Intento de inicio", "Acceso denegado: No tiene permisos para este perfil", "validar_usuario", $ip_address, $dispositivo, "fallido", "media");
                $_SESSION['error_message'] = "Lo sentimos, no tienes permisos para acceder a este perfil. Por favor, contacta al administrador si necesitas ayuda.";
                header("Location: iniciar_sesion.php");
                exit();
            }
            $stmt_usuario->close();
        } else {
            // Perfil no válido
            registrar_actividad($conn, null, "Intento de inicio", "Perfil no válido", "validar_usuario", $ip_address, $dispositivo, "fallido", "alta");
            $_SESSION['error_message'] = "Este perfil se encuentra en mantenimiento, contacte al administrador para más información.";
            header("Location: iniciar_sesion.php");
            exit();
        }
        $stmt_perfil->close();
        $conn->close();
    } else {
        $_SESSION['error_message'] = "Por favor, completa todos los campos.";
        header("Location: iniciar_sesion.php");
        exit();
    }
} else {
    $_SESSION['error_message'] = "Acceso no autorizado.";
    header("Location: iniciar_sesion.php");
    exit();
}

/**
 * Función para registrar actividades en la base de datos
 */
function registrar_actividad($conn, $id_usuario, $accion, $actividad, $modulo, $ip_address, $dispositivo, $estado, $importancia) {
    $query = "
        INSERT INTO registro_actividades (id_usuario, accion, actividad, modulo, ip_address, dispositivo, estado, importancia) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    // Verificar si `id_usuario` es NULL
    if (is_null($id_usuario)) {
        // Usar `NULL` explícito para id_usuario
        $stmt->bind_param("ssssssss", $id_usuario, $accion, $actividad, $modulo, $ip_address, $dispositivo, $estado, $importancia);
    } else {
        $stmt->bind_param("isssssss", $id_usuario, $accion, $actividad, $modulo, $ip_address, $dispositivo, $estado, $importancia);
    }

    $stmt->execute();
    $stmt->close(); // Liberar recursos del statement
}