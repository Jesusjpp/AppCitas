<?php
include "db.php";

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

switch ($method) {
    case 'GET':
        if ($id) {
            // Obtener un paciente específico
            $stmt = $conexion->prepare("SELECT * FROM pacientes WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            echo json_encode(["success" => true, "data" => $data ?: null]);
        } else {
            // Obtener todos los pacientes
            $result = $conexion->query("SELECT * FROM pacientes ORDER BY nombre");
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            echo json_encode(["success" => true, "data" => $data]);
        }
        break;

    case 'POST':
        // Crear nuevo paciente
        if (isset($input['nombre']) && isset($input['documento']) && isset($input['telefono']) && isset($input['correo'])) {
            $stmt = $conexion->prepare("INSERT INTO pacientes (nombre, documento, telefono, correo) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $input['nombre'], $input['documento'], $input['telefono'], $input['correo']);
            if ($stmt->execute()) {
                echo json_encode(["success" => true, "id" => $conexion->insert_id, "message" => "Paciente creado"]);
            } else {
                echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Faltan campos requeridos"]);
        }
        break;

    case 'PUT':
        // Actualizar paciente
        if ($id && isset($input['nombre']) && isset($input['documento']) && isset($input['telefono']) && isset($input['correo'])) {
            $stmt = $conexion->prepare("UPDATE pacientes SET nombre = ?, documento = ?, telefono = ?, correo = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $input['nombre'], $input['documento'], $input['telefono'], $input['correo'], $id);
            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Paciente actualizado"]);
            } else {
                echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "ID o campos requeridos faltantes"]);
        }
        break;

    case 'DELETE':
        // Eliminar paciente
        if ($id) {
            $stmt = $conexion->prepare("DELETE FROM pacientes WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Paciente eliminado"]);
            } else {
                echo json_encode(["success" => false, "message" => "Error: " . $stmt->error . " (Verifica citas asociadas)"]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "ID requerido"]);
        }
        break;

    default:
        echo json_encode(["success" => false, "message" => "Método no permitido"]);
        break;
}
?>