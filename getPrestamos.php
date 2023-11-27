<?php
// listar_prestamos.php

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

        $sql = "SELECT prestamos.id_prestamo, prestamos.estado, prestamos.desde, prestamos.hasta, libros.titulo, usuarios.nombre, usuarios.apellidos
                FROM prestamos
                INNER JOIN reservas ON prestamos.id_reserva = reservas.id_reserva
                INNER JOIN libros ON prestamos.id_libro = libros.id_libro
                INNER JOIN usuarios ON reservas.id_usuario = usuarios.id";

        $stmt = $con->prepare($sql);
        $stmt->execute();
        $prestamos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(array("data" => $prestamos));
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
