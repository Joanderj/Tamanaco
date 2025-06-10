<?php
// Conexión a la base de datos
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$sql = "SELECT * FROM empresa WHERE id_empresa=1";
$result = $conexion->query($sql);
$empresa = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <!-- Encabezado HTML -->
<head>
    <meta charset="UTF-8">
    <title>Tamanaco Servicios - <?php echo $pagina ?? 'Inicio'; ?></title>
    <link rel="icon" type="image/png" href="public/img/logo2.png">
    <!-- Asegúrate de tener el ícono en la ruta especificada -->
</head>

    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Fuentes Web de Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
   

    <!-- Hoja de Estilos de Iconos -->
     
    <link href="public/lib/fontawesome-free-6.7.2-web/css/all.min.css" rel="stylesheet">
    <!-- Hoja de Estilos de Librerías -->
    <link href="public/lib/animate/animate.min.css" rel="stylesheet">
    <link href="public/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Hoja de Estilos Personalizada de Bootstrap -->
    <link href="public/css/bootstrap.css" rel="stylesheet">

    <!-- Hoja de Estilos de la Plantilla -->
    <link href="public/css/style2.css" rel="stylesheet">
</head>
<style>.text-justify-custom {
    text-align: justify; /* Justifica el texto */

}


</style>

<body>
    <section id="inicio">
  


 <!-- Topbar Start -->
<div class="container-fluid">
    <div style="background-color: #0f0f10 ;" class="row py-2 px-lg-5">
        <div class="col-lg-6 text-center text-lg-left mb-2 mb-lg-0">
            <div class="d-inline-flex align-items-center">
   
            
            </div>
        </div>
        <div class="col-lg-6 text-center text-lg-right">
            <div class="d-inline-flex align-items-center">
                <a style="color: #46494a;" class=" px-3" href="https://www.facebook.com/@tamanacosports/">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a style="color: #46494a;" class=" px-3" href="">
    <i class="fab fa-x-twitter"></i>
</a>
               
                <a  style="color: #46494a;" class=" px-3" href="https://www.instagram.com/tamanacosports/?hl=es">
                    <i class="fab fa-instagram"></i>
                </a>
                <a style="color: #46494a;" class=" pl-3" href="">
                    <i class="fab fa-youtube"></i>
                </a>
            </div>
        </div>
    </div>
    <div style="background-color: #46494a;" class="row py-3 px-lg-5 ">
        <div class="col-lg-4">
    <a href="#" class="navbar-brand d-none d-lg-block">
        <img src="public/img/logo.png" alt="Logo Tamanaco" style="height: 50px;">
    </a>
</div>

        <div class="col-lg-8 text-center text-lg-right">
            <div class="d-inline-flex align-items-center">
                <div class="d-inline-flex flex-column text-center pr-3 border-right">
                    <h6 class="text-danger">Direccion</h6>
                    <p class="m-0 text-white"><?php echo $empresa['direccion']; ?></p>
                </div>
                <div class="d-inline-flex flex-column text-center px-3 border-right">
                    <h6 class="text-danger">Correo</h6>
                    <p class="m-0 text-white"><?php echo $empresa['correo_1']; ?>
                    </p>
                </div>
                <div class="d-inline-flex flex-column text-center pl-3">
                    <h6 class="text-danger">Telefono</h6>
                    <p class="m-0 text-white"><?php echo $empresa['telefono_1']; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Topbar End -->
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>

