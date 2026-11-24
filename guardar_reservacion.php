<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "conexion.php";

try {

    $datos = json_decode(file_get_contents("php://input"), true);

    if (!$datos) {
        throw new Exception("No se recibieron los datos.");
    }

    $nombre = trim($datos["nombre"]);
    $telefono = trim($datos["telefono"]);
    $fecha = $datos["fecha"];
    $hora = $datos["horario"];
    $nombreSucursal = $datos["sucursal"];
    $nombreBarbero = $datos["barbero"];
    $servicio = $datos["servicio"];
    $descripcion = $datos["descripcion"];
    $precio = $datos["total"];

    // Buscar cliente
    $consulta = $conexion->prepare(
        "SELECT id_cliente FROM cliente WHERE telefono = ?"
    );
    $consulta->execute([$telefono]);

    $cliente = $consulta->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {

        $consulta = $conexion->prepare(
            "INSERT INTO cliente (nombre, telefono) VALUES (?, ?)"
        );

        $consulta->execute([$nombre, $telefono]);

        $idCliente = $conexion->lastInsertId();

    } else {

        $idCliente = $cliente["id_cliente"];

    }

    // Buscar barbero
    $consulta = $conexion->prepare(
        "SELECT id_barbero FROM barberos WHERE nombre = ?"
    );

    $consulta->execute([$nombreBarbero]);

    $barbero = $consulta->fetch(PDO::FETCH_ASSOC);

    if (!$barbero) {
        throw new Exception("No se encontró el barbero: " . $nombreBarbero);
    }

    $idBarbero = $barbero["id_barbero"];

    // Buscar sucursal
    $consulta = $conexion->prepare(
        "SELECT id_sucursal FROM sucursal WHERE nombre = ?"
    );

    $consulta->execute([$nombreSucursal]);

    $sucursal = $consulta->fetch(PDO::FETCH_ASSOC);

    if (!$sucursal) {
        throw new Exception("No se encontró la sucursal: " . $nombreSucursal);
    }

    $idSucursal = $sucursal["id_sucursal"];

    // Agregar detalles
    if (!empty($descripcion)) {
        $servicio .= " - " . $descripcion;
    }

    if (isset($datos["lavado"]) && $datos["lavado"] == "40") {
        $servicio .= " + Lavado";
    }

    // Guardar reservación en la tabla correcta
    $consulta = $conexion->prepare(
        "INSERT INTO reservacion
        (id_cliente, id_barbero, id_sucursal, fecha, precio, servicio, estado, hora)
        VALUES (?, ?, ?, ?, ?, ?, 'Pendiente', ?)"
    );

    $consulta->execute([
        $idCliente,
        $idBarbero,
        $idSucursal,
        $fecha,
        $precio,
        $servicio,
        $hora
    ]);

    echo json_encode([
        "exito" => true,
        "mensaje" => "Reservación guardada correctamente."
    ]);

} catch (Exception $e) {

    echo json_encode([
        "exito" => false,
        "mensaje" => $e->getMessage()
    ]);

}

?>