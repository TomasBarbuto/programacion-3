<?php
// ConsultasVentas.php

class ConsultasVentas {
    private $ventasFilePath = 'ventas.json';

    public function __construct() {
        if (!file_exists($this->ventasFilePath)) {
            file_put_contents($this->ventasFilePath, json_encode([]));
        }
    }

    public function cantidadVendidaPorDia($fecha = null) {
        if (!$fecha) {
            $fecha = date('Y-m-d', strtotime('yesterday'));
        }

        $data = json_decode(file_get_contents($this->ventasFilePath), true);
        $cantidadVendida = 0;

        foreach ($data as $venta) {
            if (strpos($venta['fecha'], $fecha) === 0) {
                $cantidadVendida += $venta['cantidad'];
            }
        }

        if ($cantidadVendida > 0) {
            return ['message' => 'Cantidad vendida por día', 'fecha' => $fecha, 'cantidadVendida' => $cantidadVendida];
        } else {
            return ['message' => 'No se encontraron ventas para el día especificado', 'fecha' => $fecha];
        }
    }

    public function listadoVentasPorUsuario($email) {
        $data = json_decode(file_get_contents($this->ventasFilePath), true);
        $ventasUsuario = array_filter($data, fn($venta) => $venta['email'] === $email);

        if (count($ventasUsuario) > 0) {
            return ['message' => 'Listado de ventas por usuario', 'usuario' => $email, 'ventas' => array_values($ventasUsuario)];
        } else {
            return ['message' => 'No se encontraron ventas para el usuario especificado', 'usuario' => $email];
        }
    }

    public function listadoVentasEntreFechas($fechaInicio, $fechaFin) {
        $data = json_decode(file_get_contents($this->ventasFilePath), true);
        $ventasEntreFechas = array_filter($data, function($venta) use ($fechaInicio, $fechaFin) {
            return $venta['fecha'] >= $fechaInicio && $venta['fecha'] <= $fechaFin;
        });

        usort($ventasEntreFechas, fn($a, $b) => strcmp($a['sabor'], $b['sabor']));

        if (count($ventasEntreFechas) > 0) {
            return ['message' => 'Listado de ventas entre fechas', 'fechaInicio' => $fechaInicio, 'fechaFin' => $fechaFin, 'ventas' => $ventasEntreFechas];
        } else {
            return ['message' => 'No se encontraron ventas entre las fechas especificadas', 'fechaInicio' => $fechaInicio, 'fechaFin' => $fechaFin];
        }
    }

    public function listadoVentasPorSabor($sabor) {
        $data = json_decode(file_get_contents($this->ventasFilePath), true);
        $ventasPorSabor = array_filter($data, fn($venta) => $venta['sabor'] === $sabor);

        if (count($ventasPorSabor) > 0) {
            return ['message' => 'Listado de ventas por sabor', 'sabor' => $sabor, 'ventas' => array_values($ventasPorSabor)];
        } else {
            return ['message' => 'No se encontraron ventas para el sabor especificado', 'sabor' => $sabor];
        }
    }

    public function listadoVentasPorVaso($vaso) {
        $data = json_decode(file_get_contents($this->ventasFilePath), true);
        $ventasPorVaso = array_filter($data, fn($venta) => $venta['vaso'] === $vaso);

        if (count($ventasPorVaso) > 0) {
            return ['message' => 'Listado de ventas por vaso', 'vaso' => $vaso, 'ventas' => array_values($ventasPorVaso)];
        } else {
            return ['message' => 'No se encontraron ventas para el tipo de vaso especificado', 'vaso' => $vaso];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $consultasVentas = new ConsultasVentas();

    if (isset($_GET['fecha'])) {
        $response = $consultasVentas->cantidadVendidaPorDia($_GET['fecha']);
    } elseif (isset($_GET['usuario'])) {
        $response = $consultasVentas->listadoVentasPorUsuario($_GET['usuario']);
    } elseif (isset($_GET['fechaInicio'], $_GET['fechaFin'])) {
        $response = $consultasVentas->listadoVentasEntreFechas($_GET['fechaInicio'], $_GET['fechaFin']);
    } elseif (isset($_GET['sabor'])) {
        $response = $consultasVentas->listadoVentasPorSabor($_GET['sabor']);
    } elseif (isset($_GET['vaso'])) {
        $response = $consultasVentas->listadoVentasPorVaso($_GET['vaso']);
    } else {
        $response = ['message' => 'Consulta no válida'];
    }

    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>
