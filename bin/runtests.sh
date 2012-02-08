#! /bin/bash

__DIR__=$(dirname $(readlink -f $0))

pushd $__DIR__/../test >/dev/null
phpunit --configuration testsuite.xml
popd >/dev/null