<!-- Navbar Start -->
<div class="container-fluid p-0">
    <nav class="navbar navbar-expand-lg bg-dark navbar-dark py-3 py-lg-0 px-lg-5">
        <a href="" class=" d-block d-lg-none">
            <img src="public/img/logo.png" alt="Logo Tamanaco" style="height: 50px;">
        </a>
        <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-between px-3" id="navbarCollapse">
            <div class="navbar-nav mr-auto py-0">
                <a href="#inicio" class="nav-item nav-link text-danger">Inicio</a>
                <a href="#acerca" class="nav-item nav-link text-white">Historia</a>
                <a href="#acerca2" class="nav-item nav-link text-white">Acerca de</a>
        <a href="#blog" class="nav-item nav-link text-white">Blog</a>
        <a href="#contacto" class="nav-item nav-link text-white">Contacto</a>
            </div>
            <div class="nav-item">
    <button class="btn btn-danger" onclick="window.location.href='public/seleccionar_perfil.php'">
        Iniciar sesión
    </button>
</div>
        </div>
    </nav>
</div>
<!-- Navbar End -->


   <!-- Inicio del Carrusel -->
<div class="container-fluid px-0 mb-5">
    <div id="header-carousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img class="w-100" src="public/img/carrusel3.jpg" alt="Imagen del carrusel 1">
                <div class="carousel-caption">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-10 text-start">
                                <p class="fs-5 fw-medium text-danger text-uppercase animated slideInRight">55 años acompañando con el Deportista Latinoamericano</p>
                                <h1 class="display-1 text-white mb-5 animated slideInRight">Cree en ti, crece sin límites.</h1>
                                <a href="https://tamanacosports.com/novedades/" class="btn btn-danger py-3 px-5 animated slideInRight">Explorar Más</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <img class="w-100" src="public/img/carrusel2.jpeg" alt="Imagen del carrusel 2">
                <div class="carousel-caption">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-10 text-start">
                                <p class="fs-5 fw-medium text-danger text-uppercase animated slideInRight">Hazlo Posible</p>
                                <h1 class="display-1 text-white mb-5 animated slideInRight">Impulsado por tu pasión, supérate a lo grande.</h1>
                                <a href="https://tamanacosports.com/novedades/" class="btn btn-danger py-3 px-5 animated slideInRight">Explorar Más</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Botón para ir al slide anterior -->
        <button class="carousel-control-prev " type="button" data-bs-target="#header-carousel"
            data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Anterior</span>
        </button>
        <!-- Botón para ir al siguiente slide -->
        <button class="carousel-control-next" type="button" data-bs-target="#header-carousel"
            data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Siguiente</span>
        </button>
    </div>
</div>
<!-- Carousel End -->
</section>

<section id="acerca">


  <!-- About Start -->
<div class="container-fluid py-5" id="about">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-5">
                <img class="img-fluid mb-4 mb-lg-0" src="public/img/tamanaco.png" alt="">
            </div>
            <div class="col-lg-7">
                <h6 class="text-uppercase text-danger mb-3" style="letter-spacing: 3px;">Sobre Nosotros</h6>
                <h1 class="display-4 mb-3"><span class="text-danger">Historia de</span> Tamanaco</h1>
                <p class="text-justify-custom"><?php echo $empresa['historia']; ?>.</p> <!-- Clase personalizada añadida aquí -->
                <a onclick="window.location.href='https://tamanacosports.com/nosotros/'" class="btn btn-danger font-weight-bold py-3 px-5 mt-2" type="button" data-toggle="modal" data-target="#exampleModalLong">Mas informacion</a>
            </div>
        </div>
    </div>
</div>
<!-- About End -->
</section>


<!-- Inicio de Servicios -->
<div class="container-xxl py-5">
    <div class="container">
        <!-- Sección de Título de Servicios -->
        <div class="text-center mx-auto pb-4 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
            <p class="fw-medium text-uppercase text-danger mb-2">Nuestros Mantenimientos</p>
            <h1 class="display-5 mb-4">Capacitación del personal</h1>
        </div>
<div class="row gy-5 gx-4">
       <!-- Curso: Configuración -->
