<?php

namespace lifeeka\jsql\Helpers;

/**
 * Class Mysql.
 */
class Mysql
{
    public $mysql_text;
    public $mysql_array;

    /**
     * Json constructor.
     *
     * @param string $mysql
     */
    public function __construct(String $mysql)
    {
        $this->mysql_text = $mysql;
    }

    /**
     * @return mixed
     */
    public function toArray()
    {
        return json_decode($this->mysql_array);
    }
}
