<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/../vendor/autoload.php';

// Recoger datos del formulario
$nacionalidad = $_POST['nacionalidad'] ?? '';
$cedula = $_POST['cedula'] ?? '';
$usuario = $_POST['usuario'] ?? '';

if (empty($nacionalidad) || empty($cedula) || empty($usuario)) {
    $_SESSION['error'] = "Todos los campos son obligatorios.";
    header("Location: verificacion.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "bd_tamanaco");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Buscar persona
$query_persona = "SELECT id_persona, correo_electronico FROM personas WHERE nacionalidad = ? AND cedula = ? AND id_status = 1";
$stmt = $conn->prepare($query_persona);
$stmt->bind_param("ss", $nacionalidad, $cedula);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Datos incorrectos.";
    header("Location: verificacion.php");
    exit();
}
$persona = $result->fetch_assoc();
$id_persona = $persona['id_persona'];
$correo = $persona['correo_electronico'];

// Buscar usuario
$query_usuario = "SELECT id_usuario FROM usuarios WHERE usuario = ? AND id_persona = ? AND id_status = 1";
$stmt2 = $conn->prepare($query_usuario);
$stmt2->bind_param("si", $usuario, $id_persona);
$stmt2->execute();
$result2 = $stmt2->get_result();

if ($result2->num_rows === 0) {
    $_SESSION['error'] = "Datos incorrectos.";
    header("Location: verificacion.php");
    exit();
}

$usuario_data = $result2->fetch_assoc();
$id_usuario = $usuario_data['id_usuario'];

// Crear token y expiración
$token = bin2hex(random_bytes(3)); // Ejemplo: "6c5e3a"
$expiracion = date("Y-m-d H:i:s", strtotime("+10 minutes"));

// Guardar token y expiración
$update_token = "UPDATE usuarios SET token_recuperacion = ?, expiracion_token = ? WHERE id_usuario = ?";
$stmt3 = $conn->prepare($update_token);
$stmt3->bind_param("ssi", $token, $expiracion, $id_usuario);
$stmt3->execute();

// Enviar token por correo
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'tamanacoservicio@gmail.com';
    $mail->Password = 'dutr jtlo aexq psmb';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('tamanacoservicio@gmail.com', 'Recuperacion de Contrasena');
    $mail->addAddress($correo);
    $mail->isHTML(true);
    $mail->Subject = "Codigo de verificacion - Tamanaco";
    $mail->Body = '
    <div style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 30px;">
      <div style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 10px; padding: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.05);">
        <h2 style="color: #2c3e50; text-align: center;">Recuperación de Contraseña</h2>
        <p style="font-size: 15px; color: #333333;">
          Hola, hemos recibido una solicitud para restablecer tu contraseña en <strong>Tamanaco</strong>.
        </p>
        <p style="font-size: 15px; color: #333333;">
          Utiliza el siguiente código para completar el proceso:
        </p>
        <div style="text-align: center; margin: 20px 0;">
          <span style="font-size: 24px; font-weight: bold; color: #007bff; background-color: #e6f0ff; padding: 10px 20px; border-radius: 6px; display: inline-block;">' . $token . '</span>
        </div>
        <p style="font-size: 14px; color: #666666;">
          Este código es válido por 10 minutos. Si no solicitaste este cambio, puedes ignorar este correo.
        </p>
        <hr style="margin: 30px 0;">
        <p style="font-size: 12px; color: #999999; text-align: center;">
          © ' . date("Y") . ' Tamanaco Servicio Técnico. Todos los derechos reservados.
        </p>
      </div>
    </div>';

    $mail->send();

    $_SESSION['id_usuario'] = $id_usuario;
    header("Location: recuperar_token.php");
    exit();

} catch (Exception $e) {
    $_SESSION['error'] = "Error al enviar el correo.";
    header("Location: recuperar_contraseña.php");
    exit();
}
?>
