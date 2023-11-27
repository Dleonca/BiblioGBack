<?php
// obtener_libro_por_id.php
/*
header('Access-Control-Allow-Headers: access');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');*/

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
        $url = parse_url($_SERVER['REQUEST_URI']);
        $path = explode('/', $url['path']);

        if (isset($path[3]) && is_numeric($path[3])) {
            $id_libro = intval($path[3]);
            require_once "conexion.php";
            $sql = "SELECT * FROM libros WHERE id_libro = ?";
            $st = $con->prepare($sql);
            $st->bindParam(1, $id_libro);

            try {
                if ($st->execute()) {
                    $libro = $st->fetch(PDO::FETCH_ASSOC);
                    if ($libro) {
                        echo json_encode(array("data" => $libro));
                    } else {
                        echo json_encode(array("mensaje" => "Libro no encontrado"));
                    }
                } else {
                    echo json_encode(array("mensaje" => "Error al obtener el libro"));
                }
            } catch (PDOException $e) {
                echo json_encode(array("mensaje" => "Error al obtener el libro: " . $e->getMessage()));
            }
        } else {
            echo json_encode(array("mensaje" => "ID de libro no válido"));
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
