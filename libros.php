<?php
// libros.php (Insertar Libro)

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

        if (
            isset($data["titulo"]) && isset($data["autor"]) && isset($data["publicacion"]) &&
            isset($data["genero"]) && isset($data["cantidad"]) && isset($data["descripcion"]) &&
            isset($data["imagen"])
        ) {
            $titulo = $data["titulo"];
            $autor = $data["autor"];
            $publicacion = $data["publicacion"];
            $genero = $data["genero"];
            $cantidad = $data["cantidad"];
            $descripcion = $data["descripcion"];
            $imagen = $data["imagen"];

            if (
                empty(trim($titulo)) || empty(trim($autor)) || empty(trim($publicacion)) ||
                empty(trim($genero)) || empty(trim($cantidad)) || empty(trim($descripcion)) ||
                empty(trim($imagen))
            ) {
                echo json_encode(array("mensaje" => "Todos los campos son obligatorios"));
            } else {
                require_once "conexion.php";
                $sql = "INSERT INTO libros (titulo, autor, publicacion, genero, cantidad, descripcion, imagen) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $st = $con->prepare($sql);

                // Vincular los parámetros usando bindValue
                $st->bindValue(1, $titulo);
                $st->bindValue(2, $autor);
                $st->bindValue(3, $publicacion);
                $st->bindValue(4, $genero);
                $st->bindValue(5, $cantidad);
                $st->bindValue(6, $descripcion);
                $st->bindValue(7, $imagen);

                // Manejar errores de duplicados u otros errores
                try {
                    if ($st->execute()) {
                        $nuevo_id = $con->lastInsertId();
                        $libro_creado = array("id_libro" => $nuevo_id, "titulo" => $titulo, "autor" => $autor, "publicacion" => $publicacion, "genero" => $genero, "cantidad" => $cantidad, "descripcion" => $descripcion, "imagen" => $imagen);
                        echo json_encode(array("data" => $libro_creado));
                    } else {
                        echo json_encode(array("mensaje" => "Error al registrar el libro"));
                    }
                } catch (PDOException $e) {
                    $errorInfo = $st->errorInfo();
                    if ($errorInfo[1] == 1062) {
                        echo json_encode(array("mensaje" => "Error: El libro ya está registrado."));
                    } else {
                        echo json_encode(array("mensaje" => "Error al registrar el libro: " . $e->getMessage()));
                    }
                }
            }
        } else {
            $pTitulo = isset($data["titulo"]) ? "" : "[titulo]";
            $pAutor = isset($data["autor"]) ? "" : "[autor]";
            $pPublicacion = isset($data["publicacion"]) ? "" : "[publicacion]";
            $pGenero = isset($data["genero"]) ? "" : "[genero]";
            $pCantidad = isset($data["cantidad"]) ? "" : "[cantidad]";
            $pDescripcion = isset($data["descripcion"]) ? "" : "[descripcion]";
            $pImagen = isset($data["imagen"]) ? "" : "[imagen]";
            echo json_encode(array("mensaje" => "Faltan los parámetros $pTitulo $pAutor $pPublicacion $pGenero $pCantidad $pDescripcion $pImagen en la petición"));
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
