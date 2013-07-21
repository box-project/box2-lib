<?php

define('RES_DIR', __DIR__ . '/../../res');

$loader = require __DIR__ . '/../vendors/autoload.php';
$loader->add(null, __DIR__);

org\bovigo\vfs\vfsStreamWrapper::register();
