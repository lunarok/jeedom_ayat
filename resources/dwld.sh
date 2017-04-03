#!/bin/bash

files=$(echo $1 | tr "," "\n")
sudo mkdir /tmp/ayat

for addr in $files
do
    wget $addr
done

ffmpeg -i "concat:$1" -acodec copy $2

rm *