<div class="col-md-6 col-lg-4 wow fadeInUp" data-wow-delay="0.1s">
    <div class="service-item">
        <img class="img-fluid" src="public/img/configuracion.png" alt="Configuración del sistema">
        <div class="service-img">
            <img class="img-fluid" src="public/img/configuracion.jpg" alt="Configuración del sistema">
        </div>
        <div class="service-detail">
            <div class="service-title">
                <hr class="w-25">
                <h3 class="mb-0">Configuración del Sistema</h3>
                <hr class="w-25">
            </div>
            <div class="service-text">
                <p class="text-white mb-3">
                    Aprende cómo registrar la empresa, configurar los parámetros generales y definir las estructuras maestras que permiten que el sistema funcione correctamente desde el inicio.
                </p>
                
            </div>
        </div>
        <a href="curso_configuracion.html" class="btn btn-light btn-sm">Ver capacitación</a>
    </div>
</div>

<!-- Curso: Autogestión -->
<div class="col-md-6 col-lg-4 wow fadeInUp" data-wow-delay="0.3s">
    <div class="service-item">
        <img class="img-fluid" src="public/img/autogestion.png" alt="Autogestión de Usuarios">
        <div class="service-img">
            <img class="img-fluid" src="public/img/autogestion.jpg" alt="Autogestión de Usuarios">
        </div>
        <div class="service-detail">
            <div class="service-title">
                <hr class="w-25">
                <h3 class="mb-0">Autogestión y Permisos</h3>
                <hr class="w-25">
            </div>
            <div class="service-text">
                <p class="text-white mb-3">
                    Descubre cómo registrar empleados, asignar roles, gestionar niveles de usuario y controlar el acceso a cada parte del sistema mediante permisos configurables.
                </p>
                
            </div>
        </div>
        <a href="curso_autogestion.html" class="btn btn-light btn-sm">Ver capacitación</a>
    </div>
</div>


           <!-- Curso: Mantenimiento -->
<div class="col-md-6 col-lg-4 wow fadeInUp" data-wow-delay="0.5s">
    <div class="service-item">
        <img class="img-fluid" src="public/img/mantenimiento.png" alt="Módulo de Mantenimiento">
        <div class="service-img">
            <img class="img-fluid" src="public/img/mantenimiento.jpg" alt="Módulo de Mantenimiento">
        </div>
        <div class="service-detail">
            <div class="service-title">
                <hr class="w-25">
                <h3 class="mb-0">Gestión de Mantenimientos</h3>
                <hr class="w-25">
            </div>
            <div class="service-text">
                <p class="text-white mb-3">
                    Aprende cómo crear planes, tareas preventivas o correctivas, asignar recursos (repuestos, herramientas, personal) y realizar seguimiento a cada ejecución desde el módulo de mantenimiento.
                </p>
                
            </div>
        </div>
        <a href="curso_mantenimiento.html" class="btn btn-light btn-sm">Ver capacitación</a>
    </div>
</div>

        </div>
    </div>
</div>

<!-- Fin de Servicios -->

<section id="acerca2">
<!-- Features Start -->
<div class="container-xxl py-5">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s">
                <div class="position-relative me-lg-4">
                    <!-- Imagen de la característica -->
                    <img class="img-fluid w-100" src="public/img/jajajaa.png" alt="Descripción de la imagen">
                    <span class="position-absolute top-50 start-100 translate-middle bg-white rounded-circle d-none d-lg-block" style="width: 120px; height: 120px;"></span>
                    <!-- Botón para abrir el modal del video -->
                    <button  type="button" class="btn-play " data-bs-toggle="modal"
                        data-src="public/img/tamanaco.mp4" data-bs-target="#videoModal">
                        <span></span>
                    </button>
                </div>
            </div>
            <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.5s">
                <p class="fw-medium text-uppercase text-danger mb-2">¡Por qué elegirnos!</p>
                <h1 class="display-5 mb-4">Bienvenido a <?php echo $empresa['nombre']; ?></h1>
                <p class="text-justify-custom"><?php echo $empresa['mision']; ?></p>
                <div class="row gy-4">
                    <div class="col-12">
                       <div class="col-12">
    <div class="d-flex">
        <div class="flex-shrink-0 btn-lg-square rounded-circle bg-danger">
            <i class="fa fa-check text-white"></i>
        </div>
        <div class="text-justify-custom">
            <h4>Nuestra Visión</h4>
            <span class="text-justify-custom"><?php echo $empresa['vision']; ?></span> <!-- Clase personalizada añadida aquí -->
        </div>
    </div>
