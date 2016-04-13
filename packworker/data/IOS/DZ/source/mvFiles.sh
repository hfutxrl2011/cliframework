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

#echo "cp -r $5 $1/$2.plist";
#cp -r $5 $2.plist;
#echo "sed -i 's/ipaUrl/${6}/g' $2.plist";
#echo "sed -i 's/logoUrl/${7}/g' $1/$2.plist";
#echo "sed -i 's/appName/${8}/g' $1/$2.plist";
#sed "s/ipaUrl/${6}/g" $2.plist > $2.plist;
#sed "s/logoUrl/${7}/g" $2.plist > $2.plist;
#sed "s/appName/${8}/g" $2.plist > $2.plist;
#defaults write $2.plist ipaUrl $6;
#defaults write $2.plist logoUrl $6;
#defaults write $2.plist appName $6;

echo "tar -czf $2.tar.gz $3/Payload not do";
#tar -czf $2.tar.gz $3/Payload;
touch $2.tar.gz;

echo "cp -r $3/$2.ipa $1";
cd $3;
cp -rf $2.ipa $1;

echo "cp -rf $1 $1.bak";
cp -rf $1 $1.bak;

echo "mv $1 $4";
mv $1 $4;
