#! /bin/bash

DIR=$(dirname $(readlink -f $0))
VERBOSE=""

while [[ $1 = -* ]]; do
  case "$1" in
    -v|--verbose)
      VERBOSE="--verbose"
      shift 1
    ;;
    *)
      echo "Error: Unknown option: $1" >&2
      exit 1
    ;;
  esac
done

pushd $DIR/../test >/dev/null
phpunit $VERBOSE --configuration testsuite.xml
popd >/dev/null
