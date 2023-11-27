<?php
// buscar_libros_por_titulo.php

header('Access-Control-Allow-Headers: access');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo != "GET") {
    http_response_code(400);
    echo json_encode(array("mensaje" => "Método incorrecto, debe ser GET"));
    die();
}

// Token 
define('API_KEY', "e1f602bf73cc96f53c10bb7f7953a438fb7b3c0a");
$headers = apache_request_headers();

if (isset($headers["authorization"])) {
    if ($headers["authorization"] == 'e1f602bf73cc96f53c10bb7f7953a438fb7b3c0a') {
        $url = parse_url($_SERVER['REQUEST_URI']);
        $params = array();
        parse_str($url['query'], $params);

        if (isset($params['titulo']) && !empty($params['titulo'])) {
            $titulo = '%' . $params['titulo'] . '%'; // Agrega comodines para coincidencias parciales
            require_once "conexion.php";
            $sql = "SELECT * FROM libros WHERE titulo LIKE ?";
            $st = $con->prepare($sql);
            $st->bindParam(1, $titulo);

            try {
                if ($st->execute()) {
                    $libros = $st->fetchAll(PDO::FETCH_ASSOC);
                    echo json_encode(array("data" => $libros));
                } else {
                    echo json_encode(array("mensaje" => "Error al buscar libros por título"));
                }
            } catch (PDOException $e) {
                echo json_encode(array("mensaje" => "Error al buscar libros por título: " . $e->getMessage()));
            }
        } else {
            echo json_encode(array("mensaje" => "Falta el parámetro 'titulo' en la petición"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("mensaje" => "API_KEY incorrecto"));
        die();
    }
} else {
    http_response_code(400);
    echo json_encode(array("mensaje" => "Falta el API_KEY"));
    die();
}

// Cerrar la conexión
$con = null;
?>
