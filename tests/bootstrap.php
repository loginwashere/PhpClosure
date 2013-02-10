<?php
define('DS', DIRECTORY_SEPARATOR);

define('PATH_ROOT', realpath(dirname(__FILE__) . DS . '..' . DS));
define('PATH_APPLICATION', PATH_ROOT . DS .'src');
define('PATH_VENDOR', PATH_ROOT . DS . 'vendor');

// init loader
require_once PATH_VENDOR . '/autoload.php';