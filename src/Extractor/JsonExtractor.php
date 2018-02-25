<?php

namespace lifeeka\jsql\Extractor;

use lifeeka\jsql\Helpers\Json;

/**
 * Class JsonExtractor.
 */
class JsonExtractor
{
    public $json;
    public $need_id = true;
    public $snake_case_column = true;
    public $snake_case_table = true;
    public $main_table_name = "main";

    /**
     * JsonExtractor constructor.
     *
     * @param Json $json
     */
    public function __construct(Json $json)
    {
        $this->json = $json;
    }


    /**
     * @param bool $data
     * @param string $prefix
     * @return string
     */
    public function toMysqlTables($data = false, $prefix = '')
    {
        $sql_tables = '';

        if (!$data) {//if this is not a recursive
            $data = $this->json->toObject();
        }

        foreach ($data as $key => $value) {//loop the data

            $table_name = is_numeric($key) ? $this->main_table_name : $key;

            if (is_array($value) && is_object($value[0])) {//check whether it's a array and it's firs element is a object
                $sql_tables .= $this->getTable($prefix . $table_name, $value);//get table sql
                $sql_tables .= $this->toMysqlTables($this->getHighestColumnArray($value), $prefix . $table_name . '_');//get it inside tables
            } elseif (is_array($value)) {//if it's a array and  firs element is not a object
                $sql_tables .= $this->getTable($prefix . $table_name, $value);
            } elseif (is_object($value)) {//if it's a object and  firs element is not a object
                $sql_tables .= $this->getTable($prefix . $table_name, $value);
            }
        }

        return $sql_tables;
    }

    /**
     * @param $table
     * @param $data
     *
     * @return string
     */
    public function getTable($table, $data)
    {
        $sql = '';
        $column_sql = '';

        $column = $this->getColumn($this->getHighestColumnArray($data));

        if ($this->need_id && array_search('id', array_column($column, 'name'))) {
            $column_sql .= '`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,';
            $column_sql .= 'PRIMARY KEY (`id`)';
            $column_sql .= $column ? ',' : '';
        }

        foreach ($column as $count => $column_item) {

            if ($this->snake_case_column)
                $column_item['name'] = $this->snakeCase($column_item['name']);

            switch ($column_item['type']) {

                case 'string':
                    $column_sql .= "`{$column_item['name']}` TEXT COLLATE 'utf8_unicode_ci'";
                    break;
                case 'integer':
                    $column_sql .= "`{$column_item['name']}` int(20)";
                    break;
                case 'boolean':
                    $column_sql .= "`{$column_item['name']}` INT(1) COLLATE 'utf8_unicode_ci'";
                    break;
                case 'double':
                    $column_sql .= "`{$column_item['name']}` float(50) COLLATE 'utf8_unicode_ci'";
                    break;
                default :
                    print_r($column_item);
            }


            if ((count($column) - $count) > 1) {//check whether this is not the last one
                $column_sql .= ',';
            }
        }

        if ($this->snake_case_table)
            $table = $this->snakeCase($table);

        $sql .= "CREATE TABLE `$table` ($column_sql);\n";


        //get table data array
        $table_data = $this->getData($data, $column);

        $column_string = $this->getStringFromColumns($column);;
        $data_string = $this->getStringFromData($table_data);

        //$sql .= "INSERT INTO `$table` ($column_string) $data_string;\n";

        return $sql;
    }

    /**
     * @param $data
     *
     * @return array
     */
    public function getColumn($data)
    {
        $Columns = [];

        if (is_object($data)) {
            foreach ($data ?? [] as $Column => $Value) {
                if (!is_array($Value) && !is_object($Value) && !empty($Column) && !is_numeric($Column)) {
                    $Columns[] = ['name' => $Column, 'type' => gettype($this->getActualDataType($Value,""))];
                }
            }
        } elseif (is_array($data)) {
            $Columns[] = ['name' => 'value', 'type' => gettype($this->getActualDataType($data[0],""))];
        }

        return $Columns;
    }

    /**
     * @param $data
     * @param $column
     * @return array
     */
    function getData($data, $column)
    {

        $values = [];

        $index = 0;
        foreach ($data as $row_item) {
            foreach ($column as $column_item) {
                switch (is_object($row_item)) {
                    case true:
                        $values[$index][] = $this->getActualDataType(($row_item->{$column_item['name']}) ?? null, null);
                        break;
                    case false:
                        $values[$index][] = $this->getActualDataType($row_item, null);
                        break;
                }

            }
            $index++;
        }

        return $values;

    }

    /**
     * @param $input
     *
     * @return string
     */
    public static function snakeCase($input)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }

        return implode('_', $ret);
    }


    /**
     * @param $array
     * @return bool|mixed
     */
    public static function getHighestColumnArray($array)
    {
        if (is_object($array) || (is_array($array) && !is_object($array[0]) && !is_array($array[0])))
            return $array;

        $Highest = false;
        $ColumnCount = 0;

        foreach ($array as $array_item) {
            if ($ColumnCount < count($array_item)) {
                $Highest = $array_item;
            }
        }

        return $Highest;
    }

    /**
     * @param $array
     * @return string
     */
    public static function getStringFromData($array)
    {
        $String = [];

        foreach ($array as $item_row) {
            $value = [];
            foreach ($item_row as $item) {
                if (empty($item) && !is_numeric($item)) {
                    $value[] = "null";
                } elseif (is_numeric($item))
                    $value[] = $item;
                else
                    $value[] = '"' . addcslashes($item, "W") . '"';
            }
            $String[] = '(' . implode(",", $value) . ')';
        }

        return "values" . implode(", ", $String);
    }

    /**
     * @param $array
     * @return string
     */
    public static function getStringFromColumns($array)
    {

        $String = [];

        foreach ($array as $column) {
            $String[] = "`" . JsonExtractor::snakeCase($column['name']) . "`";
        }

        return implode(",", $String);
    }

    /**
     * @param $Data
     * @param null $empty_val
     * @return int|null|string
     */
    public static function getActualDataType($Data, $empty_val = null)
    {
        $Data = trim($Data);
        if (is_numeric($Data)) {
            return $Data + 0;
        } elseif (empty($Data))
            return $empty_val;
        else
            return (string)$Data;
    }
}
