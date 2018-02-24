<?php

namespace lifeeka\jsql\Helpers;

/**
 * Class MysqlExtractor
 */
class Json
{

    var $json_text;
    var $json_array;


    /**
     * Json constructor.
     * @param String $json
     */
    function __construct(String $json)
    {
        $this->json_text = $json;
    }

    /**
     * @return mixed
     */
    function toArray()
    {
        return json_decode($this->json_text, true);
    }

    /**
     * @return mixed
     */
    function toObject()
    {
        return json_decode($this->json_text);
    }


}