</div>

                    <div class="col-12">
                        <div class="d-flex">
                            <div class="flex-shrink-0 btn-lg-square rounded-circle bg-danger">
                                <i class="fa fa-check text-white"></i>
                            </div>
                            <div class="text-justify-custom">
                                <h4>Objetivo General</h4>
                                <spanc><?php echo $empresa['objetivo_general']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="text-justify-custom ">
                            <div class="flex-shrink-0 btn-lg-square rounded-circle bg-danger">
                                <i class="fa fa-check text-white"></i>
                            </div>
                            <div class="ms-4">
                                <h4>Objetivos Específicos</h4>
                                <span><?php echo $empresa['objetivos_especificos']; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Features End -->
</section>

<!-- Video Modal Start -->
<div class="modal modal-video fade" id="videoModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-0">
            <div class="modal-header">
                <h3 class="modal-title" id="exampleModalLabel">Video</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <!-- Relación de aspecto 16:9 -->
                <div class="ratio ratio-16x9">
                    <video class="embed-responsive-item" controls>
                        <source src="public/img/tamanaco.mp4" type="video/mp4">
                        Tu navegador no soporta el elemento de video.
                    </video>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Video Modal End -->
<section id="blog">
   


    <!-- Inicio del Blog -->
<div class="container pt-5">
    <!-- Sección de Título del Blog -->
    <div class="d-flex flex-column text-center mb-5">
        <h4 class="text-secondary mb-3">Blog de Tamanaco</h4>
        <h1 class="display-4 m-0"><span class="text-danger">Actualizaciones</span> Desde el Blog</h1>
    </div>
    <!-- Fila de Artículos del Blog -->
    <div class="row pb-3">
        <!-- Primer Artículo -->
       <div class="swiper mySwiper">
  <div class="swiper-wrapper">
    <?php include 'cargar_blog.php'; ?>
  </div>
  
</div>


    </div>
</div>
<!-- Swiper CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
  var swiper = new Swiper(".mySwiper", {
    slidesPerView: 3,
    spaceBetween: 30,
    loop: false,
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },
    breakpoints: {
      320: { slidesPerView: 1 },
      768: { slidesPerView: 2 },
      1024: { slidesPerView: 3 },
    },
  });
</script>

<!-- Fin del Blog -->
 </section>
<!-- Contacto Inicio -->
 <section id="contacto">

