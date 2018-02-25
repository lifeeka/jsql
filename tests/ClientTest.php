<?php

use lifeeka\jsql\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public $Client;

    public function setUp()
    {

        $config['host'] = '127.0.0.1';
        $config['db'] = 'test';
        $config['username'] = 'root';
        $config['password'] = 'secret';

        $this->Client = new Client($config);

        $this->assertFalse($this->Client->error);
    }

    public function testJsonExtractor()
    {
        $this->Client->clearDatabase();
        $this->Client->loadFile('sample.json');
        $this->Client->toMysql();

        ($this->Client->toMysql());

        $this->assertInstanceOf(\PDOStatement::class, $this->Client->migrate());
    }
}
