<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['name'] ?? '';
    $correo = $_POST['email'] ?? '';
    $asunto = $_POST['subject'] ?? '';
    $mensaje = $_POST['message'] ?? '';

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'tamanacoservicio@gmail.com';
        $mail->Password = 'dutr jtlo aexq psmb';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('tamanacoservicio@gmail.com', 'Formulario Web');
        $mail->addAddress('tamanacoservicio@gmail.com');

        $mail->isHTML(true);
        $mail->Subject = "Formulario Web - $asunto";

        // Correo con diseño atractivo
        $mail->Body = '
        <div style="font-family: Arial, sans-serif; background-color: #f8f9fa; padding: 20px;">
            <div style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; padding: 30px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                <h2 style="color: #0d6efd; margin-bottom: 20px;">Nuevo mensaje desde el formulario web</h2>

                <p style="font-size: 15px; color: #333;"><strong>Nombre:</strong> ' . htmlspecialchars($nombre) . '</p>
                <p style="font-size: 15px; color: #333;"><strong>Correo:</strong> ' . htmlspecialchars($correo) . '</p>
                <p style="font-size: 15px; color: #333;"><strong>Asunto:</strong> ' . htmlspecialchars($asunto) . '</p>

                <hr style="margin: 20px 0;">

                <p style="font-size: 15px; color: #333;"><strong>Mensaje:</strong></p>
                <div style="background-color: #f1f1f1; padding: 15px; border-radius: 5px; color: #444;">
                    ' . nl2br(htmlspecialchars($mensaje)) . '
                </div>

                <hr style="margin: 30px 0;">
                <p style="font-size: 12px; color: #999; text-align: center;">Este mensaje fue enviado desde el sitio web de Tamanaco.</p>
            </div>
        </div>';

        $mail->send();
        header("Location: index.php?enviado=1");
        exit();
    } catch (Exception $e) {
        header("Location: index.php?enviado=0");
        exit();
    }
}
?>
