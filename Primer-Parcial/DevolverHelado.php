<?php
// DevolverHelado.php

class DevolverHelado {
    private $ventasFilePath = 'ventas.json';
    private $devolucionesFilePath = 'devoluciones.json';
    private $cuponesFilePath = 'cupones.json';
    private $imageDirectory = 'ImagenesDeDevoluciones/2024/';

    public function __construct() {
        if (!file_exists($this->ventasFilePath)) {
            file_put_contents($this->ventasFilePath, json_encode([]));
        }
        if (!file_exists($this->devolucionesFilePath)) {
            file_put_contents($this->devolucionesFilePath, json_encode([]));
        }
        if (!file_exists($this->cuponesFilePath)) {
            file_put_contents($this->cuponesFilePath, json_encode([]));
        }
        if (!file_exists($this->imageDirectory)) {
            mkdir($this->imageDirectory, 0777, true);
        }
    }

    public function devolverHelado($numeroPedido, $causa, $image) {
        $ventasData = json_decode(file_get_contents($this->ventasFilePath), true);
        $devolucionesData = json_decode(file_get_contents($this->devolucionesFilePath), true);
        $cuponesData = json_decode(file_get_contents($this->cuponesFilePath), true);

        foreach ($ventasData as $venta) {
            if ($venta['numeroPedido'] === $numeroPedido) {
                $devolucionId = count($devolucionesData) + 1;
                $devolucion = [
                    'id' => $devolucionId,
                    'numeroPedido' => $numeroPedido,
                    'causa' => $causa
                ];
                $devolucionesData[] = $devolucion;
                file_put_contents($this->devolucionesFilePath, json_encode($devolucionesData));

                $this->saveImage($numeroPedido, $devolucionId, $image);

                $cuponId = count($cuponesData) + 1;
                $cupon = [
                    'id' => $cuponId,
                    'devolucion_id' => $devolucionId,
                    'porcentajeDescuento' => rand(10, 50),
                    'estado' => 'no usado'
                ];
                $cuponesData[] = $cupon;
                file_put_contents($this->cuponesFilePath, json_encode($cuponesData));

                return [
                    'message' => 'Devolución registrada y cupón generado',
                    'cupon' => $cupon
                ];
            }
        }
        return ['message' => 'Número de pedido no encontrado'];
    }

    private function saveImage($numeroPedido, $devolucionId, $image) {
        $filename = 'devolucion_' . $numeroPedido . '_' . $devolucionId;
        $filename = preg_replace('/[^a-zA-Z0-9_]/', '', $filename);
        $extension = pathinfo($image['name'], PATHINFO_EXTENSION);
        $imagePath = $this->imageDirectory . $filename . '.' . $extension;
        move_uploaded_file($image['tmp_name'], $imagePath);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['numeroPedido'], $_POST['causa']) && isset($_FILES['image'])) {
        $devolverHelado = new DevolverHelado();
        $response = $devolverHelado->devolverHelado($_POST['numeroPedido'], $_POST['causa'], $_FILES['image']);
        echo json_encode($response);
    } else {
        echo json_encode(['message' => 'Faltan datos o imagen']);
    }
}
?>
