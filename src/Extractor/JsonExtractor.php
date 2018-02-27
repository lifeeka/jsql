<?php

namespace lifeeka\jsql\Extractor;

use lifeeka\jsql\Helpers\Json;

/**
 * Class JsonExtractor.
 */
class JsonExtractor
{
    public $json;

    private $table = [];
    private $data = [];

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
     * @return array
     */
    public function getTablesArray()
    {
        return $this->table;
    }

    /**
     * @return array
     */
    public function getDataArray()
    {
        return $this->data;
    }


    /**
     * @param bool $data
     * @param string $prefix
     * @return string
     */
    public function toMysqlTables($data = false, $prefix = '')
    {
        if (!$data) {//if this is not a recursive
            $data = $this->json->toObject();
        }


        foreach ($data as $key => $value) {//loop the data

            $table_name = is_numeric($key) ? $this->main_table_name : $key;

            if (is_array($value) && is_object($value[0])) {//check whether it's a array and it's firs element is a object

                $table_data = $this->getTable($prefix . $table_name, $value);//get table sql
                $this->table[$table_data['tables']['name']] = $table_data['tables']['column'];
                $this->data[$table_data['data']['table']] = $table_data['data']['data'];

                $this->toMysqlTables($this->getHighestColumnArray($value), $prefix . $table_name . '_');//get it inside tables

            } elseif (is_array($value) || is_object($value)) {//if it's a array and  firs element is not a object

                $table_data = $this->getTable($prefix . $table_name, $value);
                $this->table[$table_data['tables']['name']] = $table_data['tables']['column'];
                $this->data[$table_data['data']['table']] = $table_data['data']['data'];
            }
        }

    }


    /**
     * @param $table
     * @param $data
     * @return array
     */
    public function getTable($table, $data)
    {
        $column = $this->getColumn($this->getHighestColumnArray($data));


        if ($this->snake_case_table)
            $table = $this->snakeCase($table);

        $table_data = $this->getData($data, $column);

        $last_columns = $column;
        if ($this->need_id && array_search('id', array_column($column, 'name')) === false) {

            $last_columns[] = [
                'name' => "id",
                'type' => "int"
            ];
        }

        return [
            'tables' => [
                'name' => $table,
                'column' => $last_columns
            ],
            'data' => [
                'table' => $table,
                'data' => $table_data
            ]
        ];
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
                    $Columns[] = ['name' => $Column, 'type' => gettype($this->getActualDataType($Value, ""))];
                }
            }
        } elseif (is_array($data)) {
            $Columns[] = ['name' => 'value', 'type' => gettype($this->getActualDataType($data[0], ""))];
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
                        $values[$index][$column_item['name']] = $this->getActualDataType(($row_item->{$column_item['name']}) ?? null, null);
                        break;
                    case false:
                        $values[$index][$column_item['name']] = $this->getActualDataType(($data->{$column_item['name']}) ?? null, null);
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
        $ColumnCount = false;
        $HighestSubCount = false;

        foreach ($array as $array_item) {

            $current_sub = 0;
            //check how many array/object have
            foreach ($array_item as $SubTableName => $SubArrayItem) {
                if (is_array($SubArrayItem) || is_object($SubArrayItem)) {
                    $current_sub = $current_sub + 2;
                }

            }
            //check how many column have
            if ($ColumnCount <= count($array_item) || !$Highest) {
                if ($current_sub > $HighestSubCount || !$Highest) {
                    $Highest = $array_item;
                    $HighestSubCount = $current_sub;
                    $ColumnCount = count($array_item);
                }
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
     * @param $empty_val
     * @return int|string
     */
    public static function getActualDataType($Data, $empty_val)
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
