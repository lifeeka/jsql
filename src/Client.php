<?php

namespace lifeeka\jsql;

use lifeeka\jsql\Extractor\JsonExtractor;
use lifeeka\jsql\Helpers\Json;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Class Client.
 */
class Client
{
    public $capsule;
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
        $this->capsule = new Capsule();

        $this->capsule->addConnection([
            'driver' => 'mysql',
            'host' => $config['host'],
            'database' => $config['db'],
            'username' => $config['username'],
            'password' => $config['password'],
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
            'prefix' => '',
        ]);

        $this->capsule->setAsGlobal();
    }


    /**
     * @param String $text
     * @return bool
     */
    public function loadText(String $text)
    {
        try {
            $this->file_content = $text;

            return true;
        } catch (\Exception $e) {
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
     * @return bool
     */
    public function migrate()
    {
        $JsonExtractor = new JsonExtractor(new Json($this->file_content));
        $JsonExtractor->toMysqlTables();
        $this->createTables($JsonExtractor);
        return $this->insertData($JsonExtractor);
    }

    /**
     * @param JsonExtractor $JsonExtractor
     */
    private function createTables(JsonExtractor $JsonExtractor)
    {
        foreach ($JsonExtractor->getTablesArray() as $TableName => $TableColumn) {
            $this->capsule::schema()->dropIfExists($TableName);
            $this->capsule::schema()->create($TableName, function ($table) use ($TableColumn) {
                foreach ($TableColumn as $column_item) {
                    switch ($column_item['type']) {
                        case 'int':
                            $table->increments($column_item['name']);
                            break;
                        case 'integer':
                            $table->decimal($column_item['name'], 65)->nullable();
                            break;
                        case 'boolean':
                            $table->boolean($column_item['name'])->nullable();
                            break;
                        case 'double':
                            $table->double($column_item['name'])->nullable();
                            break;
                        default:
                            $table->text($column_item['name'])->nullable();
                            break;

                    }
                }
            });
        }
    }

    /**
     * @param JsonExtractor $JsonExtractor
     * @return bool
     */
    private function insertData(JsonExtractor $JsonExtractor)
    {
        foreach ($JsonExtractor->getDataArray() as $TableName => $TableData) {
            $this->capsule::table($TableName)->insert($TableData);
        }
        return true;
    }

    public function clearDatabase()
    {
    }
}
