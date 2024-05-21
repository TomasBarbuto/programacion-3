<?php
// ModificarVenta.php

class ModificarVenta {
    private $ventasFilePath = 'ventas.json';
    private $heladeriaFilePath = 'heladeria.json';

    public function __construct() {
        if (!file_exists($this->ventasFilePath)) {
            file_put_contents($this->ventasFilePath, json_encode([]));
        }
        if (!file_exists($this->heladeriaFilePath)) {
            file_put_contents($this->heladeriaFilePath, json_encode([]));
        }
    }

    public function modificarVenta($numeroPedido, $email, $sabor, $tipo, $vaso, $cantidad) {
        $ventasData = json_decode(file_get_contents($this->ventasFilePath), true);
        $heladeriaData = json_decode(file_get_contents($this->heladeriaFilePath), true);

        foreach ($ventasData as &$venta) {
            if ($venta['numeroPedido'] === $numeroPedido) {
                // Revertir stock de la venta anterior
                foreach ($heladeriaData as &$item) {
                    if ($item['sabor'] === $venta['sabor'] && $item['tipo'] === $venta['tipo']) {
                        $item['stock'] += $venta['cantidad'];
                    }
                }

                // Verificar si hay suficiente stock para la nueva cantidad
                foreach ($heladeriaData as &$item) {
                    if ($item['sabor'] === $sabor && $item['tipo'] === $tipo) {
                        if ($item['stock'] >= $cantidad) {
                            $item['stock'] -= $cantidad;

                            // Actualizar la venta
                            $venta['email'] = $email;
                            $venta['sabor'] = $sabor;
                            $venta['tipo'] = $tipo;
                            $venta['vaso'] = $vaso;
                            $venta['cantidad'] = $cantidad;

                            file_put_contents($this->ventasFilePath, json_encode($ventasData));
                            file_put_contents($this->heladeriaFilePath, json_encode($heladeriaData));

                            return ['message' => 'Venta modificada con éxito'];
                        } else {
                            return ['message' => 'Stock insuficiente'];
                        }
                    }
                }
                return ['message' => 'Helado no encontrado'];
            }
        }
        return ['message' => 'Número de pedido no encontrado'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    parse_str(file_get_contents("php://input"), $_PUT);

    if (isset($_PUT['numeroPedido'], $_PUT['email'], $_PUT['sabor'], $_PUT['tipo'], $_PUT['vaso'], $_PUT['cantidad'])) {
        $modificarVenta = new ModificarVenta();
        $response = $modificarVenta->modificarVenta($_PUT['numeroPedido'], $_PUT['email'], $_PUT['sabor'], $_PUT['tipo'], $_PUT['vaso'], $_PUT['cantidad']);
        echo json_encode($response);
    } else {
        echo json_encode(['message' => 'Faltan datos']);
    }
}
?>
