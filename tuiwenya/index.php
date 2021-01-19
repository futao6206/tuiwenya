<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/15
 * Time: 5:32 PM
 * 前端入口文件
 */

use Frame\Frame;
//定义常量
define("DS",DIRECTORY_SEPARATOR);
define("ROOT_PATH",getcwd().DS);//根目录
define("APP_PATH",ROOT_PATH."Home".DS);//应用目录

//包含框架初始类文件
require_once(ROOT_PATH . "Frame/Frame.class.php");
//框架初始化 命名空间
Frame::run();


////创建PDOWrapper类的对象
//$pdo = new \Frame\Vendor\PDOWrapper();
//$sql = "SELECT * FROM novel";
//$arrs = $pdo->rowCount($sql);
//print_r($arrs);

//require_once ("./Frame/Vendor/Smarty.class.php");

//echo sys_get_temp_dir();