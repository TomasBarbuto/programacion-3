<?php

class Heladeria
{
    private $filePath = 'heladeria.json';
    private $imageDirectory = 'ImagenesDeHelados/2024/';

    public function __construct()
    {
        if (!file_exists($this->filePath)) {
            file_put_contents($this->filePath, json_encode([]));
        }
        if (!file_exists($this->imageDirectory)) {
            mkdir($this->imageDirectory, 0777, true);
        }
    }

    public function alta($sabor, $precio, $tipo, $vaso, $stock, $image)
    {
        $data = json_decode(file_get_contents($this->filePath), true);
        $id = 1;

        foreach ($data as &$item) {
            if ($item['sabor'] == $sabor && $item['tipo'] == $tipo) {
                $item['precio'] = $precio;
                $item['stock'] += $stock;
                $this->saveImage($sabor, $tipo, $image);
                file_put_contents($this->filePath, json_encode($data));
                return;
            }
            $id = max($id, $item['id'] + 1);
        }

        $data[] = [
            'id' => $id,
            'sabor' => $sabor,
            'precio' => $precio,
            'tipo' => $tipo,
            'vaso' => $vaso,
            'stock' => $stock
        ];

        $this->saveImage($sabor, $tipo, $image);
        file_put_contents($this->filePath, json_encode($data));
    }

    private function saveImage($sabor, $tipo, $image)
    {
        $imagePath = $this->imageDirectory . $sabor . '_' . $tipo . '.' . pathinfo($image['name'], PATHINFO_EXTENSION);
        move_uploaded_file($image['tmp_name'], $imagePath);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['sabor'], $_POST['precio'], $_POST['tipo'], $_POST['vaso'], $_POST['stock']) && isset($_FILES['image'])) {
        $heladeria = new Heladeria();
        $heladeria->alta($_POST['sabor'], $_POST['precio'], $_POST['tipo'], $_POST['vaso'], $_POST['stock'], $_FILES['image']);
        echo json_encode(['message' => 'Helado agregado/actualizado con éxito']);
    } else {
        echo json_encode(['message' => 'Faltan datos o imagen']);
    }
}
