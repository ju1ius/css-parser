#!/bin/bash

__FILE__=$(readlink -f "$0")
__DIR__=$(dirname "$__FILE__")


$__DIR__/../vendor/bin/phpunit "$@"
