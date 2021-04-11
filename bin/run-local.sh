#!/bin/bash

APP_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )/../" && pwd )"

PORT=9090
HOST=localhost



xdg-open "http://$HOST:$PORT"


cd "$APP_DIR"

source ".env"

echo "DATA_ROOT=$DATA_ROOT"

echo "Starting..."

while :
do
	DATA_ROOT="$DATA_ROOT" \
    php -S $HOST:$PORT -t "public"

    echo "Restarting... Press [CTRL+C] to abort"
	sleep 1
done
