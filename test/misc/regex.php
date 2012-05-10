<?php

$subject = "foo bar bà€œz baz";
$pattern = '\s+';
$position = 13;

mb_regex_encoding('utf-8');
mb_internal_encoding('utf-8');
mb_ereg_search_init($subject, '\G'.$pattern, 'msi');
mb_ereg_search_setpos($position);
var_dump(mb_ereg_search_regs());

