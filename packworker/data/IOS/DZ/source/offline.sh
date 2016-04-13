#!/bin/sh

#先接参数	name:文件夹名称 logopath:图标路径 logoname:图标名称 appname:应用名 launchImagePath:启动图路径 launchImageName:启动图名字 bbsName:论坛名称 navigationBarColor:配色 sourceAppPath:源代码路径 baseurl: 接口域名 urlpath:接口path channel:渠道包名称 appdownloadurl:app下载地址 bundleid:项目的bundleid pathforprovision:描述文件路径 provisionname:描述文件名称 TARGET:项目target CODE_SIGNING_IDENTITY:签名证书

#useage
#/Users/zhangsh/Desktop/IOSPACKAGE/package.sh aaa 随便 /Users/zhangsh/Desktop/500x500.png /Users/zhangsh/Desktop/6-c.png bbsname 65,65,65 "http://www.3body.com" "api/mobile/iyz_index.php" "appStore" "123"

name="${1}"
appname="${2}"
logopath="${3}"
launchImagePath="${4}"
bbsName="${5}"
navigationBarColor="${6}"
baseurl="${7}"
urlpath="${8}"
channel="${9}"
appkey="${10}"
sharekey_sina="${11}"
shareSecret_sina="${12}"
shareRedirecturi_sina="${13}"
shareKey_wechat="${14}"
shareSecret_wechat="${15}"
sharekey_qq="${16}"
shareSecret_qq="${17}"
#版本号
appversion="${18}"
#bundle id 即包名
bundleid="${19}"
#jpush的appkey 默认是“b7bf5ec527d73136c7a0944c”
jpush_appkey="${20}"
app_evn="${21}"
sourceAppPath="${22}"
#jpush的appsecret 默认是“2fec24cf15dc9125d145879e”
#jpush_appsecret="${21}"


#appdownloadurl="${10}"
#bundleid="${11}"
#pathforprovision="${12}"
#CODE_SIGNING_IDENTITY="${13}"
#output=`set`
#echo $output>/Users/youzu/Desktop/ximi/output.txt
#echo ">>>>>>">>/Users/youzu/Desktop/ximi/output.txt
#exit
#先把这些写死
appdownloadurl="https://itunes.apple.com/us/app/san-ti-she-qu/id997795570?l=zh&ls=1&mt=8"
#bundleid="com.youzu.test"
#pathforprovision="/Users/youzu/Desktop/IOSPACKAGE/k_commonTest_inhouse_pro.mobileprovision"
#CODE_SIGNING_IDENTITY="iPhone Distribution: Su zhou Zhengyou Network Technology Co. Ltd."
pathforprovision="/Users/youzu/Desktop/IOSPACKAGE/apns_discuz_pro.mobileprovision"
#CODE_SIGNING_IDENTITY="Apple Production IOS Push Services: com.youzu.test"
CODE_SIGNING_IDENTITY="iPhone Distribution: Su zhou Zhengyou Network Technology Co. Ltd."

#获取主目录
#这句话的用意就是当每次执行脚本的时候都要回到主目录
#因为如果目录不对的话不能实现复制粘贴等功能
#rootpath=$(cd `dirname $0`)
TARGET=Clan
provisionname=`basename ${pathforprovision}| awk -F'.' '{print $1}'`
#provisionname=`basename '/Users/youzu/Downloads/k_commonTest_inhouse_pro.mobileprovision' | awk -F'.' '{print $1}'`
#sourceAppPath="/Users/zhangsh/Desktop/IOSPACKAGE/BaseSourceCode_nonpod"
#sourceAppPath="/Users/youzu/Desktop/IOSPACKAGE/offline_base"
rootpath="/Users/youzu"
basepath="Desktop/IOSPACKAGE/package"
normalpath="${basepath}/${name}/Payload"

#很重要啊 没有这个 牛牛的电脑上就没有权限了
#security unlock-keychain -p 001 /Users/youzu/Library/Keychains/login.keychain>>/Users/youzu/Desktop/ximi/package/$name/keychain.txt