<div class="container-xxl py-5">
    <div class="container">
        <div class="text-center">
            <h1 class="display-4 m-0"><span class="text-danger"></span>Contactanos</h1>
        </div></div>
        <div class="row g-5 justify-content-center mb-5">
            <!-- Sección de contacto telefónico -->
            <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                <div class="bg-light text-center h-100 p-5">
                    <div class="btn-square bg-white rounded-circle mx-auto mb-4" style="width: 90px; height: 90px;">
                        <i class="fa fa-phone-alt fa-2x text-danger"></i>
                    </div>
                    <h4 class="mb-3">Número de Teléfono</h4>
                    <p class="mb-2"><?php echo $empresa['telefono_1']; ?></p>
                    <p class="mb-4"><?php echo $empresa['telefono_2']; ?></p>
                    <a class="btn btn-danger px-4" href="tel:0255-6216166">Llama Ahora <i class="fa fa-arrow-right ms-2"></i></a>
                </div>
            </div>
            <!-- Sección de contacto por correo electrónico -->
            <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.3s">
                <div class="bg-light text-center h-100 p-5">
                    <div class="btn-square bg-white rounded-circle mx-auto mb-4" style="width: 90px; height: 90px;">
                        <i class="fa fa-envelope-open fa-2x text-danger"></i>
                    </div>
                    <h4 class="mb-3">Dirección de Correo Electrónico</h4>
                    <p class="mb-2"><?php echo $empresa['correo_1']; ?></p>
                    <p class="mb-4"><?php echo $empresa['correo_2']; ?>   </p>
                    <a class="btn btn-danger px-4" href="mailto:tamanacoservicio@gmail.com">Envía un Correo <i class="fa fa-arrow-right ms-2"></i></a>
                </div>
            </div>
            <!-- Sección de dirección de la oficina -->
            <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.5s">
                <div class="bg-light text-center h-100 p-5">
                    <div class="btn-square bg-white rounded-circle mx-auto mb-4" style="width: 90px; height: 90px;">
                        <i class="fa fa-map-marker-alt fa-2x text-danger"></i>
                    </div>
                    <h4 class="mb-3">Dirección de la Oficina</h4>
                    <p class="mb-2"></p>
                    <p class="mb-4"><?php echo $empresa['direccion']; ?>.</p>
                    <a class="btn btn-danger px-4" href="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15875.000000000002!2d-69.2045714!3d9.5747336!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8e7dc15f78f2cd93%3A0x22db5d950a926a91!2sTamanaco%20C.A.!5e0!3m2!1ses!2sbd!4v1603794290143!5m2!1ses!2sbd" target="blank">Dirección <i class="fa fa-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>
        <div class="row mb-5">
            <div class="col-12 wow fadeInUp" data-wow-delay="0.1s">
                <iframe class="w-100"
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15875.000000000002!2d-69.2045714!3d9.5747336!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8e7dc15f78f2cd93%3A0x22db5d950a926a91!2sTamanaco%20C.A.!5e0!3m2!1ses!2sbd!4v1603794290143!5m2!1ses!2sbd"
                frameborder="0" style="min-height: 450px; border:0;" allowfullscreen="" aria-hidden="false"
                tabindex="0"></iframe>
            </div>
        </div>
        
        
        
        <div class="row g-5">
            <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s">
                <p class="fw-medium text-uppercase text-danger mb-2">Contáctanos</p>
                <h1 class="display-5 mb-4">Si Tienes Alguna Consulta, No Dudes en Contactarnos</h1>
                <p class="mb-4">¿Tienes alguna pregunta o necesitas más información? ¡Estamos aquí para ayudarte! No dudes en ponerte en contacto con nosotros.</p>
                <div class="row g-4">
                    <div class="col-6">
                        <div class="d-flex">
                            <div class="flex-shrink-0 btn-square bg-danger rounded-circle">
                                <i class="fa fa-phone-alt text-white"></i>
                            </div>
                            <div class="ms-3">
                                <h6>Llámame</h6>
                                <span><?php echo $empresa['telefono_1']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex">
                            <div class="flex-shrink-0 btn-square bg-danger rounded-circle">
                                <i class="fa fa-envelope text-white"></i>
                            </div>
                            <div class="ms-3">
                                <h6>Escríbenos</h6>
                                <span><?php echo $empresa['correo_1']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.5s">
               <form action="enviar_mensaje.php" method="POST">
    <div class="row g-3">
        <div class="col-md-6">
            <div class="form-floating">
                <input type="text" class="form-control" id="name" name="name" placeholder="Tu Nombre" required>
                <label for="name">Tu Nombre</label>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-floating">
                <input type="email" class="form-control" id="email" name="email" placeholder="Tu Correo Electrónico" required>
                <label for="email">Tu Correo Electrónico</label>
            </div>
        </div>
        <div class="col-12">
            <div class="form-floating">
                <input type="text" class="form-control" id="subject" name="subject" placeholder="Asunto" required>
                <label for="subject">Asunto</label>
            </div>
        </div>
        <div class="col-12">
            <div class="form-floating">
                <textarea class="form-control" placeholder="Deja un mensaje aquí" id="message" name="message" style="height: 150px" required></textarea>
                <label for="message">Mensaje</label>
            </div>
        </div>
        <div class="col-12">
            <button class="btn btn-danger py-3 px-5" type="submit">Enviar Mensaje</button>
        </div>
    </div>
