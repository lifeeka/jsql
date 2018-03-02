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
            return (object) [
                $this->main_table_name=>$json
            ];
        }
        return $json;
    }
}
