#! /bin/bash

DIR=$(dirname $(readlink -f $0))
VERBOSE="-p"

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

phpdoc $VERBOSE -d lib/ju1ius -t doc \
  --defaultpackagename Css --sourcecode \
  --title "ju1ius\Css Documentation"

popd >/dev/null
