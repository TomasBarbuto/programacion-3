<?php
// borrarVenta.php

class BorrarVenta {
    private $ventasFilePath = 'ventas.json';
    private $backupImageDirectory = 'ImagenesBackupVentas/2024/';

    public function __construct() {
        if (!file_exists($this->ventasFilePath)) {
            file_put_contents($this->ventasFilePath, json_encode([]));
        }
        if (!file_exists($this->backupImageDirectory)) {
            mkdir($this->backupImageDirectory, 0777, true);
        }
    }

    public function borrarVenta($numeroPedido) {
        $ventasData = json_decode(file_get_contents($this->ventasFilePath), true);

        foreach ($ventasData as &$venta) {
            if ($venta['numeroPedido'] === $numeroPedido) {
                $venta['soft_deleted'] = true;

                file_put_contents($this->ventasFilePath, json_encode($ventasData));

                $this->moveImageToBackup($venta);

                return ['message' => 'Venta eliminada (soft-delete) y imagen movida a respaldo'];
            }
        }

        return ['message' => 'Número de pedido no encontrado'];
    }

    private function moveImageToBackup($venta) {
        $imageFilename = $venta['sabor'] . '_' . $venta['tipo'] . '_' . $venta['vaso'] . '_' . explode('@', $venta['email'])[0] . '_' . $venta['fecha'];
        $imageFilename = preg_replace('/[^a-zA-Z0-9_]/', '', $imageFilename);

        $sourceDirectory = 'ImagenesDeLaVenta/2024/';
        $extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        foreach ($extensions as $extension) {
            $sourcePath = $sourceDirectory . $imageFilename . '.' . $extension;
            if (file_exists($sourcePath)) {
                $destinationPath = $this->backupImageDirectory . $imageFilename . '.' . $extension;
                rename($sourcePath, $destinationPath);
                break;
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);

    if (isset($_DELETE['numeroPedido'])) {
        $borrarVenta = new BorrarVenta();
        $response = $borrarVenta->borrarVenta($_DELETE['numeroPedido']);
        echo json_encode($response);
    } else {
        echo json_encode(['message' => 'Falta número de pedido']);
    }
}
?>
