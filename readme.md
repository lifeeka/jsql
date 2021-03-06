<p align="center"><img src="https://i.imgur.com/hGzLkvc.png"></p>

<br>

[![Build Status](https://travis-ci.org/lifeeka/jsql.svg?branch=master)](https://travis-ci.org/lifeeka/jsql)
[![StyleCI](https://styleci.io/repos/122715777/shield?branch=master)](https://styleci.io/repos/122715777)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/lifeeka/jsql/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/lifeeka/jsql/?branch=master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/lifeeka/jsql/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)
[![Latest Stable Version](https://poser.pugx.org/lifeeka/jsql/v/stable)](https://packagist.org/packages/lifeeka/jsql)
[![Total Downloads](https://poser.pugx.org/lifeeka/jsql/downloads)](https://packagist.org/packages/lifeeka/jsql)
[![License](https://poser.pugx.org/lifeeka/jsql/license)](https://packagist.org/packages/lifeeka/jsql)

## JSQL
Convert Json Object to SQL.

### Installation

```
composer require lifeeka/jsql:dev-master
```

### Usage
```php
<?php

    $config['host'] = '127.0.0.1';
    $config['db'] = 'test';
    $config['username'] = 'test';
    $config['password'] = 'test';
    
    $Client = new Lifeeka\JSQL\Client($config);
    $Client->loadFile('sample/sample2.json');
    $Client->migrate();
        
```

### Tests
```
./vendor/bin/phpunit
```


### Contributing
Contributions are welcome! 😍

### Todos

 - Convert SQL to Json 

### License

This project is licensed under the MIT License
