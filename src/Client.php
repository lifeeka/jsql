<?php

namespace lifeeka\jsql;

use lifeeka\jsql\Extractor\JsonExtractor;
use lifeeka\jsql\Helpers\Json;

/**
 * Class Client.
 */
class Client
{
    public $db_connection;
    public $error = false;
    public $file_content = null;
    public $sql = null;

    /**
     * Client constructor.
     *
     * @param $config
     */
    public function __construct($config)
    {
        try {
            return $this->db_connection = new \PDO("mysql:host={$config['host']};dbname={$config['db']}", $config['username'], $config['password']);
        } catch (\PDOException $e) {
            $this->error = $e->getMessage();

            return false;
        }
    }

    /**
     * @param $file_name
     *
     * @return bool
     */
    public function loadFile($file_name)
    {
        try {
            $this->file_content = file_get_contents($file_name);

            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();

            return false;
        }
    }

    /**
     * @return string
     */
    public function toMysql()
    {
        $JsonExtractor = new JsonExtractor(new Json($this->file_content));

        return $this->sql = $JsonExtractor->toMysqlTables();
    }

    /**
     * @return bool|\PDOStatement
     */
    public function migrate()
    {
        try {
            return $this->db_connection->query($this->sql);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function clearDatabase()
    {
        $this->db_connection->query('SET foreign_key_checks = 0');
        if ($result = $this->db_connection->query('SHOW TABLES')) {
            $row = $result->fetchAll();

            foreach ($row as $rowItem) {
                $this->db_connection->query('DROP TABLE IF EXISTS '.$rowItem[0]);
            }
        }

        $this->db_connection->query('SET foreign_key_checks = 1');
    }
}
