<?php
// ConsultasDevoluciones.php

class ConsultasDevoluciones {
    private $devolucionesFilePath = 'devoluciones.json';
    private $cuponesFilePath = 'cupones.json';

    public function __construct() {
        if (!file_exists($this->devolucionesFilePath)) {
            file_put_contents($this->devolucionesFilePath, json_encode([]));
        }
        if (!file_exists($this->cuponesFilePath)) {
            file_put_contents($this->cuponesFilePath, json_encode([]));
        }
    }

    public function listarDevolucionesConCupones() {
        $devolucionesData = json_decode(file_get_contents($this->devolucionesFilePath), true);
        $cuponesData = json_decode(file_get_contents($this->cuponesFilePath), true);
        $devolucionesConCupones = [];

        foreach ($devolucionesData as $devolucion) {
            foreach ($cuponesData as $cupon) {
                if ($cupon['devolucion_id'] === $devolucion['id']) {
                    $devolucion['cupon'] = $cupon;
                    $devolucionesConCupones[] = $devolucion;
                    break;
                }
            }
        }

        return ['devolucionesConCupones' => $devolucionesConCupones];
    }

    public function listarCupones() {
        $cuponesData = json_decode(file_get_contents($this->cuponesFilePath), true);
        return ['cupones' => $cuponesData];
    }

    public function listarDevolucionesYCupones() {
        $devolucionesData = json_decode(file_get_contents($this->devolucionesFilePath), true);
        $cuponesData = json_decode(file_get_contents($this->cuponesFilePath), true);
        $devolucionesYCupones = [];

        foreach ($devolucionesData as $devolucion) {
            $devolucionConCupon = $devolucion;
            $devolucionConCupon['cupon'] = [];

            foreach ($cuponesData as $cupon) {
                if ($cupon['devolucion_id'] === $devolucion['id']) {
                    $devolucionConCupon['cupon'] = $cupon;
                    break;
                }
            }

            $devolucionesYCupones[] = $devolucionConCupon;
        }

        return ['devolucionesYCupones' => $devolucionesYCupones];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $consultasDevoluciones = new ConsultasDevoluciones();

    if (isset($_GET['consulta'])) {
        $consulta = $_GET['consulta'];

        switch ($consulta) {
            case 'devolucionesConCupones':
                $response = $consultasDevoluciones->listarDevolucionesConCupones();
                break;
            case 'cupones':
                $response = $consultasDevoluciones->listarCupones();
                break;
            case 'devolucionesYCupones':
                $response = $consultasDevoluciones->listarDevolucionesYCupones();
                break;
            default:
                $response = ['message' => 'Consulta no válida'];
                break;
        }

        echo json_encode($response, JSON_PRETTY_PRINT);
    } else {
        echo json_encode(['message' => 'Falta especificar la consulta'], JSON_PRETTY_PRINT);
    }
}
?>
