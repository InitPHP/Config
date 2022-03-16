# InitPHP Config

Advanced configuration manager library.

[![Latest Stable Version](http://poser.pugx.org/initphp/config/v)](https://packagist.org/packages/initphp/config) [![Total Downloads](http://poser.pugx.org/initphp/config/downloads)](https://packagist.org/packages/initphp/config) [![Latest Unstable Version](http://poser.pugx.org/initphp/config/v/unstable)](https://packagist.org/packages/initphp/config) [![License](http://poser.pugx.org/initphp/config/license)](https://packagist.org/packages/initphp/config) [![PHP Version Require](http://poser.pugx.org/initphp/config/require/php)](https://packagist.org/packages/initphp/config)

## Requirements

- PHP 7.4 or higher
- [ParameterBag Library](https://github.com/initphp/parameterbag)

## Installation

```php 
composer require initphp/config
```

## Usage

### Config Classes

```php 
class MyAppConfig extends \InitPHP\Config\Classes
{
    public $url = 'http://lvh.me';
    
    public $name = 'LocalHost';
    
    public $db = [
        'host'  => 'localhost',
        'user'  => 'root'
    ];
    
    // ...
}
```

```php 
$config = new MyAppConfig();

echo $config->get('url'); 
// Output : "http://lvh.me"

echo $config->get('details', 'Not Found'); 
// Output : "Not Found"

echo $config->get('db.host');
// Output : "localhost"

if($config->has('name')){
    echo $config->get('name');
    // Output : "LocalHost"
}
```

### Config Library

#### `Config::setClass()`

Lets you define properties of an object or class as a configuration.

```php 
public function setClass(string|object $classOrObject): self;
```

**_Example :_**

```php 
namespace App\Config;

class AppConfig
{
    public $url = 'http://lvh.me';
}

class Database 
{
    public $host = 'localhost';
}
```

```php 
use \InitPHP\Config\Config;

// Class
Config::setClass(\App\Config\AppConfig::class);

// or Object
Config::setClass(new \App\Config\Database());

Config::get('appconfig.url');

Config::get('database.host');
```

#### `Config::setArray()`

Imports an array.

```php 
public function setArray(?string $name, array $assoc = []): self;
```

**_Example :_** 

```php 
require_once "vendor/autoload.php";
use \InitPHP\Config\Config;

$configs = [
    'url'   => 'http://lvh.me',
    'db'    => [
        'host'  => 'localhost',
        'user'  => 'db_user',
        'pass'  => '',
        'name'  => 'database'
    ],
];
Config::setArray('site', $configs);


Config::get('site.url');
Config::get('site.db.host', '127.0.0.1');
Config::get('site.db.user', 'root');
```

#### `Config::setFile()`

Loads the configurations in the PHP file, which returns an associative array.

```php 
public function setFile(?string $name, string $path): self;
```

**_Example :_** 

`public_html/db_config.php` :

```php 
<?php 
return [
    'HOST'  => 'localhost',
    'USER'  => 'root',
    'PASS'  => '',
    'NAME'  => 'database'
];
```

```php 
require_once "vendor/autoload.php";
use \InitPHP\Config\Config;

Config::setFile('DB', __DIR__ . '/public_html/db_config.php');

// Usage : 
Config::get('db.host');
```

#### `Config::setDir()`

Loads PHP files in a directory as configuration files.

```php 
public function setDir(?string $name, string $path, array $exclude = []): self;
```

**_Example :_** 

`public_html/config/db.php` :

```php 
<?php 
return [
    'HOST'  => 'localhost',
    'USER'  => 'root',
    'PASS'  => '',
    'NAME'  => 'database'
];
```

`public_html/config/site.php` :

```php 
<?php 
return [
    'URL'   => 'http://lvh.me',
    // ...
];
```


```php 
require_once "vendor/autoload.php";
use \PHPConfig\Config;

Config::setDir('app', __DIR__ . '/public_html/config/');

// Usage : 
Config::get('app.site.url');
Config::get('app.db.host');
```

## Credit

- [Muhammet ŞAFAK](https://www.muhammetsafak.com.tr) <<info@muhammetsafak.com.tr>>

## License

Copyright &copy; 2022 [MIT License](./LICENSE)
