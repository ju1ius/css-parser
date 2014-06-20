<?php

require_once __DIR__.'/../lib/vendor/Symfony/Component/ClassLoader/UniversalClassLoader.php';
$loader = new Symfony\Component\ClassLoader\UniversalClassLoader();
$loader->registerNamespace(
    'ju1ius', array(
        __DIR__.'/../lib',
        __DIR__.'/../../libphp/lib'
    )
);
$loader->register();
