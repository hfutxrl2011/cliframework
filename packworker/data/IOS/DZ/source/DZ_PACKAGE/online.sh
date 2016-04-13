#!/bin/sh
#***********************************************************************************
# Usage
# 终端直接执行
# 1、cd 到脚本所在的目录
# 2、终端执行以下脚本
# ./offline.sh "zhongyuzaixian" "中羽在线" "com.badmintoncn.bbs" "1.8" "/Users/Jessise/Desktop/zhongyulogo.png" "/Users/Jessise/Desktop/zhongyulaunchimg.png" "中羽在线社区" "234,65,81" "www.baidu.com" "/path" "appStore" "69cad20f1a3717f39e0524f31942b725" "3552514107" "b234a863f8cfb50e4ec7c47ca0babba9" "https://api.weibo.com/oauth2/default.html" "wxb05003635a752c53" "1bf2e7110a83d2b1d2ce71c11f18ca36" "1104768684" "QBWUohHz82fpd55h" "ebc7378665fd22bd8a2beac2" "7c6d9cecc9f0e606d5130cba"
#***********************************************************************************
# 1、app_taskid ：打包的任务id
# 2、app_name ：应用的名称，打包ipa的时候命名所用
# 3、app_bundleid ：应用的bundle id，必须跟描述文件 证书保持一致
# 4、app_version ：应用的版本号，上架appStore时候版本
# 5、app_logopath ：应用图标
# 6、app_launchImgPath ：应用启动图
# 7、app_navigationBarColor ：应用的导航背景颜色
# 8、app_baseurl ：baseurl
# 9、app_urlpath : basepath
# 10、app_urlpath : basepath
# 11、app_channel : 渠道包名
# 12、app_appkey : appkey
# 13、app_sharekey_sina : app_sharekey_sina
# 14、app_shareSecret_sina : app_shareSecret_sina
# 15、app_shareRedirecturi_sina : app_shareRedirecturi_sina
# 16、app_shareKey_wechat : app_shareKey_wechat
# 17、app_shareSecret_wechat : app_shareSecret_wechat
# 18、app_sharekey_qq : app_sharekey_qq
# 19、app_shareSecret_qq : app_shareSecret_qq
# 20、app_jpush_appkey : jpush的appkey
# 21、app_jpush_appsecret : jpush的secret

#***********************************************************************************


app_taskid="${1}"
app_name="${2}"
app_bundleid="${3}"
app_version="${4}"
app_logopath="${5}"
app_launchImgPath="${6}"
app_bbsname="${7}"
app_navigationBarColor="${8}"
app_baseurl="${9}"
app_urlpath="${10}"
app_channel="${11}"
app_appkey="${12}"
app_sharekey_sina="${13}"
app_shareSecret_sina="${14}"
app_shareRedirecturi_sina="${15}"
app_shareKey_wechat="${16}"
app_shareSecret_wechat="${17}"
app_sharekey_qq="${18}"
app_shareSecret_qq="${19}"
app_jpush_appkey="${20}"
app_jpush_appsecret="${21}"

#当前目录路径
#current_path=$(pwd)
current_path=$(pwd)
echo "current_path是 ${current_path}"
#母包路径
base_ipafile_name="Clan"
base_ipafile_path="${current_path}/Clan.ipa"
#要输出的文件名字
cdate=`date '+%Y-%m-%d_%H:%M:%S'`
#output_ipafile_name="${app_name}_${cdate}.ipa"
output_ipafile_name="${app_taskid}.ipa"
#输出文件的目录
output_path=${current_path}/packages/${app_taskid}
ditto ${base_ipafile_path} ${output_path}/Clan.ipa
#if [ ! -d "${output_path}"]; then
#    mkdir "${output_path}"
#fi
#先清除掉遗留文件
rm -rf ${output_path}/Clan.ipa
rm -rf ${output_path}/${output_ipafile_name}
rm -rf ${output_path}/Payload
#传进来的母包拷贝到该应用的目录下
#ditto ${base_ipafile_path} ${output_path}/Clan.ipa
#cp -rf ${project_path} ${output_path}/${base_ipafile_name}.ipa

#distribution_name="iPhone Distribution: Zhejiang walan cultural creative Co. Ltd. (K9N5G9J89K)"
#rm -rf ./Payload
#解压母包
unzip -d ${output_path} ${base_ipafile_path} > ${output_path}/unzip.log


