<?php
require_once __DIR__ . '/../app/controllers/MarcaController.php';

$action = $_GET['action'] ?? 'index';
$controller = new MarcaController();

switch ($action) {
    case 'add':
        $controller->add($_POST['nombre'], $_POST['id_status']);
        break;
    case 'update':
        $controller->update($_POST['id'], $_POST['nombre'], $_POST['id_status']);
        break;
    case 'delete':
        $controller->delete($_GET['id']);
        break;
    default:
        $controller->index();
        break;
}
?>