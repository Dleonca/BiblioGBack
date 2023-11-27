<?php
// api_actualizar_estado_prestamo.php

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

        if (isset($data["id_prestamo"]) && isset($data["estado"])) {
            $id_prestamo = $data["id_prestamo"];
            $estado = $data["estado"];

            require_once "conexion.php";
            
            $sql = "UPDATE prestamos SET estado = ? WHERE id_prestamo = ?";
            $st = $con->prepare($sql);

            try {
                $st->bindParam(1, $estado);
                $st->bindParam(2, $id_prestamo, PDO::PARAM_INT);

                if ($st->execute()) {
                    echo json_encode(array("mensaje" => "Estado del préstamo actualizado con éxito"));
                } else {
                    echo json_encode(array("mensaje" => "Error al actualizar el estado del préstamo"));
                }
            } catch (PDOException $e) {
                echo json_encode(array("mensaje" => "Error al actualizar el estado del préstamo: " . $e->getMessage()));
            }
        } else {
            echo json_encode(array("mensaje" => "Faltan los parámetros 'id_prestamo' y 'estado' en la petición"));
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
