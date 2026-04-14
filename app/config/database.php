<?php

class Database
{
    private static string $host = 'localhost';
    private static string $db_name = 'cotizador_mvc';
    private static string $username = 'root';
    private static string $password = '';

    private static $conn = null;

    /**
     * Obtiene una conexion compartida. Usa PDO cuando el driver esta
     * disponible y hace fallback a mysqli cuando Apache no carga pdo_mysql.
     */
    public static function connect()
    {
        if (self::$conn !== null) {
            return self::$conn;
        }

        try {
            if (extension_loaded('pdo_mysql')) {
                self::$conn = new PDO(
                    'mysql:host=' . self::$host . ';dbname=' . self::$db_name . ';charset=utf8mb4',
                    self::$username,
                    self::$password
                );

                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                return self::$conn;
            }

            if (!extension_loaded('mysqli')) {
                throw new Exception('No hay un driver disponible para conectarse a MySQL.');
            }

            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $mysqli = new mysqli(
                self::$host,
                self::$username,
                self::$password,
                self::$db_name
            );

            $mysqli->set_charset('utf8mb4');
            self::$conn = new DatabaseMysqliConnection($mysqli);

            return self::$conn;
        } catch (Throwable $e) {
            die('Error critico de Conexion a la Base de Datos: ' . $e->getMessage());
        }
    }
}

class DatabaseMysqliConnection
{
    private mysqli $mysqli;

    public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function query(string $sql): DatabaseMysqliStatement
    {
        $result = $this->mysqli->query($sql);
        return DatabaseMysqliStatement::fromResult($result);
    }

    public function prepare(string $sql): DatabaseMysqliStatement
    {
        return new DatabaseMysqliStatement($this->mysqli, $sql);
    }

    public function beginTransaction(): void
    {
        $this->mysqli->begin_transaction();
    }

    public function commit(): void
    {
        $this->mysqli->commit();
    }

    public function rollBack(): void
    {
        $this->mysqli->rollback();
    }

    public function lastInsertId(): string
    {
        return (string) $this->mysqli->insert_id;
    }
}

class DatabaseMysqliStatement
{
    private ?mysqli $mysqli = null;
    private ?mysqli_stmt $stmt = null;
    private ?mysqli_result $result = null;
    private string $sql = '';
    private string $preparedSql = '';
    private array $namedKeys = [];

    public function __construct(?mysqli $mysqli, string $sql)
    {
        $this->mysqli = $mysqli;
        $this->sql = $sql;

        if ($sql !== '') {
            $this->preparedSql = preg_replace_callback(
                '/:([a-zA-Z_][a-zA-Z0-9_]*)/',
                function ($matches) {
                    $this->namedKeys[] = $matches[1];
                    return '?';
                },
                $sql
            );
        }
    }

    public static function fromResult(mysqli_result|bool $result): self
    {
        $instance = new self(null, '');
        $instance->result = $result instanceof mysqli_result ? $result : null;

        return $instance;
    }

    public function execute(array $params = []): bool
    {
        if ($this->mysqli === null) {
            return true;
        }

        $this->stmt = $this->mysqli->prepare($this->preparedSql);

        if ($params !== []) {
            $orderedValues = $this->normalizeParams($params);
            $types = '';
            $bindValues = [];

            foreach ($orderedValues as $value) {
                $types .= $this->detectType($value);
                $bindValues[] = $value;
            }

            $refs = [];
            foreach ($bindValues as $index => $value) {
                $refs[$index] = &$bindValues[$index];
            }

            $this->stmt->bind_param($types, ...$refs);
        }

        $executed = $this->stmt->execute();
        $queryResult = $this->stmt->get_result();
        $this->result = $queryResult instanceof mysqli_result ? $queryResult : null;

        return $executed;
    }

    public function fetch($mode = null)
    {
        if ($this->result === null) {
            return false;
        }

        return $this->result->fetch_assoc();
    }

    public function fetchAll($mode = null): array
    {
        if ($this->result === null) {
            return [];
        }

        return $this->result->fetch_all(MYSQLI_ASSOC);
    }

    public function fetchColumn(int $column = 0)
    {
        if ($this->result === null) {
            return false;
        }

        $row = $this->result->fetch_row();
        return $row[$column] ?? false;
    }

    private function normalizeParams(array $params): array
    {
        if ($this->namedKeys === []) {
            return array_values($params);
        }

        $ordered = [];
        foreach ($this->namedKeys as $key) {
            $ordered[] = $params[$key] ?? null;
        }

        return $ordered;
    }

    private function detectType($value): string
    {
        return match (true) {
            is_int($value) => 'i',
            is_float($value) => 'd',
            default => 's',
        };
    }
}
