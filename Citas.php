<?php
include "db.php";

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

switch ($method) {
    case 'GET':
        if ($id) {
            // Obtener una cita específica con JOIN
            $stmt = $conexion->prepare("SELECT c.*, p.nombre AS paciente_nombre FROM citas c JOIN pacientes p ON c.paciente_id = p.id WHERE c.id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            echo json_encode(["success" => true, "data" => $data ?: null]);
        } else {
            // Obtener todas las citas con JOIN, ordenadas por fecha/hora
            $result = $conexion->query("SELECT c.*, p.nombre AS paciente_nombre FROM citas c JOIN pacientes p ON c.paciente_id = p.id ORDER BY c.fecha, c.hora");
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            echo json_encode(["success" => true, "data" => $data]);
        }
        break;

    case 'POST':
        // Crear nueva cita
        if (isset($input['paciente_id']) && isset($input['fecha']) && isset($input['hora']) && isset($input['odontologo'])) {
            $paciente_id = intval($input['paciente_id']);
            $fecha = $input['fecha'];
            $hora = $input['hora'];
            $odontologo = $input['odontologo'];

            // Verificar si el horario está disponible (no hay cita pendiente/confirmada en ese slot)
            $stmt_check = $conexion->prepare("SELECT id FROM citas WHERE paciente_id = ? AND fecha = ? AND hora = ? AND estado IN ('pendiente', 'confirmada')");
            $stmt_check->bind_param("iss", $paciente_id, $fecha, $hora);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                echo json_encode(["success" => false, "message" => "Horario no disponible para este paciente"]);
                break;
            }

            $stmt = $conexion->prepare("INSERT INTO citas (paciente_id, fecha, hora, odontologo, estado) VALUES (?, ?, ?, ?, 'pendiente')");
            $stmt->bind_param("isss", $paciente_id, $fecha, $hora, $odontologo);
            if ($stmt->execute()) {
                echo json_encode(["success" => true, "id" => $conexion->insert_id, "message" => "Cita creada"]);
            } else {
                echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Faltan campos requeridos"]);
        }
        break;

    case 'PUT':
        // Actualizar cita
        if ($id && isset($input['paciente_id']) && isset($input['fecha']) && isset($input['hora']) && isset($input['odontologo']) && isset($input['estado'])) {
            $paciente_id = intval($input['paciente_id']);
            $fecha = $input['fecha'];
            $hora = $input['hora'];
            $odontologo = $input['odontologo'];
            $estado = $input['estado'];

            // Verificar disponibilidad si se cambian fecha/hora (excluyendo la cita actual)
            if (isset($input['fecha']) || isset($input['hora'])) {
                $stmt_check = $conexion->prepare("SELECT id FROM citas WHERE paciente_id = ? AND fecha = ? AND hora = ? AND estado IN ('pendiente', 'confirmada') AND id != ?");
                $stmt_check->bind_param("issi", $paciente_id, $fecha, $hora, $id);
                $stmt_check->execute();
                if ($stmt_check->get_result()->num_rows > 0) {
                    echo json_encode(["success" => false, "message" => "Nuevo horario no disponible"]);
                    break;
                }
            }

            $stmt = $conexion->prepare("UPDATE citas SET paciente_id = ?, fecha = ?, hora = ?, odontologo = ?, estado = ? WHERE id = ?");
            $stmt->bind_param("issssi", $paciente_id, $fecha, $hora, $odontologo, $estado, $id);
            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Cita actualizada"]);
            } else {
                echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "ID o campos requeridos faltantes"]);
        }
        break;

    case 'DELETE':
        // Cancelar cita (actualizar estado)
        if ($id) {
            $stmt = $conexion->prepare("UPDATE citas SET estado = 'cancelada' WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Cita cancelada"]);
            } else {
                echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
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