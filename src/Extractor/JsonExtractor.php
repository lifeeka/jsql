<?php

namespace lifeeka\jsql\Extractor;

use lifeeka\jsql\Helpers\Json;


/**
 * Class JsonExtractor
 * @package lifeeka\jsql\Extractor
 */
class JsonExtractor
{
    var $json;
    var $need_id = true;

    /**
     * JsonExtractor constructor.
     * @param Json $json
     */
    function __construct(Json $json)
    {
        $this->json = $json;
    }


    /**
     * @param bool $data
     * @return string
     */
    function toMysqlTables($data = false, $prefix = "")
    {
        $sql_tables = "";

        if (!$data) {
            $data = $this->json->toObject();
        }

        foreach ($data as $key => $value) {

            if (is_array($value) && is_object($value[0])) {
                $sql_tables .= $this->getTables($prefix.$key, $this->getHighestColumnArray($value));
                $sql_tables .= $this->toMysqlTables($this->getHighestColumnArray($value),$prefix.$key.'_');
            } elseif (is_array($value) ) {
                $sql_tables .= $this->getTables($prefix.$key, $value);
            }elseif (is_object($value) ) {
                $sql_tables .= $this->getTables($prefix.$key, $value);
            }
        }


        return $sql_tables;
    }

    /**
     * @param $table
     * @param $data
     * @return string
     */
    function getTables($table, $data)
    {
        $sql = "";
        $column_sql = "";

        $column = $this->getColumn($data);

        if ($this->need_id) {
            $column_sql .= "`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,";
            $column_sql .= "PRIMARY KEY (`id`)";
            $column_sql .= $column ? "," : '';
        }



        foreach ($column as $count => $column_item) {


            if ($column_item['type'] == "integer")
                $column_sql .= "`{$column_item['name']}` int(20)";
            elseif ($column_item['type'] == "string")
                $column_sql .= "`{$column_item['name']}` VARCHAR(50) COLLATE 'utf8_unicode_ci'";
            elseif ($column_item['type'] == "boolean")
                $column_sql .= "`{$column_item['name']}` BIT(1) COLLATE 'utf8_unicode_ci'";
            elseif ($column_item['type'] == "double")
                $column_sql .= "`{$column_item['name']}` float(50) COLLATE 'utf8_unicode_ci'";

            if ((count($column) - $count) > 1) {
                $column_sql .= ",";
            }
        }
        $table = $this->toUnderscore($table);
        $sql .= "CREATE TABLE `$table` ($column_sql);\n";

        return $sql;
    }


    /**
     * @param $data
     * @return array
     */
    function getColumn($data)
    {

        $Columns = [];

        if (is_object($data)) {

            foreach ($data ?? [] as $Column => $Value) {

                if (!is_array($Value) && !is_object($Value) && !empty($Column) && !is_numeric($Column)) {
                    $Columns[] = ['name' => $this->toUnderscore($Column), "type" => gettype($Value)];
                }
            }
        }
        elseif(is_array($data)){
            $Columns[] = ['name' => "value", "type" => gettype($data[0])];
        }

        return $Columns;
    }

    /**
     * @param $array
     * @return bool
     */
    static function getHighestColumnArray($array)
    {
        $Highest = false;
        $ColumnCount = 0;


        foreach ($array as $array_item) {
            if ($ColumnCount < count($array_item))
                $Highest = $array_item;
        }

        return $Highest;

    }

    /**
     * @param $input
     * @return string
     */
    static function toUnderscore($input)
    {


        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }

}