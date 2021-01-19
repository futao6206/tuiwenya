<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/16
 * Time: 10:01 AM
 * 声明命名空间
 */

namespace Frame;//与目录一致方便加载

//定义最终框架初始类
final class Frame {
    //初始化方法
    public static function run() {
        self::initCharset();     //初始化字符集设置
        self::initConfig();      //初始化配置文件
        self::initRoute();       //初始化路由参数
        self::initConst();       //初始化常量目录设置
        self::initAutoLoad();    //初始化类的自动加载
        self::initDispatch();    //初始化请求分发
    }

    //私有的静态的字符集设置
    private  static  function initCharset() {
        header("content-type:text/html;charset=utf-8");
        define('UTF32_BIG_ENDIAN_BOM', chr(0x00) . chr(0x00) . chr(0xFE) . chr(0xFF));
        define('UTF32_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE) . chr(0x00) . chr(0x00));
        define('UTF16_BIG_ENDIAN_BOM', chr(0xFE) . chr(0xFF));
        define('UTF16_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE));
        define('UTF8_BOM', chr(0xEF) . chr(0xBB) . chr(0xBF));
        //开启session会话
        session_start();
    }

    //私有的静态的初始化配置文件
    private static function initConfig() {
        //配置文件路径eg：./Home/Conf/Config.php
        $GLOBALS['config'] = require_once(APP_PATH . "Conf" . DS . "Config.php");
    }

    //私有的静态的初始化路由参数
    private static function initRoute() {
        $p = $GLOBALS['config']['default_platform'];   //平台参数
        $c = isset($_GET['c']) ? $_GET['c'] : $GLOBALS['config']['default_controller']; //控制器参数
        $a = isset($_GET['a']) ? $_GET['a'] : $GLOBALS['config']['default_action'];     //动作参数
        define("PLAT",$p);
        define("CONTROLLER",$c);
        define("ACTION",$a);
    }

    private static function initConst() {
        //eg: ./Home/View/
        define("VIEW_PATH",APP_PATH."View".DS);
        define("FRAME_PATH",ROOT_PATH."Frame".DS);
        define("UPLOAD_PATH",ROOT_PATH."tmp".DS);
    }

    private static function initAutoLoad() {
        spl_autoload_register(function ($className){
            //将空间中的类名，转换成真实的类文件路径 eg：\Home\Controller\StudentController -> ./Home/Controller/StudentController.class.php
            $filename = ROOT_PATH.str_replace("\\",DS,$className).".class.php";
            //如果类文件存在 包含
            if (file_exists($filename)) require_once($filename);
        });
    }

    //创建哪个控制器类的对象？调用控制器对象的哪个方法
    private static function initDispatch() {
        //构建控制器类名 \Home\Controller\StudentController
        $className = "\\".PLAT."\\"."Controller"."\\".CONTROLLER."Controller";
        //创建控制器类的对象
        $controllerObj = new $className();
        //调用控制器对象的方法
        $actionName = ACTION;
        $controllerObj->$actionName();//index()
    }
}