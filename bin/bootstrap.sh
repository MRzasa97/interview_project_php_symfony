#!/bin/bash

set -e

BIN_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_DIR="$(realpath $BIN_DIR'/../')/";

cd $PROJECT_DIR

bin/download-composer

./composer.phar install
