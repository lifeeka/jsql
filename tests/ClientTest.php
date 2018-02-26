<?php

use lifeeka\jsql\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public $Client;

    public function setUp()
    {
        //ini_set('memory_limit', '2048M');

        $config['host'] = '127.0.0.1';
        $config['db'] = 'test';
        $config['username'] = 'test';
        $config['password'] = 'test';

        $this->Client = new Client($config);

        $this->assertFalse($this->Client->error);
    }

    public function testJsonExtractor()
    {
        $this->Client->clearDatabase();
        $this->Client->loadFile('sample2.json');
        $this->Client->migrate();
    }


}
