<?php

namespace Lifeeka\JSQL\Helpers;

use Lifeeka\JSQL\Extractor\JsonExtractor;

/**
 * Class MysqlExtractor.
 */
class Json
{

    private $json_text;
    private $main_table_name = "main";
    private $foreign_keys = [];
    private $increment = 0;


    /**
     * Json constructor.
     * @param String $json
     * @param $mainTableName
     */
    public function __construct(String $json, $mainTableName)
    {
        $this->json_text = $json;
        $this->json_text = json_encode($this->addID($this->json_text));
        $this->json_text = json_encode($this->addForeign($this->json_text));
        $this->main_table_name = $mainTableName;
    }

    /**
     * @return mixed
     */
    public function toArray()
    {
        return json_decode($this->json_text, true);
    }

    /**
     * @return mixed
     */
    public function toObject()
    {
        $json_object = $this->validate(json_decode($this->json_text));
        return $json_object;
    }

    /**
     * @return mixed
     */
    public function getForeignKeys()
    {
        return $this->foreign_keys;
    }


    /**
     * @param $json
     * @return object
     */
    public function validate($json)
    {
        if (is_array($json)) {
            return (object)[
                $this->main_table_name => $json
            ];
        }
        return $json;
    }


    /**
     * @param $array
     * @param bool $parent_table
     * @return array|mixed
     */
    private function addID($array, $parent_table = false)
    {

        if (!is_array($array))
            $array = json_decode($array, true);//if this is not the first time decode the text

        $return_array = $array;//return array

        foreach ($array ?? [] as $key => $array_item) {

            if (!is_numeric($key) && $parent_table)//single array table, no column
                $table_name = $parent_table . '_' . $key;
            elseif ($parent_table)//multiple array items
                $table_name = $parent_table;
            else {//first time loop
                $table_name = $this->main_table_name;
            }

            if (is_array($array_item)) {//if this is a array
                $array_item = $this->addID($array_item, $table_name, $this->increment);//recursive the array
                if (empty($array_item['id']) && empty($array_item[0])) {//if this is a single array with no child
                    $array_item = ['id' => $this->increment++] + $array_item;//add id
                }

                if(isset($array_item[0]) && !is_array($array_item[0]) && !is_object($array_item[0])){//reference table


                    $array_item = (object)array_map(function ($item){
                        return (object)[
                            'id' => $this->increment++,
                            'value'=>$item
                        ];
                    },$array_item); 
                }

                $return_array[$key] = $array_item;
            }
        }

        return $return_array;

    }

    /**
     * @param $array
     * @param bool $parent_table
     * @param bool $parent_key
     * @return array|mixed
     */
    private function addForeign($array, $parent_table = null, $parent_key = false)
    {
        if (!is_array($array))
            $array = json_decode($array, true);

        $return_array = $array;
        foreach ($array ?? [] as $key => $array_item) {



            if (!is_numeric($key) && $parent_table)//single array table, no column
                $table_name = $parent_table . '_' . $key;
            elseif ($parent_table)//multiple array items
                $table_name = $parent_table;
            elseif (!is_numeric($key))  //first time loop
                $table_name = $key;
            else {//first time loop
                $table_name = $this->main_table_name;
            }


            if (is_array($array_item)) {
                $array_item = $this->addForeign($array_item, $table_name, $array_item['id'] ?? ($parent_key ?? false));
                $return_array[$key] = $array_item;


                if ($parent_key && empty($array_item[0])) {
                    $return_array[$key] = $array_item + ['foreign_key' => $parent_key];
                }

                if (!is_numeric($key)) {
                    $this->foreign_keys[JsonExtractor::snakeCase($table_name)] = [
                        'ref' => $parent_table,
                        'name' => JsonExtractor::snakeCase('foreign_key')
                    ];
                }


            }
        }

        return $return_array;

    }
}
