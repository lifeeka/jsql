<?php

use lifeeka\jsql\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public $Client;

    public function setUp()
    {
        $env = new Dotenv\Dotenv('./');
        $env->load();

        $config['host'] = getenv('DB_HOST');
        $config['db'] = getenv('DB_DATABASE');
        $config['username'] = getenv('DB_USERNAME');
        $config['password'] = getenv('DB_PASSWORD');

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
