<?php
require_once("Image.class.php");
$gd_info  = 0;
if(function_exists("gd_info"))
{
	$gd_info = 1;
}
elseif(class_exists("Imagick", false))
{
				$gd_info = 2;				
}
if($gd_info === 0){
	exit(111);
}
$Image = new Image($gd_info);
if(@$Image->get_handle())
{
	@$Image->open("default.png");				
	@$Image->thumb("200","200")->save("default200x200.png");//Ëõ·ÅÍ¼Æ¬
}