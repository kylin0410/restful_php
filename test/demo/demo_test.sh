#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
echo "Change to $DIR"
cd $DIR

echo "Run python test for demo app."
python -W ignore -m unittest discover -vf baseline
