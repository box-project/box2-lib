<?php

$loader = require __DIR__ . '/../vendors/autoload.php';
$loader->add(null, __DIR__);

org\bovigo\vfs\vfsStreamWrapper::register();
