<?php
// api_obtener_reservas.php

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
        require_once "conexion.php";

        $sql = "SELECT r.id_reserva, r.id_libro, l.titulo as nombre_libro, u.nombre, u.apellidos, u.correo, r.estado
                FROM reservas r
                INNER JOIN libros l ON r.id_libro = l.id_libro
                INNER JOIN usuarios u ON r.id_usuario = u.id";

        $st = $con->prepare($sql);

        try {
            $st->execute();
            $reservas = $st->fetchAll(PDO::FETCH_ASSOC);

            if ($reservas) {
                echo json_encode(array("data" => $reservas));
            } else {
                echo json_encode(array("mensaje" => "No se encontraron reservas"));
            }
        } catch (PDOException $e) {
            echo json_encode(array("mensaje" => "Error al obtener las reservas: " . $e->getMessage()));
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
