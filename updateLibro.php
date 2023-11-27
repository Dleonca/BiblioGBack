<?php
// actualizar_libro.php

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

        if (
            isset($data["id_libro"]) &&
            isset($data["titulo"]) &&
            isset($data["autor"]) &&
            isset($data["publicacion"]) &&
            isset($data["genero"]) &&
            isset($data["cantidad"]) &&
            isset($data["descripcion"]) &&
            isset($data["imagen"])
        ) {
            $id_libro = $data["id_libro"];
            $titulo = $data["titulo"];
            $autor = $data["autor"];
            $publicacion = $data["publicacion"];
            $genero = $data["genero"];
            $cantidad = $data["cantidad"];
            $descripcion = $data["descripcion"];
            $imagen = $data["imagen"];

            if (
                empty(trim($id_libro)) ||
                empty(trim($titulo)) ||
                empty(trim($autor)) ||
                empty(trim($publicacion)) ||
                empty(trim($genero)) ||
                empty(trim($cantidad)) ||
                empty(trim($descripcion)) ||
                empty(trim($imagen))
            ) {
                echo json_encode(array("mensaje" => "Ningún campo puede ir vacío"));
            } else {
                require_once "conexion.php";
                $sql = "UPDATE libros SET
                        titulo = ?,
                        autor = ?,
                        publicacion = ?,
                        genero = ?,
                        cantidad = ?,
                        descripcion = ?,
                        imagen = ?
                        WHERE id_libro = ?";

                $st = $con->prepare($sql);
                $st->bindParam(1, $titulo);
                $st->bindParam(2, $autor);
                $st->bindParam(3, $publicacion);
                $st->bindParam(4, $genero);
                $st->bindParam(5, $cantidad);
                $st->bindParam(6, $descripcion);
                $st->bindParam(7, $imagen);
                $st->bindParam(8, $id_libro);

                try {
                    if ($st->execute()) {
                        echo json_encode(array("mensaje" => "Libro actualizado correctamente"));
                    } else {
                        echo json_encode(array("mensaje" => "Error al actualizar el libro"));
                    }
                } catch (PDOException $e) {
                    echo json_encode(array("mensaje" => "Error al actualizar el libro: " . $e->getMessage()));
                }
            }
        } else {
            $pIdLibro = isset($data["id_libro"]) ? "" : "[id_libro]";
            $pTitulo = isset($data["titulo"]) ? "" : "[titulo]";
            $pAutor = isset($data["autor"]) ? "" : "[autor]";
            $pPublicacion = isset($data["publicacion"]) ? "" : "[publicacion]";
            $pGenero = isset($data["genero"]) ? "" : "[genero]";
            $pCantidad = isset($data["cantidad"]) ? "" : "[cantidad]";
            $pDescripcion = isset($data["descripcion"]) ? "" : "[descripcion]";
            $pImagen = isset($data["imagen"]) ? "" : "[imagen]";
            echo json_encode(array("mensaje" => "Faltan los parámetros $pIdLibro $pTitulo $pAutor $pPublicacion $pGenero $pCantidad $pDescripcion $pImagen en la petición"));
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
