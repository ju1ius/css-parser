<?php

$str = file_get_contents(__DIR__.'/../files/full/02.css');

printf("String Memory: %s\n", memory_get_usage(true));

$a = new SplFixedArray(mb_strlen($str, "utf-8"));
$a->fromArray(mb_split('\r\n|\n', $str));
$str = null;

printf("Array Memory: %s\n", memory_get_usage(true));

$a = null;
printf("Memory: %s\n", memory_get_usage(true));
printf("Memory peak: %s\n", memory_get_peak_usage(true));
