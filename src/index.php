<?php
define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', dirname(__FILE__));

require_once ROOT_PATH . '/vendor/autoload.php';
require_once ROOT_PATH . '/app/engine/Config.php';
require_once ROOT_PATH . '/app/engine/Init.php';
require_once ROOT_PATH . '/app/engine/Api.php';
require_once ROOT_PATH . '/sphinx_func.php';

if (php_sapi_name() !== 'cli') {
    $app = new Engine\Api();
} else {
    require_once ROOT_PATH . '/app/engine/Cli.php';
    $app = new Engine\Cli();
}

$app->run();
$app->getOutput();
