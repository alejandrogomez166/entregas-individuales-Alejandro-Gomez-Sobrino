<?php

require_once "Clases.php";
require_once "Varios.php";

class DAO
{
    private static $pdo = null;

    private static function obtenerPdoConexionBD()
    {
        $servidor = "localhost";
        $identificador = "root";
        $contrasenna = "";
        $bd = "agenda"; // Schema
        $opciones = [
            PDO::ATTR_EMULATE_PREPARES => false, // Modo emulación desactivado para prepared statements "reales"
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Que los errores salgan como excepciones.
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // El modo de fetch que queremos por defecto.
        ];

        try {
            $pdo = new PDO("mysql:host=$servidor;dbname=$bd;charset=utf8", $identificador, $contrasenna, $opciones);
        } catch (Exception $e) {
            error_log("Error al conectar: " . $e->getMessage());
            exit("Error al conectar" . $e->getMessage());
        }

        return $pdo;
    }

    private static function ejecutarConsulta(string $sql, array $parametros): array
    {
        if (!isset(self::$pdo)) self::$pdo = self::obtenerPdoConexionBd();

        $select = self::$pdo->prepare($sql);
        $select->execute($parametros);
        $rs = $select->fetchAll();

        return $rs;
    }

    // Devuelve:
    //   - null: si ha habido un error
    //   - 0, 1 u otro número positivo: OK (no errores) y estas son las filas afectadas.
    private static function ejecutarActualizacion(string $sql, array $parametros): ?int
    {
        if (!isset(self::$pdo)) self::$pdo = self::obtenerPdoConexionBd();

        $actualizacion = self::$pdo->prepare($sql);
        $sqlConExito = $actualizacion->execute($parametros);

        if (!$sqlConExito) return null;
        else return $actualizacion->rowCount();
    }



    /* CATEGORÍA */

    private static function categoriaCrearDesdeRs(array $fila): Categoria
    {
        return new Categoria($fila["id"], $fila["nombre"]);
    }

    public static function categoriaObtenerPorId(int $id): ?Categoria
    {
        $rs = self::ejecutarConsulta(
            "SELECT * FROM Categoria WHERE id=?",
            [$id]
        );
        if ($rs) return self::crearCategoriaDesdeRs($rs[0]);
        else return null;
    }

    public static function categoriaActualizar($id, $nombre)
    {
        self::ejecutarActualizacion(
            "UPDATE Categoria SET nombre=? WHERE id=?",
            [$nombre, $id]
        );
    }

    public static function categoriaCrear(string $nombre)
    {
        self::ejecutarActualizacion(
            "INSERT INTO Categoria (nombre) VALUES (?)",
            [$nombre]
        );
    }

    public static function categoriaObtenerTodas(): array
    {
        $datos = [];

        $rs = self::ejecutarConsulta(
            "SELECT * FROM Categoria ORDER BY nombre",
            []
        );

        foreach ($rs as $fila) {
            $categoria = self::categoriaCrearDesdeRs($fila);
            array_push($datos, $categoria);
        }

        return $datos;
    }
    public static function categoriatodas():array{
        $rs = self::ejecutarConsulta(
            "SELECT * FROM Categoria ",[]);
        $arraylargo=[];
        foreach ($rs as $fila) {
            $categoria = new Categoria($fila["id"],$fila["nombre"]);
            array_push($arraylargo,$categoria);

        }
        return $arraylargo;
    }
      public static function eliminarCategoriaPorId(int $id): bool
    {

        $sql = "DELETE FROM Categoria WHERE id=?";

        return self::ejecutarActualizacion($sql, [$id]);
    }
    /* PERSONA */

    private static function personaCrearDesdeRs(array $fila): Persona
    {
        return new Persona($fila["id"], $fila["nombre"], $fila["apellidos"], $fila["telefono"], $fila["estrella"], $fila["categoriaId"]);
    }

    public static function personaObtenerPorId(int $id): ?Persona
    {
        $rs = self::ejecutarConsulta(
            "SELECT * FROM Persona WHERE id=?",
            [$id]
        );
        if ($rs) return self::personaCrearDesdeRs($rs[0]);
        else return null;
    }

    public static function personaActualizar(int $id, string $nombre, string $apellidos, string $telefono, bool $estrella, int $categoriaId)
    {
        self::ejecutarActualizacion(
            "UPDATE Persona SET nombre=?, apellidos=?, telefono=?, estrella=?, categoriaId=? WHERE id=?",
            [$nombre, $apellidos, $telefono, $estrella, $categoriaId, $id]
        );
    }

    public static function personaCrear(string $nombre, string $apellidos, string $telefono, bool $estrella, int $categoriaId): bool
    {
        return self::ejecutarActualizacion(
            "INSERT INTO Persona (nombre, apellidos, telefono, estrella, categoriaId) VALUES (?, ?, ?, ?, ?)",
            [$nombre, $apellidos, $telefono, $estrella, $categoriaId]
        );
    }

    public static function personaObtenerTodas($posibleClausulaWhere): array
    {
        $datos = [];
        $rs = self::ejecutarConsulta(
            "SELECT * FROM Persona $posibleClausulaWhere ORDER BY nombre",
            []
        );

        foreach ($rs as $fila) {
            $persona = self::personaCrearDesdeRs($fila);
            array_push($datos, $persona);
        }

        return $datos;
    }
    public static function eliminarPersonaPorId(int $id): bool
    {

        $sql = "DELETE FROM Persona WHERE id=?";

        return self::ejecutarActualizacion($sql, [$id]);
    }

    public static function personaGuardarPorId(int $id, string $nombre, string $apellidos, string $telefono, bool $estrella, int $categoriaId): bool
    {
        return self::ejecutarActualizacion(
            "UPDATE Persona SET nombre=?, apellidos=?, telefono=?, estrella=?, categoriaId=? WHERE id=?",
            [$nombre, $apellidos, $telefono, $estrella, $categoriaId, $id]
        );
    }


    public static function personaCategoria(int $id): string
    {
        $rs= self::ejecutarConsulta(
            "SELECT nombre FROM Categoria WHERE id=?",
            [$id]
        );
        return $rs[0]["nombre"];
    }
    public static function personaFicha($id): array
    {
        $nuevaEntrada = ($id == -1);
        $categorias= self::categoriaObtenerTodas();
        if ($nuevaEntrada) {
            $personaNombre = "<introduzca nombre>";
            $personaApellidos= "<introduzca apellidos>";
            $personaTelefono= "<introduzca telefono>";
            $personaCategoriaId= "<introduzca id categoria>";
            $personaEstrella= false;
            return [$nuevaEntrada, $personaNombre, $personaApellidos, $personaTelefono, $personaCategoriaId, $personaEstrella, $categorias];

        } else {
            $rs= self::ejecutarConsulta(
                "SELECT * FROM Persona WHERE id=?",
                [$id]
            );
            return [$nuevaEntrada, $rs[0]["nombre"], $rs[0]["apellidos"], $rs[0]["telefono"], $rs[0]["categoriaId"], $rs[0]["estrella"], $categorias];
        }
    }

    public static function personaEstablecerEstadoEstrella(int $id): bool
    {
        return self::ejecutarActualizacion(
            "UPDATE persona SET estrella = (NOT (SELECT estrella FROM persona WHERE id=?)) WHERE id=?",
            [$id, $id]
        );

    }
}