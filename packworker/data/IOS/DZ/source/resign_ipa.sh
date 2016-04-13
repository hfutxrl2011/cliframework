#!/bin/sh
#***********************************************************************************
# Usage
# 终端直接执行
# 1、cd 到脚本所在的目录
# 2、终端执行以下脚本
# ./resign_ipa.sh "001" "小香网" "com.mooxiang.jingyou" "2.0" "47" "/Users/Jessise/Desktop/bigapp02_cert.p12" "123456" "/Users/Jessise/Desktop/个人证书重签名/dis_xiaoxiangwang.mobileprovision" "/Users/Jessise/Desktop/tuisong.ipa"
#***********************************************************************************
# 1、mac_password ：MAC电脑的密码，导入证书到钥匙链的时候用到
# 2、app_name ：应用的名称，打包ipa的时候命名所用
# 3、app_bundleid ：应用的bundle id，必须跟描述文件 证书保持一致
# 4、app_version ：应用的版本号，上架appStore时候版本
# 5、app_build_version ：应用的build版本号，每个version对应的build号都是依次累加的 不可重复，否则上传store失败
# 6、cert_path ：发布证书p12文件的路径，一定注意是.p12后缀
# 7、cert_secretkey ：发布证书p12文件导出时候的秘钥，这里做导入p12文件到loginkeychain的时候用到
# 8、cert_provision_path ：描述文件路径，校验证书和bundleid的一个文件，打包必须参数
# 9、project_path : 母包路径
#***********************************************************************************

#mac_password -> MAC电脑的密码，导入证书到钥匙链的时候用到
mac_password="${1}"

#app_name -> 应用的名称，打包ipa的时候命名所用
app_name="${2}"

#app_bundleid -> 应用的bundle id，必须跟描述文件 证书保持一直
app_bundleid="${3}"

#app_version -> 应用的版本号，上架appStore时候版本
app_version="${4}"

#app_build_version -> 应用的build版本号，每个version对应的build号都是依次累加的 不可重复，否则上传store失败
app_build_version="${5}"

#cert_path -> 发布证书p12文件的路径，一定注意是.p12后缀
cert_path="${6}"

#cert_secretkey -> 发布证书p12文件导出时候的秘钥，这里做导入p12文件到loginkeychain的时候用到
cert_secretkey="${7}"

#cert_provision_path -> 描述文件路径，校验证书和bundleid的一个文件，打包必须参数
cert_provision_path="${8}"

#project_path -> 母包路径
project_path="${9}"

#channel -> 打包渠道
#channel="${11}"

#当前目录路径
#current_path=$(pwd)
current_path=$(pwd)
echo "current_path是 ${current_path}"
#母包
base_ipafile_name="Clan"
#要输出的文件名字
output_ipafile_name="${app_name}.ipa"
#输出文件的目录
output_path=${current_path}/packages/${app_name}
#先清除掉遗留文件
rm -rf ${output_path}/${base_ipafile_name}.ipa
rm -rf ${output_path}/${output_ipafile_name}.ipa
#传进来的母包拷贝到该应用的目录下
ditto ${project_path} ${output_path}/${base_ipafile_name}.ipa
#cp -rf ${project_path} ${output_path}/${base_ipafile_name}.ipa

#distribution_name="iPhone Distribution: Zhejiang walan cultural creative Co. Ltd. (K9N5G9J89K)"
#rm -rf ./Payload
#解压母包
unzip -d ${output_path} ${output_path}/${base_ipafile_name}.ipa > unzip.log


##################################################替换资源文件 start ####################################
target_info_path="${output_path}/Payload/${base_ipafile_name}.app/Info.plist"
appconfig_path="${output_path}/Payload/${base_ipafile_name}.app/ThemeStyle.plist"
pushconfig_path="${output_path}/Payload/${base_ipafile_name}.app/PushConfig.plist"
#列举所有codesigning
#security find-identity -v -p codesigning

#安装证书
security import ${cert_path} -T /usr/bin/codesign -k ~/Library/Keychains/login.keychain -P ${cert_secretkey}
#把描述文件写入plist文件
security cms -D -i ${cert_provision_path} > tmp.plist
#取到描述文件对应的bundle id
pro_identifer=$(/usr/libexec/PlistBuddy -c 'Print :Entitlements:application-identifier' tmp.plist)
#根据bundleid 取出组织名称例如“FAK9X54DC6“
org_identifer=`basename ${pro_identifer}| awk -F'.' '{print $1}'`
rm -rf tmp.plist
#最终获取钥匙串中的证书的名字
cert_distribution_name=$(security find-certificate -c ${org_identifer} -Z login.keychain | awk -F'<blob>=' '{if($1 =="    \"alis\""){print $2}}' | awk -F'"' '{print $2}')
echo "****************************************${cert_distribution_name}"

##################################################替换资源文件 start ####################################
#改变bundle id
/usr/libexec/PlistBuddy -c "Set :CFBundleIdentifier ${app_bundleid}" ${target_info_path}

#改变version
/usr/libexec/PlistBuddy -c "Set :CFBundleShortVersionString ${app_version}" ${target_info_path}

#改变build version
/usr/libexec/PlistBuddy -c "Set :CFBundleVersion ${app_build_version}" ${target_info_path}

#改变jpushkey
temp_pushkey=$(/usr/libexec/PlistBuddy -c "Print :JpushAppKey" ${appconfig_path})
/usr/libexec/PlistBuddy -c "Set :APP_KEY ${temp_pushkey}" ${pushconfig_path}
##################################################替换资源文件 end ####################################

rm -rf ${output_path}/Payload/${base_ipafile_name}.app/_CodeSignature
cp ${cert_provision_path} ${output_path}/Payload/${base_ipafile_name}.app/embedded.mobileprovision
/usr/libexec/PlistBuddy -x -c "print :Entitlements " /dev/stdin <<< $(security cms -D -i ${output_path}/Payload/*.app/embedded.mobileprovision) > entitlements.plist

#input project build setttings [Code Signing Resource Rules Path] = $(SDKROOT)/ResourceRules.plist
#/usr/bin/codesign -f -s "${cert_distribution_name}" --resource-rules ${output_path}/Payload/${base_ipafile_name}.app/ResourceRules.plist --entitlements entitlements.plist ${output_path}/Payload/${base_ipafile_name}.app

/usr/bin/codesign -f -s "${cert_distribution_name}" --no-strict --entitlements entitlements.plist ${output_path}/Payload/${base_ipafile_name}.app


cd ${output_path}
zip -qr ./${output_ipafile_name} ./Payload
echo "---------rm Payload"
rm -rf ${output_path}/Payload
cd ${current_path}
echo "---------rm entitlements.plist"
rm  ./entitlements.plist
mv ./unzip.log ${output_path}
paras_path=${output_path}/${app_name}.txt
echo $1>>${paras_path}
echo $2>>${paras_path}
echo $3>>${paras_path}
echo $4>>${paras_path}
echo $5>>${paras_path}
echo $6>>${paras_path}
echo $7>>${paras_path}
echo $8>>${paras_path}
echo $9>>${paras_path}
echo "done"


