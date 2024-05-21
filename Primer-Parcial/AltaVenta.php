<?php
// AltaVenta.php

class AltaVenta {
    private $heladeriaFilePath = 'heladeria.json';
    private $ventasFilePath = 'ventas.json';
    private $imageDirectory = 'ImagenesDeLaVenta/2024/';
    private $cuponesFilePath = 'cupones.json';

    public function __construct() {
        if (!file_exists($this->heladeriaFilePath)) {
            file_put_contents($this->heladeriaFilePath, json_encode([]));
        }
        if (!file_exists($this->ventasFilePath)) {
            file_put_contents($this->ventasFilePath, json_encode([]));
        }
        if (!file_exists($this->imageDirectory)) {
            mkdir($this->imageDirectory, 0777, true);
        }
        if (!file_exists($this->cuponesFilePath)) {
            file_put_contents($this->cuponesFilePath, json_encode([]));
        }
    }

    public function altaVenta($email, $sabor, $tipo, $cantidad, $imageName, $imageContent, $cuponId = null) {
        $heladeriaData = json_decode(file_get_contents($this->heladeriaFilePath), true);
        $ventaData = json_decode(file_get_contents($this->ventasFilePath), true);
        $cuponData = json_decode(file_get_contents($this->cuponesFilePath), true);

        $itemEncontrado = false;

        foreach ($heladeriaData as &$item) {
            if ($item['sabor'] === $sabor && $item['tipo'] === $tipo) {
                $itemEncontrado = true;
                if ($item['stock'] >= $cantidad) {
                    $item['stock'] -= $cantidad;
                    $ventaId = count($ventaData) + 1;
                    $fecha = date('Y-m-d H:i:s');
                    $numeroPedido = 'PED' . str_pad($ventaId, 5, '0', STR_PAD_LEFT);
                    
                    $descuentoAplicado = 0;
                    if ($cuponId) {
                        foreach ($cuponData as &$cupon) {
                            if ($cupon['id'] === $cuponId && $cupon['estado'] === 'no usado') {
                                $descuentoAplicado = $cupon['porcentajeDescuento'];
                                $cupon['estado'] = 'usado';
                                break;
                            }
                        }
                    }
                    $importeFinal = $this->calcularImporteFinal($item['precio'], $cantidad, $descuentoAplicado);

                    $ventaData[] = [
                        'id' => $ventaId,
                        'email' => $email,
                        'sabor' => $sabor,
                        'tipo' => $tipo,
                        'cantidad' => $cantidad,
                        'fecha' => $fecha,
                        'numeroPedido' => $numeroPedido,
                        'importeFinal' => $importeFinal,
                        'cuponId' => $cuponId,
                        'descuentoAplicado' => $descuentoAplicado
                    ];

                    file_put_contents($this->heladeriaFilePath, json_encode($heladeriaData));
                    file_put_contents($this->ventasFilePath, json_encode($ventaData));
                    
                    $this->saveImage($email, $sabor, $tipo, $fecha, $imageName, $imageContent);
                    
                    file_put_contents($this->cuponesFilePath, json_encode($cuponData));

                    return ['message' => 'Venta registrada con éxito', 'numeroPedido' => $numeroPedido, 'importeFinal' => $importeFinal];
                } else {
                    return ['message' => 'Stock insuficiente'];
                }
            }
        }

        if (!$itemEncontrado) {
            return ['message' => 'Helado no encontrado'];
        }
    }

    private function calcularImporteFinal($precio, $cantidad, $descuento) {
        $importeSinDescuento = $precio * $cantidad;
        
        // Verificar si hay un descuento aplicado
        if ($descuento) {
            $descuentoAplicado = ($importeSinDescuento * $descuento) / 100;
            $importeFinal = $importeSinDescuento - $descuentoAplicado;
        } else {
            // Si no hay descuento aplicado, el importe final es el mismo que el importe sin descuento
            $importeFinal = $importeSinDescuento;
        }
    
        return $importeFinal;
    }

    private function saveImage($email, $sabor, $tipo, $fecha, $imageName, $imageContent) {
        $username = explode('@', $email)[0];
        $filename = $sabor . '_' . $tipo . '_' . $username . '_' . $fecha;
        $filename = preg_replace('/[^a-zA-Z0-9_]/', '', $filename);
        $extension = pathinfo($imageName, PATHINFO_EXTENSION);
        $imagePath = $this->imageDirectory . $filename . '.' . $extension;
        file_put_contents($imagePath, $imageContent);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email'], $_POST['sabor'], $_POST['tipo'], $_POST['cantidad'], $_FILES['image'])) {
        $altaVenta = new AltaVenta();
        $cuponId = isset($_POST['cuponId']) ? $_POST['cuponId'] : null;
        $response = $altaVenta->altaVenta($_POST['email'], $_POST['sabor'], $_POST['tipo'], $_POST['cantidad'], $_FILES['image']['name'], file_get_contents($_FILES['image']['tmp_name']), $cuponId);
        echo json_encode($response);
    } else {
        echo json_encode(['message' => 'Faltan datos o imagen']);
    }
}
?>