#拷贝母包
#Ditto比cp命令稍显高级和方便主要是基于以下几点：首先，它在复制过程中不仅能保留源文件（夹）的属性与权限，还能保留源文件的资源分支结构和文件夹的源结构。其次，此命令能确保文件（夹）被如实复制。另外，如果目标文件（夹）不存在，ditto将直接复制过去或创建新的文件（夹），相反，对于已经存在的文件（夹），命令将与目标文件（夹）合并。最后ditto还提供符号链接，使命令行重度使用都用起来更顺手。
#先清除掉遗留文件
rm -rf $rootpath/${basepath}/$name
#rm -rf /Users/youzu/Library/Developer/Xcode/DerivedData

ditto $sourceAppPath $rootpath/$normalpath

#过滤到svn的代码
#find . -type d -name ".svn"|xargs rm -rf
#find $rootpath/$normalpath/ -type d -name '.svn'|xargs rm -rf

#改变Logo尺寸并移动logo
#MAC中用Shell脚本批量裁剪各种尺寸的App图标的方法就是使用自带的sips工具，它是一个脚本图像处理系统，可用于查询和修改图像文件
iconname_array=("AppIcon29x29@2x.png" "AppIcon29x29@3x.png" "AppIcon40x40@2x.png" "AppIcon40x40@3x.png" "AppIcon60x60@2x.png" "AppIcon60x60@3x.png")
iconsize_array=("58 58" "87 87" "80 80" "120 120" "120 120" "180 180")

