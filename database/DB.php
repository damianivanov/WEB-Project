<?php

// TODO: Add comments everywhere
class DB {
    private PDO $connection;

    public function __construct() {
        $connection    = $_ENV["DB_CONNECTION"];
        $host          = $_ENV["DB_HOST"];
        $port          = $_ENV["DB_PORT"];
        $username      = $_ENV["DB_USERNAME"];
        $database_name = $_ENV["DB_DATABASE"];
        $password      = $_ENV["DB_PASSWORD"];

        $this->connection = new PDO("$connection:host=$host:$port;dbname=$database_name", $username, $password, [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    public function execute(string $sql, array $values): array {
        $stmt = $this->connection->prepare($sql);

        $result = $stmt->execute($values);

        if(!$result) {
            throw new DatabaseQueryError();
        }

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function multipleExecute(string $sql, array $values) {
        $stmt = $this->connection->prepare($sql);

        foreach ($values as $value) {
           $result = $stmt->execute($value);

           if(!$result) {
               throw new DatabaseQueryError();
           }
        }
    }

    public function getLastId() {
        return intval($this->connection->lastInsertId());
    }

//    public static function prepare;

    public static function prepareMultipleInsertSQL(string $table, string $columns, int $count) : string {
        $columnArr = explode(", ", $columns);

        return $sql = "INSERT INTO $table ($columns) VALUES " . DB::getQuestionMarks($count, $columns);
    }

    public static function prepareMultipleData(array $values): array {
        $result = [];
        foreach ($values as $value) {
            $result[] = [$value];
        }
        return $result;
    }

    public static function getQuestionLine(int $count) {
        $result = "(?";
        for ($i = 1; $i < $count; ++$i) {
            $result .= ",?";
        }
        $result .= ")";
        return $result;
    }

    private static function getQuestionMarks(int $count, string $columns): string {
        if ($count <= 0) {
            throw new UnexpectedValueError();
        }

        $questionMarks = [];

        for($i = 0; $i < $count; $i++) {
            $questionMarks[] = "(" . DB::getPlaceHolder($columns) . ")";
        }

        return implode(", ", $questionMarks);
    }

    private static function getPlaceHolder(string $columns) : string {
        $columnArr = explode(", ", $columns);
        $questionMarks = [];

        foreach ($columnArr as $val) {
            $questionMarks[] = "?";
        }

        return implode(",", $questionMarks);
    }

    public static function hasDuplicate(string $sql, array $values): bool {
        $result = (new DB())->execute($sql, $values);
        $count = count($result);

        return $count >= 1;
    }
}
