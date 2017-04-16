#!/bin/bash

files=$(echo $1 | tr "," "\n")
sudo mkdir /tmp/ayat
sudo chown -R www-data /tmp/ayat
rm $2
cd /tmp/ayat

for addr in $files
do
    wget $addr
done

for f in ./*; do echo "file '$f'" >> mylist.txt; done
ffmpeg -f concat -safe 0 -i mylist.txt -c copy $2

rm *
