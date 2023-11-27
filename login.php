<?php
// api_login.php

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

if ($metodo != "POST") {
    http_response_code(400);
    echo json_encode(array("mensaje" => "Método incorrecto, debe ser POST"));
    die();
}

// Token 
define('API_KEY', "e1f602bf73cc96f53c10bb7f7953a438fb7b3c0a");
$headers = apache_request_headers();

if (isset($headers["Authorization"])) {
    if ($headers["Authorization"] == 'e1f602bf73cc96f53c10bb7f7953a438fb7b3c0a') {
        $json_data = file_get_contents("php://input");
        $data = json_decode($json_data, true);

        if (isset($data["correo"]) && isset($data["clave"])) {
            $correo = $data["correo"];
            $clave = $data["clave"];

            if (empty(trim($correo)) || empty(trim($clave))) {
                echo json_encode(array("mensaje" => "Correo y clave son obligatorios"));
            } else {
                require_once "conexion.php";
                
                $sql = "SELECT id, nombre, apellidos, correo, rol FROM usuarios WHERE correo = ? AND clave = ?";
                $st = $con->prepare($sql);

                try {
                    $st->bindParam(1, $correo);
                    $st->bindParam(2, $clave);
                    $st->execute();
                    $usuario = $st->fetch(PDO::FETCH_ASSOC);

                    if ($usuario) {
                        echo json_encode(array("data" => $usuario));
                    } else {
                        echo json_encode(array("mensaje" => "Correo o clave incorrectos"));
                    }
                } catch (PDOException $e) {
                    echo json_encode(array("mensaje" => "Error en la consulta: " . $e->getMessage()));
                }
            }
        } else {
            echo json_encode(array("mensaje" => "Faltan los parámetros 'correo' y 'clave' en la petición"));
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