##################################################替换资源文件 start ####################################

iconname_array=("AppIcon29x29@2x.png" "AppIcon29x29@3x.png" "AppIcon40x40@2x.png" "AppIcon40x40@3x.png" "AppIcon60x60@2x.png" "AppIcon60x60@3x.png")
iconsize_array=("58 58" "87 87" "80 80" "120 120" "120 120" "180 180")

#图片资源文件路径
images_xcassets_path="${output_path}/Payload/${base_ipafile_name}.app"
echo "=======images_xcassets_path : ${images_xcassets_path}=======";

for ((i=0;i<${#iconname_array[@]};++i)); do
cp ${app_logopath} ${images_xcassets_path}/${iconname_array[i]}
sips -s format png ${images_xcassets_path}/${iconname_array[i]} --out ${images_xcassets_path}/${iconname_array[i]}
sips -z ${iconsize_array[i]} ${images_xcassets_path}/${iconname_array[i]}
done

#改变启动图尺寸并移动启动图
launchname_array=("LaunchImage-700-568h@2x.png" "LaunchImage-700@2x.png" "LaunchImage-800-667h@2x.png" "LaunchImage-800-Portrait-736h@3x.png")
launchsize_array=("960 640" "1136 640" "1334 750" "2208 1242")
for ((i=0;i<${#launchname_array[@]};++i)); do
launch_image_name_png="${images_xcassets_path}"/"${launchname_array[i]}"

cp ${app_launchImgPath}  "${launch_image_name_png}"
sips -s format png  "${launch_image_name_png}" --out  "${launch_image_name_png}"
sips -z ${launchsize_array[i]}   "${launch_image_name_png}"
done

path_infoplist="${images_xcassets_path}/Info.plist"
#应用名称的修改
/usr/libexec/PlistBuddy -c "Set :CFBundleDisplayName ${app_name}" ${path_infoplist}
#应用版本号
/usr/libexec/PlistBuddy -c "Set :CFBundleShortVersionString ${app_version}" ${path_infoplist}

#第三方平台的key
#QQ URL Scheme
ximi_UrlScheme_qq=`echo 'ibase=10;obase=16;'${app_sharekey_qq}''|bc`
ximi_UrlScheme_qq_len=`echo ${#ximi_UrlScheme_qq}`
if [[ "${ximi_UrlScheme_qq_len}" == "8" ]]
then
ximi_UrlScheme_qq="QQ${ximi_UrlScheme_qq}"
else
ximi_UrlScheme_qq="QQ0${ximi_UrlScheme_qq}"
fi

#微信URL scheme
ximi_UrlScheme_wx=${app_shareKey_wechat}
#新浪URL scheme
ximi_UrlScheme_sina="wb${app_sharekey_sina}"

#QQ
defaults write ${path_infoplist} "CFBundleURLTypes" -array-add '<dict><key>CFBundleTypeRole</key><string>Editor</string><key>CFBundleURLName</key><string>QQ</string><key>CFBundleURLSchemes</key><array><string>'${ximi_UrlScheme_qq}'</string></array></dict>'
#微信
defaults write ${path_infoplist} "CFBundleURLTypes" -array-add '<dict><key>CFBundleTypeRole</key><string>Editor</string><key>CFBundleURLName</key><string>weixin</string><key>CFBundleURLSchemes</key><array><string>'${ximi_UrlScheme_wx}'</string></array></dict>'

#新浪
defaults write ${path_infoplist} "CFBundleURLTypes" -array-add '<dict><key>CFBundleTypeRole</key><string>Editor</string><key>CFBundleURLName</key><string>sina</string><key>CFBundleURLSchemes</key><array><string>'${ximi_UrlScheme_sina}'</string></array></dict>'

defaults read ${path_infoplist}

path_appconfig_plist="${images_xcassets_path}/ThemeStyle.plist"
/usr/libexec/PlistBuddy -c "Set :YZBBSName ${app_bbsname}" ${path_appconfig_plist}
/usr/libexec/PlistBuddy -c "Set :YZSegMent ${app_navigationBarColor}" ${path_appconfig_plist}
/usr/libexec/PlistBuddy -c "Set :YZBaseURL ${app_baseurl}" ${path_appconfig_plist}
/usr/libexec/PlistBuddy -c "Set :YZBasePath ${app_urlpath}" ${path_appconfig_plist}
/usr/libexec/PlistBuddy -c "Set :YZChannel ${app_channel}" ${path_appconfig_plist}
/usr/libexec/PlistBuddy -c "Set :JpushAppKey ${app_jpush_appkey}" ${path_appconfig_plist}
/usr/libexec/PlistBuddy -c "Set :JpushAppSecret ${app_jpush_appsecret}" ${path_appconfig_plist}
/usr/libexec/PlistBuddy -c "Set :AppBundleID ${app_bundleid}" ${path_appconfig_plist}
/usr/libexec/PlistBuddy -c "Set :ShareAppkeySina ${app_sharekey_sina}" ${path_appconfig_plist}
/usr/libexec/PlistBuddy -c "Set :ShareAppkeyTecent ${app_sharekey_qq}" ${path_appconfig_plist}
/usr/libexec/PlistBuddy -c "Set :ShareAppkeyWechat ${app_shareKey_wechat}" ${path_appconfig_plist}
/usr/libexec/PlistBuddy -c "Set :ShareAppSecretSina ${app_shareSecret_sina}" ${path_appconfig_plist}
/usr/libexec/PlistBuddy -c "Set :ShareAppSecretWechat ${app_shareSecret_wechat}" ${path_appconfig_plist}
/usr/libexec/PlistBuddy -c "Set :ShareAppSecretTecent ${app_shareSecret_qq}" ${path_appconfig_plist}
/usr/libexec/PlistBuddy -c "Set :ShareAppRedirectUriSina ${app_shareRedirecturi_sina}" ${path_appconfig_plist}
/usr/libexec/PlistBuddy -c "Set :APPKEY ${app_appkey}" ${path_appconfig_plist}
/usr/libexec/PlistBuddy -c "Set :OfflineMode 0" ${path_appconfig_plist}

#判断是否是三体 如果是三体 则开启游族登录
if [[ "${baseurl}" =~ "www.3body.com" ]]
then
/usr/libexec/PlistBuddy -c "Set :YouZuLogin 1" ${path_appconfig_plist}
else
/usr/libexec/PlistBuddy -c "Set :YouZuLogin 0" ${path_appconfig_plist}
fi
##################################################替换资源文件 end ####################################

path_provision=${current_path}/k_commonTest_inhouse_pro.mobileprovision
CODE_SIGNING_IDENTITY="iPhone Distribution: Su zhou Zhengyou Network Technology Co. Ltd."

rm -rf ${output_path}/Payload/${base_ipafile_name}.app/_CodeSignature
cp ${path_provision} ${output_path}/Payload/${base_ipafile_name}.app/embedded.mobileprovision
/usr/libexec/PlistBuddy -x -c "print :Entitlements " /dev/stdin <<< $(security cms -D -i ${output_path}/Payload/*.app/embedded.mobileprovision) > entitlements.plist
#input project build setttings [Code Signing Resource Rules Path] = $(SDKROOT)/ResourceRules.plist
/usr/bin/codesign -f -s "${CODE_SIGNING_IDENTITY}" --resource-rules ${output_path}/Payload/${base_ipafile_name}.app/ResourceRules.plist --entitlements entitlements.plist ${output_path}/Payload/${base_ipafile_name}.app

cd ${output_path}

zip -qr ./${output_ipafile_name} ./Payload
rm -rf ${output_path}/Payload
cd ${current_path}
echo "---------rm entitlements.plist"
rm  ./entitlements.plist

rm -rf ${output_path}/require.txt
paras_path=${output_path}/require.txt
echo ${1}>>"${paras_path}"
echo ${2}>>"${paras_path}"
echo ${3}>>"${paras_path}"
echo ${4}>>"${paras_path}"
echo ${5}>>"${paras_path}"
echo ${6}>>"${paras_path}"
echo ${7}>>"${paras_path}"
echo ${8}>>"${paras_path}"
echo ${9}>>"${paras_path}"
echo ${10}>>"${paras_path}"
echo ${11}>>"${paras_path}"
echo ${12}>>"${paras_path}"
echo ${13}>>"${paras_path}"
echo ${14}>>"${paras_path}"
echo ${15}>>"${paras_path}"
echo ${16}>>"${paras_path}"
echo ${17}>>"${paras_path}"
echo ${18}>>"${paras_path}"
echo ${19}>>"${paras_path}"
echo ${20}>>"${paras_path}"
echo ${21}>>"${paras_path}"



