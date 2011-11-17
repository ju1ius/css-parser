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

pushd $DIR/.. >/dev/null

if [[ ! -d ./doc ]];
then
  mkdir -p ./doc
fi
rm -r doc/*

docblox -d lib/CSS -t doc \
  --defaultpackagename CSS --sourcecode \
  --title "CSSParser Documentation"

popd >/dev/null
