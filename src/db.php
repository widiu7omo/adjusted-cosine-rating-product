<?php

class DB
{
    public $pdo = NULL;
    private static $db_host = 'localhost';
    private static $db_user = 'root';
    private static $db_pass = '';
    private static $db_name = 'tes_simi';

    public function __construct()
    {
        $default_options = [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ];
        $db_name = self::$db_name;
        $db_host = self::$db_host;
        $db_user = self::$db_user;
        $db_pass = self::$db_pass;
        $options = array_replace($default_options);
        $dsn = "mysql:host=$db_host;dbname=$db_name;port=3306;charset=utf8mb4";

        try {
            $this->pdo = new \PDO($dsn, $db_user, $db_pass, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public function run($sql, $args = NULL)
    {
        if (!$args) {
            return $this->pdo->query($sql);
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($args);
        return $stmt;
    }
}