</form>

            </div>
        </div>
    </div>
</div>
<!-- Contacto Fin -->

</section>


    <!-- Footer Start -->
    <div class="container-fluid bg-dark text-white py-5 px-sm-3 px-lg-5" style="margin-top: 90px;">
        <div class="row pt-5">
            <div class="col-12 mb-4 px-4">
                <div class="row mb-5 p-4" style="background: rgba(256, 256, 256, .05);">
                    <div class="col-md-4">
                        <div class="text-md-center">
                            <h5 class="text-danger text-uppercase mb-2" style="letter-spacing: 5px;">Direccion</h5>
                            <p class="mb-4 m-md-0"><?php echo $empresa['direccion']; ?>.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-md-center">
                            <h5 class="text-danger text-uppercase mb-2" style="letter-spacing: 5px;">Correo</h5>
                            <p class="mb-4 m-md-0"><?php echo $empresa['correo_1']; ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-md-center">
                            <h5 class="text-danger text-uppercase mb-2" style="letter-spacing: 5px;">Telefono</h5>
                            <p class="m-0"><?php echo $empresa['telefono_1']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7 col-md-12">
                <div class="row">
                    <div class="col-md-6 mb-5">
                        <p>Somos la marca deportiva que representa a los venezolanos, con el firme propósito de creer en ti y apoyarte en tu camino hacia la superación personal y deportiva.</p>
                        <div class="d-flex justify-content-start mt-4">
                            <a class="btn btn-sm btn-outline-light btn-sm-square mr-2" href="<?php echo $empresa['twitter']; ?>"><i class="fab fa-twitter"></i></a>
                            <a class="btn btn-sm btn-outline-light btn-sm-square mr-2" href="<?php echo $empresa['facebook']; ?>"><i class="fab fa-facebook-f"></i></a>
                            <a class="btn btn-sm btn-outline-light btn-sm-square mr-2" href="<?php echo $empresa['instagram']; ?>"><i class="fab fa-linkedin-in"></i></a>
                            <a class="btn btn-sm btn-outline-light btn-sm-square" href="<?php echo $empresa['instagram']; ?>"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="col-md-6 mb-5">
                        <h5 class="text-danger text-uppercase mb-4" style="letter-spacing: 5px;">Destacados</h5>
                        <div class="d-flex flex-column justify-content-start" id="navbarCollapse">
    <a class="text-white btn-scroll mb-2" href="#inicio"><i class="fa fa-angle-right mr-2"></i>Inicio</a>
    <a class="text-white btn-scroll mb-2" href="#acerca"><i class="fa fa-angle-right mr-2"></i>Historia</a>
    <a class="text-white btn-scroll mb-2" href="#acerca2"><i class="fa fa-angle-right mr-2"></i>Acerca de</a>
    <a class="text-white btn-scroll mb-2" href="#blog"><i class="fa fa-angle-right mr-2"></i>Blog</a>
    <a class="text-white btn-scroll mb-2" href="#contacto"><i class="fa fa-angle-right mr-2"></i>Contacto</a>
