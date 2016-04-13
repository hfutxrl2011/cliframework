#!/bin/bash
#security unlock-keychain -p 11111111 /Users/youzu/Library/Keychains/login.keychain
#$1 desc $2 taskid   $3  src $4 app_path $5 plist

#/Users/youzu/test/packworker/data/IOS/DZ/source/mvFiles.sh 
#$1 /Users/youzu/test/packworker/data/IOS/DZ/package/16258119593285477137
#$2 16258119593285477137
#$3 /Users/youzu/Desktop/IOSPACKAGE/package/16258119593285477137
#$4 /Users/youzu/test/packworker/data/IOS/DZ/app
#$5 plist
#$6 ipaUrl
#$7 logoUrl
#$8 appName

mkdir -p $1;
cd $1;
echo "tar -czf $2.tar.gz $3";
tar -czf $2.tar.gz $3;

echo "cp -r $3/$2.ipa $1";
cd $3;
cp -rf $2.ipa $1;

echo "cp -rf $1 $1.bak";
cp -rf $1 $1.bak;

echo "mv $1 $4";
mv $1 $4;
