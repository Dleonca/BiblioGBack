<?php
// aceptar_rechazar_reserva.php

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

        if (isset($data["id_reserva"]) && isset($data["estado"])) {
            $id_reserva = $data["id_reserva"];
            $estado = $data["estado"];

            require_once "conexion.php";
            
            // Obtener información de la reserva
            $sql_reserva = "SELECT id_libro FROM reservas WHERE id_reserva = ?";
            $stmt_reserva = $con->prepare($sql_reserva);
            $stmt_reserva->bindParam(1, $id_reserva);
            $stmt_reserva->execute();
            $reserva_info = $stmt_reserva->fetch(PDO::FETCH_ASSOC);

            if ($reserva_info) {
                $id_libro = $reserva_info["id_libro"];

                if ($estado == "aceptado") {
                    // Aceptar reserva y registrar préstamo
                    if (isset($data["desde"]) && isset($data["hasta"])) {
                        $desde = $data["desde"];
                        $hasta = $data["hasta"];

                        $sql_aceptar_reserva = "UPDATE reservas SET estado = 'aceptado' WHERE id_reserva = ?";
                        $stmt_aceptar_reserva = $con->prepare($sql_aceptar_reserva);
                        $stmt_aceptar_reserva->bindParam(1, $id_reserva);
                        
                        $sql_registrar_prestamo = "INSERT INTO prestamos (id_libro, id_reserva, estado, desde, hasta) VALUES (?, ?, 'prestado', ?, ?)";
                        $stmt_registrar_prestamo = $con->prepare($sql_registrar_prestamo);
                        $stmt_registrar_prestamo->bindParam(1, $id_libro);
                        $stmt_registrar_prestamo->bindParam(2, $id_reserva);
                        $stmt_registrar_prestamo->bindParam(3, $desde);
                        $stmt_registrar_prestamo->bindParam(4, $hasta);

                        try {
                            $con->beginTransaction();

                            $stmt_aceptar_reserva->execute();
                            $stmt_registrar_prestamo->execute();

                            $con->commit();
                            
                            echo json_encode(array("mensaje" => "Reserva aceptada y préstamo registrado con éxito"));
                        } catch (PDOException $e) {
                            $con->rollBack();
                            echo json_encode(array("mensaje" => "Error al procesar la reserva: " . $e->getMessage()));
                        }
                    } else {
                        echo json_encode(array("mensaje" => "Para aceptar una reserva, debes proporcionar desde y hasta"));
                    }
                } elseif ($estado == "rechazado") {
                    // Rechazar reserva
                    $sql_rechazar_reserva = "UPDATE reservas SET estado = 'rechazado' WHERE id_reserva = ?";
                    $stmt_rechazar_reserva = $con->prepare($sql_rechazar_reserva);
                    $stmt_rechazar_reserva->bindParam(1, $id_reserva);

                    if ($stmt_rechazar_reserva->execute()) {
                        echo json_encode(array("mensaje" => "Reserva rechazada con éxito"));
                    } else {
                        echo json_encode(array("mensaje" => "Error al rechazar la reserva"));
                    }
                } else {
                    echo json_encode(array("mensaje" => "El estado de la reserva debe ser 'aceptado' o 'rechazado'"));
                }
            } else {
                echo json_encode(array("mensaje" => "No se encontró la reserva con el ID proporcionado"));
            }
        } else {
            echo json_encode(array("mensaje" => "Faltan los parámetros id_reserva y estado en la petición"));
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
