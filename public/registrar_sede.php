<?php
// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Conexión a la base de datos
    $conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
    if ($conexion->connect_error) {
        die("Error en la conexión: " . $conexion->connect_error);
    }

    // Capturar datos del formulario
    $nombre_sede = $_POST['nombre_sede'] ?? null; // Nombre de la sede
    $id_sucursal_principal = $_POST['sucursal_principal'] ?? null; // Sucursal principal
    $sucursales_vinculadas = $_POST['sucursales'] ?? []; // Sucursales vinculadas (checkboxes seleccionados)

    // Datos fijos o predeterminados
    $id_empresa = 1; // ID de la empresa
    $id_status = 1; // Estado activo por defecto

    // Validación de datos
    if (!empty($nombre_sede)) {
        $conexion->begin_transaction(); // Iniciar una transacción para asegurar atomicidad

        try {
            // Insertar la nueva sede en la tabla `sede`
            $sql_insertar_sede = "
                INSERT INTO sede (id_empresa, id_status, nombre_sede, fecha_creacion, id_sucursal_fija)
                VALUES (?, ?, ?, NOW(), ?)
            ";
            $stmt_sede = $conexion->prepare($sql_insertar_sede);
            $stmt_sede->bind_param("iisi", $id_empresa, $id_status, $nombre_sede, $id_sucursal_principal);
            $stmt_sede->execute();

            // Obtener el ID de la sede recién creada
            $id_sede = $stmt_sede->insert_id;

            // Insertar las vinculaciones en la tabla `sede_sucursal`
            if (!empty($sucursales_vinculadas)) {
                $sql_vincular_sucursales = "
                    INSERT INTO sede_sucursal (id_sede, id_sucursal, fecha_asociacion)
                    VALUES (?, ?, NOW())
                ";
                $stmt_vincular = $conexion->prepare($sql_vincular_sucursales);

                foreach ($sucursales_vinculadas as $id_sucursal) {
                    // Validar que cada ID de sucursal es numérico
                    if (!is_numeric($id_sucursal)) {
                        throw new Exception("ID de sucursal inválido: $id_sucursal");
                    }
                    $stmt_vincular->bind_param("ii", $id_sede, $id_sucursal);
                    $stmt_vincular->execute();
                }
                $stmt_vincular->close();
            }

            // Confirmar la transacción
            $conexion->commit();

            // Mostrar mensaje de éxito
            echo "<p style='color: green;'>Sede registrada correctamente con ID: $id_sede.</p>";
        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            $conexion->rollback();
            echo "<p style='color: red;'>Error: {$e->getMessage()}</p>";
        } finally {
            // Cerrar el statement
            $stmt_sede->close();
        }
    } else {
        echo "<p style='color: red;'>El nombre de la sede es obligatorio.</p>";
    }

    // Cerrar la conexión a la base de datos
    $conexion->close();
} else {
    echo "<p style='color: red;'>Método de solicitud no permitido.</p>";
}
?>