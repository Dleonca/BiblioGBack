<?php
// api_registrar_reserva.php

header('Access-Control-Allow-Headers: access');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo != "POST") {
    http_response_code(400);
    echo json_encode(array("mensaje" => "Método incorrecto, debe ser POST"));
    die();
}

// Token 
define('API_KEY', "e1f602bf73cc96f53c10bb7f7953a438fb7b3c0a");
$headers = apache_request_headers();

if (isset($headers["authorization"])) {
    if ($headers["authorization"] == 'e1f602bf73cc96f53c10bb7f7953a438fb7b3c0a') {
        $json_data = file_get_contents("php://input");
        $data = json_decode($json_data, true);

        if (isset($data["id_usuario"]) && isset($data["id_libro"]) && isset($data["estado"])) {
            $id_usuario = $data["id_usuario"];
            $id_libro = $data["id_libro"];
            $estado = $data["estado"];

            if (empty(trim($id_usuario)) || empty(trim($id_libro)) || empty(trim($estado))) {
                echo json_encode(array("mensaje" => "Todos los campos son obligatorios"));
            } else {
                require_once "conexion.php";
                $sql = "INSERT INTO reservas (id_usuario, id_libro, estado) VALUES (?, ?, ?)";
                $st = $con->prepare($sql);
                $st->bindParam(1, $id_usuario);
                $st->bindParam(2, $id_libro);
                $st->bindParam(3, $estado);

                try {
                    if ($st->execute()) {
                        $nueva_reserva_id = $con->lastInsertId();
                        $reserva_creada = array("id_reserva" => $nueva_reserva_id, "id_usuario" => $id_usuario, "id_libro" => $id_libro, "estado" => $estado);
                        echo json_encode(array("data" => $reserva_creada));
                    } else {
                        echo json_encode(array("mensaje" => "Error al registrar la reserva"));
                    }
                } catch (PDOException $e) {
                    echo json_encode(array("mensaje" => "Error al registrar la reserva: " . $e->getMessage()));
                }
            }
        } else {
            $pIdUsuario = isset($data["id_usuario"]) ? "" : "[id_usuario]";
            $pIdLibro = isset($data["id_libro"]) ? "" : "[id_libro]";
            $pEstado = isset($data["estado"]) ? "" : "[estado]";
            echo json_encode(array("mensaje" => "Faltan los parámetros $pIdUsuario $pIdLibro $pEstado en la petición"));
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
