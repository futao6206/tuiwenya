<?php

//文件上传
namespace Frame\Vendor;

final class Uploader {
	//设定属性：保存允许上传的文件类型
	private static $types = array('text/plain');

	//修改类型方法
	public static function setTypes(array $types = array()){
		//判定是否为空
		if(!empty($types)) self::$types = $types;
	}

	//单文件上传
	public static $error;	//记录上传过程中出现的错误信息
	public static function uploadOne(array $file,string $path=UPLOAD_PATH,int $max = 50000000){
		//判定文件有效性
		if(!isset($file['error']) || count($file) != 5){
			self::$error = '错误的上传文件！';
			return false;
		}

		//路径判定
		if(!is_dir($path)){
			self::$error = '存储路径不存在！';
			return false;
		}

		//判定文件是否正确上传
		switch($file['error']){
			case 1:
			case 2:
				self::$error = '文件超过服务器允许大小！';
				return false;
			case 3:
				self::$error = '文件只有部分被上传！';
				return false;
			case 4:
				self::$error = '没有选中要上传的文件！';
				return false;
			case 6:
			case 7:
				self::$error = '服务器错误！';
				return false;
		}

		//判定文件类型
		if(!in_array($file['type'],self::$types)){
			self::$error = '当前上传的文件类型不允许！';
			return false;
		}

		//判定业务大小
		if($file['size'] > $max){
			self::$error = '当前上传的文件超过允许的大小！当前允许的大小是：' . (string)($max / 1000000) . 'M';
			return false;
		}

		//文件名
		$filename = $file['name'];

		//移动上传的临时文件到指定目录
		if(move_uploaded_file($file['tmp_name'],$path.$filename)){
			//成功
			return $path.$filename;
		}else{
			//失败
			self::$error = '文件移动失败！';
			return false;
		}
	}

	public function uploadAll(array $files,string $path=UPLOAD_PATH,int $max = 50000000) {
        $filePaths = array();
        foreach ($files as $file) {
            if($filePath = Uploader::uploadOne($file)){
                $filePaths[] = $filePath;
            } else {
                //上传失败 删除可能已经上传的文件
                @unlink($filePath);
            }
        }
        return $filePaths;
    }
}