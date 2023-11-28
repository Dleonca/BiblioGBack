<?php
// api_obtener_reserva_por_id.php

/*header('Access-Control-Allow-Headers: access');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');*/
// Permitir solicitudes desde cualquier origen
header("Access-Control-Allow-Origin: *");
// Permitir los métodos HTTP especificados
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
// Verificar si la solicitud es OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // Permitir los encabezados solicitados
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    // Respondemos con OK (200) para la solicitud preflight
    http_response_code(200);
    exit();
}
$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo != "GET") {
    http_response_code(400);
    echo json_encode(array("mensaje" => "Método incorrecto, debe ser GET"));
    die();
}

// Token 
define('API_KEY', "e1f602bf73cc96f53c10bb7f7953a438fb7b3c0a");
$headers = apache_request_headers();

if (isset($headers["Authorization"])) {
    if ($headers["Authorization"] == 'e1f602bf73cc96f53c10bb7f7953a438fb7b3c0a') {
        require_once "conexion.php";

        $url = $_SERVER['REQUEST_URI'];
        $url_array = explode('/', $url);
        $id_usuario = end($url_array);

        if (!empty($id_usuario)) {
            $sql = "SELECT r.id_reserva, r.id_libro, l.titulo as nombre_libro, u.nombre, u.apellidos, u.correo, r.estado
                    FROM reservas r
                    INNER JOIN libros l ON r.id_libro = l.id_libro
                    INNER JOIN usuarios u ON r.id_usuario = u.id
                    WHERE r.id_usuario = ?";

            $st = $con->prepare($sql);

            try {
                $st->bindParam(1, $id_usuario, PDO::PARAM_INT);
                $st->execute();
                //$reserva = $st->fetch(PDO::FETCH_ASSOC);
                $reserva = $st->fetchAll(PDO::FETCH_ASSOC);

                if ($reserva) {
                    echo json_encode(array("data" => $reserva));
                } else {
                    echo json_encode(array("mensaje" => "No se encontró la reserva con el ID especificado"));
                }
            } catch (PDOException $e) {
                echo json_encode(array("mensaje" => "Error al obtener la reserva: " . $e->getMessage()));
            }
        } else {
            echo json_encode(array("mensaje" => "Falta el parámetro 'id_reserva' en la petición"));
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
