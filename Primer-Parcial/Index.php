<?php
// index.php

// Recibe todas las peticiones
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestAction = isset($_GET['action']) ? $_GET['action'] : '';

// Administra a qué archivo se debe incluir
switch ($requestAction) {
    case 'alta':
        require_once 'HeladeriaAlta.php';
        break;
    case 'consultar':
        require_once 'HeladoConsultar.php';
        break;
    case 'altaVenta':
        require_once 'AltaVenta.php';
        break;
    case 'consultasVentas':
        require_once 'ConsultasVentas.php';
        break;
    case 'modificarVenta':
        require_once 'ModificarVenta.php';
        break;
    case 'devolverHelado':
        require_once 'DevolverHelado.php';
        break;
    case 'borrarVenta':
        require_once 'borrarVenta.php';
        break;
    case 'consultasDevoluciones':
        require_once 'ConsultasDevoluciones.php';
        break;
    default:
        echo json_encode(['message' => 'Acción no válida']);
        break;
}
?>
