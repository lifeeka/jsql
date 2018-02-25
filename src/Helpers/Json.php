<?php

namespace lifeeka\jsql\Helpers;

/**
 * Class MysqlExtractor.
 */
class Json
{
    public $json_text;
    public $json_array;

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
        return json_decode($this->json_text);
    }
}
