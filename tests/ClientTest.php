<?php

require_once "helpers.php";
use Lifeeka\JSQL\Client;
use PHPUnit\Framework\TestCase;

/**
 * Class ClientTest
 */
class ClientTest extends TestCase
{
    public $Client;

    public function setUp()
    {

        $config['host'] = 'mariadb';
        $config['db'] = 'test';
        $config['username'] = 'test';
        $config['password'] = 'test';

        $this->Client = new Client($config);

        $this->assertFalse($this->Client->error);
    }

    public function testJsonExtractor()
    {
        $this->Client->clearDatabase();
        $this->Client->loadFile('sample/sample.json');
        $this->assertTrue($this->Client->migrate());
    }


}