</div>
                        
                    </div>
                </div>
            </div>
            <div class="col-lg-5 col-md-12 mb-5">
                <h5 class="text-danger text-uppercase mb-4" style="letter-spacing: 5px;">Comprometidos contigo y el deporte.
                </h5>
                <p>En Tamanaco, somos más que una marca deportiva: somos tu aliado para crecer, superar tus límites y alcanzar tus metas, todo con productos de calidad que reflejan el espíritu venezolano.
                </p>
                <div class="w-100">
                    
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid bg-dark text-white text-center border-top py-4 px-sm-3 px-md-5" style="border-color: rgba(256, 256, 256, .05) !important;">
        <p class="m-0 text-white"> Venezuela <a href="https://tamanacosports.com" style="color: red;"> &copy;Tamanaco. </a>|Todos los derechos reservados 2025 | Desarrollado por: <a  style="color: red;" href="desarrolladores.php">joander,brian,luciano,daniel,rafael y sebastian</a></p>
    </div>
    <!-- Footer End -->

    



    <!-- JavaScript Librerias -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

  

    <!-- Javascript -->
    <script src="js/main.js"></script>
</body>

</html>

<?php
// Captura la ruta actual de la URL
$ruta = $_SERVER['REQUEST_URI'];

// Lógica para cargar contenido dinámico basado en la ruta
switch ($ruta) {
    case '/proyecto_tamanaco/tamanaco':
        $contenido = "";
        break;
    case '/proyecto_tamanaco':
    case '/proyecto_tamanaco/':
        $contenido = "";
        break;
    default:
        http_response_code(404);
        $contenido = "";
        break;
}
?>


  

    <div id="contenido">
        <?= $contenido; ?>
    </div>

    <script>
        // Modifica la URL sin recargar la página
        const rutaActual = window.location.pathname;

        // Esto asegura que el nombre del archivo esté oculto
        if (rutaActual.includes('index.php')) {
            const rutaLimpia = rutaActual.replace('index.php', '');
            window.history.replaceState({}, '', rutaLimpia);
        }
    </script>
<script>
    document.querySelectorAll('#navbarCollapse a').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const targetId = this.getAttribute('href').substring(1);
        const targetSection = document.getElementById(targetId);

        window.scrollTo({
            top: targetSection.offsetTop - 50, // Ajusta el valor según el diseño
            behavior: 'smooth'
        });
    });
});
</script>
<?php if (isset($_GET['enviado'])): ?>
    <div id="overlay-alerta" class="position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center"
        style="z-index: 1055; background-color: rgba(0, 0, 0, 0.4);">
        <div id="alerta-envio" class="alert alert-dismissible fade show 
            <?php echo $_GET['enviado'] == 1 ? 'alert-success' : 'alert-danger'; ?> 
            shadow-lg animate__animated animate__fadeInDown"
            style="min-width: 350px; max-width: 90%; font-size: 1.1rem;">
            
            <div class="d-flex align-items-center">
                <i class="bi <?php echo $_GET['enviado'] == 1 ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger'; ?> fs-3 me-3"></i>
                <div>
                    <strong>
                        <?php echo $_GET['enviado'] == 1 ? '¡Éxito!' : 'Error'; ?>
                    </strong><br>
                    <?php echo $_GET['enviado'] == 1 
                        ? 'Tu mensaje fue enviado correctamente. ¡Gracias por contactarnos!' 
                        : 'Hubo un problema al enviar tu mensaje. Inténtalo de nuevo.'; ?>
                </div>
                <button type="button" class="btn-close ms-auto" onclick="cerrarAlerta()"></button>
            </div>
        </div>
    </div>

    <!-- Animación automática para ocultar -->
    <script>
        setTimeout(() => cerrarAlerta(), 5000);
        function cerrarAlerta() {
            const alerta = document.getElementById('alerta-envio');
            const overlay = document.getElementById('overlay-alerta');
            if (alerta) {
                alerta.classList.remove('animate__fadeInDown');
                alerta.classList.add('animate__fadeOutUp');
                setTimeout(() => {
                    if (overlay) overlay.remove();
                }, 500);
            }
        }
    </script>
<?php endif; ?>
