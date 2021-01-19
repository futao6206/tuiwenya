<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/15
 * Time: 5:32 PM
 * 后端入口文件
 */

//定义常量
use Frame\Frame;

define("DS",DIRECTORY_SEPARATOR);
define("ROOT_PATH",getcwd().DS);//根目录
define("APP_PATH",ROOT_PATH."Admin".DS);//应用目录

//包含框架初始类文件
require_once(ROOT_PATH . "Frame/Frame.class.php");
//框架初始化 命名空间
Frame::run();




