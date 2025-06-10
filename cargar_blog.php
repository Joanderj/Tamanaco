<?php
$conn = new mysqli('localhost', 'root', '', 'bd_tamanaco');
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$sql = "SELECT * FROM blog ORDER BY fecha_blog DESC";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $titulo = htmlspecialchars($row['titulo']);
    $descripcion = htmlspecialchars($row['descripcion']);
    $fecha = date('d/m/Y', strtotime($row['fecha_blog']));
    $imagen = 'public/servidor_img/home/' . $row['nombre_img'];

    echo '
    <div class="swiper-slide">
        <div class="card border-0 mb-2">
            <img class="card-img-top" src="' . $imagen . '" alt="' . $titulo . '">
            <div class="card-body bg-light p-4">
                <h4 class="card-title text-truncate">' . $titulo . '</h4>
                <div class="d-flex mb-3">
                    <small class="mr-2"><i class="fa fa-user text-muted"></i> Admin</small>
                </div>
                <p>' . $descripcion . '</p>
                <span class="text-muted small">Publicado el ' . $fecha . '</span>
            </div>
        </div>
    </div>
    ';
}
$conn->close();
?>
