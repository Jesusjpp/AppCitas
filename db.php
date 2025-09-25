<?php
// db.php - Conexión a la base de datos
$servername = "localhost";
$username = "root";  
$password = "";      
$dbname = "clinica_db";

$conexion = new mysqli($servername, $username, $password, $dbname);

if ($conexion->connect_error) {
    die(json_encode(["success" => false, "message" => "Error de conexión: " . $conexion->connect_error]));
}

$conexion->set_charset("utf8");

// Headers CORS para API
header("Access-Control-Allow-Origin: *");  // En producción, restringe a tu dominio
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}


?>