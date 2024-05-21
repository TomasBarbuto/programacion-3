<?php
// HeladoConsultar.php

class HeladoConsultar {
    private $filePath = 'heladeria.json';

    public function consultar($sabor, $tipo) {
        if (!file_exists($this->filePath)) {
            return ['message' => 'Archivo de datos no encontrado'];
        }

        $data = json_decode(file_get_contents($this->filePath), true);
        $existeSabor = false;
        $existeTipo = false;

        foreach ($data as $item) {
            if ($item['sabor'] === $sabor) {
                $existeSabor = true;
                if ($item['tipo'] === $tipo) {
                    $existeTipo = true;
                    return ['message' => 'existe'];
                }
            }
        }

        if (!$existeSabor) {
            return ['message' => 'No existe el sabor'];
        }

        if (!$existeTipo) {
            return ['message' => 'No existe el tipo'];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['sabor'], $_POST['tipo'])) {
        $heladoConsultar = new HeladoConsultar();
        $response = $heladoConsultar->consultar($_POST['sabor'], $_POST['tipo']);
        echo json_encode($response);
    } else {
        echo json_encode(['message' => 'Faltan datos']);
    }
}
?>
