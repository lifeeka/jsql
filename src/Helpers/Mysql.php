<?php

namespace lifeeka\jsql\Helpers;


/**
 * Class Mysql
 * @package lifeeka\jsql\Helpers
 */
class Mysql
{
    var $mysql_text;
    var $mysql_array;


    /**
     * Json constructor.
     * @param String $mysql
     */
    function __construct(String $mysql)
    {
        $this->mysql_text = $mysql;
    }

    /**
     * @return mixed
     */
    function toArray(){
        return json_decode($this->mysql_array);
    }
}