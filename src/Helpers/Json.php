<?php

namespace Lifeeka\JSQL\Helpers;

/**
 * Class MysqlExtractor.
 */
class Json
{
    public $json_text;
    public $json_array;
    public $main_table_name = "main";

    /**
     * Json constructor.
     *
     * @param string $json
     */
    public function __construct(String $json)
    {
        $this->json_text = $json;
        $this->json_text = json_encode($this->addID($this->json_text));
        $this->json_text = json_encode($this->addForeign($this->json_text));
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
     * @return array
     */
    private function addID($array)
    {
        if (!is_array($array))
            $array = json_decode($array, true);

        $return_array = $array;

        $index = 1;

        foreach ($array ?? [] as $key => $array_item) {
            if (is_array($array_item)) {
                $array_item = $this->addID($array_item);

                if (empty($array_item['id']) && empty($array_item[0]))
                    $array_item = ['id' => $index++] + $array_item;

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
    private function addForeign($array, $parent_table = false, $parent_key = false)
    {
        if (!is_array($array))
            $array = json_decode($array, true);

        $return_array = $array;
        foreach ($array ?? [] as $key => $array_item) {

            $table_name = $parent_table ? $parent_table : (!is_numeric($key) ? $key : "main");

            if (is_array($array_item)) {
                $array_item = $this->addForeign($array_item, $table_name, $array_item['id'] ?? ($parent_key ?? false));
                $return_array[$key] = $array_item;


                if ($parent_key && empty($array_item[0])) {
                    $return_array[$key] = $array_item + [$parent_table . '_id' => $parent_key];
                }


            }
        }

        return $return_array;

    }
}
