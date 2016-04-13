#!/bin/bash
#/Users/youzu/test/packworker/data/IOS/DZ/source/onlineMvFiles.sh 
#$1 /Users/youzu/test/packworker/data/IOS/DZ/package/16258119593285477137_online
#$2 /Users/youzu/test/packworker/data/IOS/DZ/app
#$3 16258119593285477137
#$4 /Users/youzu/test/packworker/data/IOS/DZ/source/packages/
mkdir -p $1;
mkdir -p $4;
echo "cp -rf ${4}${3}_online/${3}_online.ipa $1";
cp -rf ${4}${3}_online/${3}_online.ipa $1;
echo "cp -rf $1 $1.bak";
cp -rf $1 $1.bak;
echo "mv $1 $2";
mv $1 $2;