for ((i=0;i<${#iconname_array[@]};++i)); do
cp "${logopath}" $rootpath/$normalpath/${TARGET}/Images.xcassets/AppIcon.appiconset/${iconname_array[i]}
sips -s format png $rootpath/$normalpath/${TARGET}/Images.xcassets/AppIcon.appiconset/${iconname_array[i]} --out $rootpath/$normalpath/${TARGET}/Images.xcassets/AppIcon.appiconset/${iconname_array[i]}
sips -z ${iconsize_array[i]} $rootpath/$normalpath/${TARGET}/Images.xcassets/AppIcon.appiconset/${iconname_array[i]}
done

#改变启动图尺寸并移动启动图
launchname_array=("Default-480h@2x.png" "Default-568h@2x.png" "Default-667h@2x.png" "Default-736h@3x.png")
launchsize_array=("960 640" "1136 640" "1334 750" "2208 1242")


for ((i=0;i<${#launchname_array[@]};++i)); do
cp "${launchImagePath}" $rootpath/$normalpath/${TARGET}/Images.xcassets/LaunchImage.launchimage/${launchname_array[i]}
sips -s format png $rootpath/$normalpath/${TARGET}/Images.xcassets/LaunchImage.launchimage/${launchname_array[i]} --out $rootpath/$normalpath/${TARGET}/Images.xcassets/LaunchImage.launchimage/${launchname_array[i]}
sips -z ${launchsize_array[i]} $rootpath/$normalpath/${TARGET}/Images.xcassets/LaunchImage.launchimage/${launchname_array[i]}
done

#改变某个key的value
infoplistpath="${rootpath}/${normalpath}/${TARGET}/Info.plist"
/usr/libexec/PlistBuddy -c "Set :CFBundleDisplayName ${appname}" ${infoplistpath}
/usr/libexec/PlistBuddy -c "Set :CFBundleShortVersionString ${appversion}" ${infoplistpath}

echo "****************开始替换share appkey ${sharekey_qq}"

#改变分享的URL schemes
#ximi_wxUrlScheme=`'ibase=10;obase=16;${sharekey_qq}'|bc`
#QQ URL Scheme
ximi_UrlScheme_qq=`echo 'ibase=10;obase=16;'${sharekey_qq}''|bc`
echo "***************1 ${ximi_UrlScheme_qq}"

ximi_UrlScheme_qq_len=`echo ${#ximi_UrlScheme_qq}`
if [[ "${ximi_UrlScheme_qq_len}" == "8" ]]
then
ximi_UrlScheme_qq="QQ${ximi_UrlScheme_qq}"
else
ximi_UrlScheme_qq="QQ0${ximi_UrlScheme_qq}"
fi
echo "*********QQ*******${ximi_UrlScheme_qq}"

#微信URL scheme
ximi_UrlScheme_wx=${shareKey_wechat}
#新浪URL scheme
ximi_UrlScheme_sina="wb${sharekey_sina}"
echo "*********ximi_UrlScheme_wx*******${ximi_UrlScheme_wx}"
echo "*********ximi_UrlScheme_sina*******${ximi_UrlScheme_sina}"

#QQ
defaults write $rootpath/$normalpath/${TARGET}/Info.plist "CFBundleURLTypes" -array-add '<dict><key>CFBundleTypeRole</key><string>Editor</string><key>CFBundleURLName</key><string>QQ</string><key>CFBundleURLSchemes</key><array><string>'${ximi_UrlScheme_qq}'</string></array></dict>'
#微信
defaults write $rootpath/$normalpath/${TARGET}/Info.plist "CFBundleURLTypes" -array-add '<dict><key>CFBundleTypeRole</key><string>Editor</string><key>CFBundleURLName</key><string>weixin</string><key>CFBundleURLSchemes</key><array><string>'${ximi_UrlScheme_wx}'</string></array></dict>'

#新浪
defaults write $rootpath/$normalpath/${TARGET}/Info.plist "CFBundleURLTypes" -array-add '<dict><key>CFBundleTypeRole</key><string>Editor</string><key>CFBundleURLName</key><string>sina</string><key>CFBundleURLSchemes</key><array><string>'${ximi_UrlScheme_sina}'</string></array></dict>'

echo "****************完成替换share appkey"
defaults read $rootpath/$normalpath/${TARGET}/Info.plist

#exit
#改变某个key的value
#环境模式 
target_themeinfo_path="$rootpath/$normalpath/${TARGET}/Resource/ThemeStyle.plist"
defaults write $rootpath/$normalpath/${TARGET}/Resource/ThemeStyle.plist OfflineMode "${app_evn}"
defaults write $rootpath/$normalpath/${TARGET}/Resource/ThemeStyle.plist YZBBSName "${bbsName}"
defaults write $rootpath/$normalpath/${TARGET}/Resource/ThemeStyle.plist YZSegMent "${navigationBarColor}"
defaults write $rootpath/$normalpath/${TARGET}/Resource/ThemeStyle.plist YZBaseURL "${baseurl}"
defaults write $rootpath/$normalpath/${TARGET}/Resource/ThemeStyle.plist YZBasePath "${urlpath}"
defaults write $rootpath/$normalpath/${TARGET}/Resource/ThemeStyle.plist YZChannel "${channel}"
#defaults write $rootpath/$normalpath/${TARGET}/Resource/ThemeStyle.plist kAPP_DOWNLOAD_URL "${appdownloadurl}"
/usr/libexec/PlistBuddy -c "Set :JpushAppKey ${jpush_appkey}" ${target_themeinfo_path}
#/usr/libexec/PlistBuddy -c "Set :JpushAppSecret ${jpush_appsecret}" ${target_themeinfo_path}
/usr/libexec/PlistBuddy -c "Set :AppBundleID ${bundleid}" ${target_themeinfo_path}

#写入分享的各个平台的sharekey
defaults write $rootpath/$normalpath/${TARGET}/Resource/ThemeStyle.plist ShareAppkeySina "${sharekey_sina}"
defaults write $rootpath/$normalpath/${TARGET}/Resource/ThemeStyle.plist ShareAppkeyTecent "${sharekey_qq}"
defaults write $rootpath/$normalpath/${TARGET}/Resource/ThemeStyle.plist ShareAppkeyWechat "${shareKey_wechat}"
defaults write $rootpath/$normalpath/${TARGET}/Resource/ThemeStyle.plist ShareAppSecretSina "${shareSecret_sina}"
defaults write $rootpath/$normalpath/${TARGET}/Resource/ThemeStyle.plist ShareAppSecretWechat "${shareSecret_wechat}"
defaults write $rootpath/$normalpath/${TARGET}/Resource/ThemeStyle.plist ShareAppSecretTecent "${shareSecret_qq}"
defaults write $rootpath/$normalpath/${TARGET}/Resource/ThemeStyle.plist ShareAppRedirectUriSina "${shareRedirecturi_sina}"
defaults write $rootpath/$normalpath/${TARGET}/Resource/ThemeStyle.plist APPKEY "${appkey}"

#判断是否是三体 如果是三体 则开启游族登录
if [[ "${baseurl}" =~ "www.3body.com" ]]
then
defaults write $rootpath/$normalpath/${TARGET}/Resource/ThemeStyle.plist YouZuLogin "1"
else
defaults write $rootpath/$normalpath/${TARGET}/Resource/ThemeStyle.plist YouZuLogin "0"
fi

defaults read $rootpath/$normalpath/${TARGET}/Resource/ThemeStyle.plist

XCP_PATH="${rootpath}/${normalpath}/${TARGET}.xcodeproj"

uuid=`grep UUID -A1 -a ${pathforprovision} | grep -io "[-A-Z0-9]\{36\}"`
#uuid=`./mpParse -f ${mp} -o uuid`
echo "Found UUID ${uuid}"
output="${rootpath}/Library/MobileDevice/Provisioning Profiles/${uuid}.mobileprovision"
cp "${pathforprovision}" "${output}"

PROVISIONING_PROFILE="${uuid}"
echo "path = ${XCP_PATH}"
echo "project target = ${TARGET}"
echo "provisioning profile = ${PROVISIONING_PROFILE}"
echo "code signing identity = ${CODE_SIGNING_IDENTITY}"

#if [ -d "${XCP_PATH}" ]
#then

PROJ_PATH="${XCP_PATH%/*}/"

ARCHIVE_PATH="$rootpath/${basepath}/$name/${TARGET}.xcarchive"
EXPORT_PATH="$rootpath/${basepath}/$name/${name}.ipa"
echo $1>>"$rootpath/${basepath}/${name}/require.txt"
echo $2>>"$rootpath/${basepath}/${name}/require.txt"
echo $3>>"$rootpath/${basepath}/${name}/require.txt"
echo $4>>"$rootpath/${basepath}/${name}/require.txt"
echo $5>>"$rootpath/${basepath}/${name}/require.txt"
echo $6>>"$rootpath/${basepath}/${name}/require.txt"
echo $7>>"$rootpath/${basepath}/${name}/require.txt"
echo $8>>"$rootpath/${basepath}/${name}/require.txt"
echo $9>>"$rootpath/${basepath}/${name}/require.txt"
echo ${10}>>"$rootpath/${basepath}/${name}/require.txt"
echo ${11}>>"$rootpath/${basepath}/${name}/require.txt"
echo ${12}>>"$rootpath/${basepath}/${name}/require.txt"
echo ${13}>>"$rootpath/${basepath}/${name}/require.txt"
echo ${14}>>"$rootpath/${basepath}/${name}/require.txt"
echo ${15}>>"$rootpath/${basepath}/${name}/require.txt"
echo ${16}>>"$rootpath/${basepath}/${name}/require.txt"
echo ${17}>>"$rootpath/${basepath}/${name}/require.txt"
echo ${18}>>"$rootpath/${basepath}/${name}/require.txt"
echo ${19}>>"$rootpath/${basepath}/${name}/require.txt"
echo ${20}>>"$rootpath/${basepath}/${name}/require.txt"
echo ${21}>>"$rootpath/${basepath}/${name}/require.txt"
echo "archive path = ${ARCHIVE_PATH}"
echo "export path = ${EXPORT_PATH}"

security unlock-keychain -p 11111111 /Users/youzu/Library/Keychains/login.keychain
xcodebuild -project "${XCP_PATH}" -arch "armv7s" -arch "armv7" -arch "arm64" -configuration "Release" -target "${TARGET}" -sdk iphoneos clean build
xcodebuild archive -project "${XCP_PATH}" -scheme "${TARGET}" -archivePath "${ARCHIVE_PATH}" CODE_SIGN_IDENTITY="${CODE_SIGNING_IDENTITY}" PROVISIONING_PROFILE="${PROVISIONING_PROFILE}" > $rootpath/${basepath}/$name/archive.log 
xcodebuild -exportArchive -exportFormat ipa -archivePath "${ARCHIVE_PATH}" -exportPath "${EXPORT_PATH}" -exportProvisioningProfile "${provisionname}" > $rootpath/${basepath}/$name/archive2.log
#script -q -t 0 $rootpath/${basepath}/$name/archive3.log /usr/local/bin/node /Users/youzu/zhengtao/packageios/scp.js "${1}" "${2}"



