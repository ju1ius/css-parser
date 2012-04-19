<?php

function _throw($msg)
{
  return new Exception($msg);
}



throw _throw('Foobar');
