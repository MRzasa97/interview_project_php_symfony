#!/bin/bash

set -e

BIN_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_DIR="$(realpath $BIN_DIR'/../')/";

cd $PROJECT_DIR

./bin/bootstrap.sh

exec php -S 0.0.0.0:80 -t public/
