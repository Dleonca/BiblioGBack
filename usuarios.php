<?php
// usuarios.php (Crear Usuario)

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
//if (isset($_SERVER['HTTP_AUTHORIZATION']) && $_SERVER['HTTP_AUTHORIZATION'] == 'e1f602bf73cc96f53c10bb7f7953a438fb7b3c0a') {

if (isset($headers["Authorization"])) {
    if ($headers["Authorization"] == 'e1f602bf73cc96f53c10bb7f7953a438fb7b3c0a') {
        $json_data = file_get_contents("php://input");
        $data = json_decode($json_data, true);

        if (isset($data["nombre"]) && isset($data["apellidos"]) && isset($data["tipo_doc"]) && isset($data["correo"]) && isset($data["telefono"]) && isset($data["clave"]) && isset($data["rol"]) && isset($data["documento"])) {
            $nombre = $data["nombre"];
            $apellidos = $data["apellidos"];
            $tipo_doc = $data["tipo_doc"];
            $correo = $data["correo"];
            $telefono = $data["telefono"];
            $clave = $data["clave"];
            $rol = $data["rol"];
            $documento = $data["documento"];

            if (empty(trim($nombre)) || empty(trim($apellidos)) || empty(trim($tipo_doc)) || empty(trim($correo)) || empty(trim($telefono)) || empty(trim($clave)) || empty(trim($rol)) || empty(trim($documento))) {
                echo json_encode(array("mensaje" => "Todos los campos son obligatorios"));
            } else {
                require_once "conexion.php";
                $sql = "INSERT INTO usuarios (nombre, apellidos, tipo_doc, correo, telefono, clave, rol, documento) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $st = $con->prepare($sql);

                // Vincular los parámetros usando bindValue
                $st->bindValue(1, $nombre);
                $st->bindValue(2, $apellidos);
                $st->bindValue(3, $tipo_doc);
                $st->bindValue(4, $correo);
                $st->bindValue(5, $telefono);
                $st->bindValue(6, $clave);
                $st->bindValue(7, $rol);
                $st->bindValue(8, $documento);

                // Manejar errores de duplicados
                try {
                    if ($st->execute()) {
                        $nuevo_id = $con->lastInsertId();
                        $usuario_creado = array("id" => $nuevo_id, "nombre" => $nombre, "apellidos" => $apellidos, "correo" => $correo, "rol" => $rol);
                        echo json_encode(array("data" => $usuario_creado));
                    } else {
                        echo json_encode(array("mensaje" => "Error al registrar el usuario"));
                    }
                } catch (PDOException $e) {
                    $errorInfo = $st->errorInfo();
                    if ($errorInfo[1] == 1062) {
                        echo json_encode(array("mensaje" => "Error: El correo electrónico ya está en uso."));
                    } else {
                        echo json_encode(array("mensaje" => "Error al registrar el usuario: " . $e->getMessage(), "errorInfo" => $errorInfo));
                    }
                }
            }
        } else {
            $pNombre = isset($data["nombre"]) ? "" : "[nombre]";
            $pApellidos = isset($data["apellidos"]) ? "" : "[apellidos]";
            $pTipoDoc = isset($data["tipo_doc"]) ? "" : "[tipo_doc]";
            $pCorreo = isset($data["correo"]) ? "" : "[correo]";
            $pTelefono = isset($data["telefono"]) ? "" : "[telefono]";
            $pClave = isset($data["clave"]) ? "" : "[clave]";
            $pRol = isset($data["rol"]) ? "" : "[rol]";
            $pDocumento = isset($data["documento"]) ? "" : "[documento]";
            echo json_encode(array("mensaje" => "Faltan los parámetros $pNombre $pApellidos $pTipoDoc $pCorreo $pTelefono $pClave $pRol $pDocumento en la petición"));
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
