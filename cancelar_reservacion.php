<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "conexion.php";

try {

    $datos = json_decode(file_get_contents("php://input"), true);

    $idReserva = $datos["id_reserva"] ?? "";

    if (!is_numeric($idReserva) || $idReserva <= 0) {
        throw new Exception("Escribe un folio válido.");
    }

    $consulta = $conexion->prepare(
        "SELECT id_reserva FROM reservacion WHERE id_reserva = ?"
    );

    $consulta->execute([$idReserva]);

    if (!$consulta->fetch()) {
        throw new Exception("No se encontró esa reservación.");
    }

    $consulta = $conexion->prepare(
        "DELETE FROM reservacion WHERE id_reserva = ?"
    );

    $consulta->execute([$idReserva]);

    echo json_encode([
        "exito" => true,
        "mensaje" => "La reservación se eliminó correctamente."
    ]);

} catch (Exception $e) {

    echo json_encode([
        "exito" => false,
        "mensaje" => $e->getMessage()
    ]);

}

?>