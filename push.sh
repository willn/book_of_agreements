#!/bin/bash

# push a single file to production

if [ -z "$1" ]; then
	echo "file not specified"
	exit;
fi
if [ ! -f $1 ]; then
	echo "file does not exist";
	exit;
fi

FILE="${1/public\//}"
echo "$FILE";

rsync -e 'ssh -p 1022' -avz $1 gocoho@gocoho.org:/home/gocoho/public_html/boa/$FILE

