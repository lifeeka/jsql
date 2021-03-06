<?php

namespace Lifeeka\JSQL;

use Lifeeka\JSQL\Extractor\JsonExtractor;
use Lifeeka\JSQL\Helpers\Json;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Class Client.
 */
class Client
{
    const TABLE_ONLY = "table_only";
    const DATA_ONLY = "data_only";
    const ALL = "all";

    public $mainTableName = "main";

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
            'strict' => false
        ]);

        $this->mainTableName = $config['main_table'] ?? $this->mainTableName;

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
     * @param string $type
     * @return bool
     */
    public function migrate($type = Client::ALL)
    {
        $JsonExtractor = new JsonExtractor(new Json($this->file_content, $this->mainTableName), $this->mainTableName);
        $JsonExtractor->toMysqlTables();

        switch ($type) {
            case Client::DATA_ONLY:
                $JsonExtractor->toMysqlData();
                $this->insertData($JsonExtractor);
                break;
            case Client::TABLE_ONLY:
                $this->createTables($JsonExtractor);
                break;
            default:
                $JsonExtractor->toMysqlData();
                $this->createTables($JsonExtractor);
                $this->insertData($JsonExtractor);
                break;
        }

        return true;
    }

    /**
     * @param JsonExtractor $JsonExtractor
     */
    private function createTables(JsonExtractor $JsonExtractor)
    {
        $this->capsule::schema()->disableForeignKeyConstraints();

        //create tables
        foreach ($JsonExtractor->getTablesArray() as $TableName => $TableColumn) {
            $this->capsule::schema()->dropIfExists($TableName);
            $this->capsule::schema()->create($TableName, function ($table) use ($TableColumn) {
                foreach ($TableColumn as $column_item) {
                    switch ($column_item['type']) {
                        case 'int':
                            $table->int($column_item['name']);
                            break;
                        case 'primary_key':
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
                        case 'foreign_key':
                            $table->integer($column_item['name'])->unsigned();
                            $table->foreign($column_item['name'])->references('id')->on($column_item['ref']);
                            break;
                        default:
                            $table->text($column_item['name'])->nullable();
                            break;

                    }
                }
            });
        }

        $this->capsule::schema()->enableForeignKeyConstraints();
    }

    /**
     * @param JsonExtractor $JsonExtractor
     * @return bool
     */
    private function insertData(JsonExtractor $JsonExtractor)
    {
        $this->capsule::schema()->disableForeignKeyConstraints();
        foreach ($JsonExtractor->getDataArray() as $TableName => $TableData) {
            $this->capsule::table($TableName)->insert($TableData);
        }
        $this->capsule::schema()->enableForeignKeyConstraints();

        return true;
    }

    public function clearDatabase()
    {
    }
}
