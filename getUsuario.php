<?php
// api_consultar_usuario.php

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
        // Obtener el ID del usuario de la URL
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $segmentos = explode('/', $url);

        // Eliminar segmentos vacíos
        $segmentos = array_filter($segmentos);

        // Obtener el último segmento (que debería ser el ID del usuario)
        $id_usuario = end($segmentos);

        if (is_numeric($id_usuario) && $id_usuario > 0) {
            require_once "conexion.php";

            // Consultar al usuario por ID
            $consulta = $con->prepare("SELECT * FROM usuarios WHERE id = ?");
            $consulta->bindParam(1, $id_usuario);
            $consulta->execute();

            $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                echo json_encode(array("data" => $usuario));
            } else {
                echo json_encode(array("mensaje" => "Usuario no encontrado"));
            }
        } else {
            echo json_encode(array("mensaje" => "ID de usuario no válido"));
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
?>
