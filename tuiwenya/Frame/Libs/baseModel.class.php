<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/18
 * Time: 4:37 PM
 * 声明命名空间 用来代替Db类
 */

namespace Frame\Libs;
use \Frame\Vendor\PDOWrapper;

//定义抽象的基础模型类

abstract class baseModel {
    //受保护的$pdo对象属性
    protected $pdo = NULL;
    //私有的的静态的保存不同模型类对象的数组属性
    private static $arrModelObj = array();

    //构造方法
    public function __construct()
    {
        $this->pdo = new PDOWrapper();
    }

    //公共的静态创建模型类对象的方法
    public static function getInstance() {
        //获取静态化方式调用的类名
        $modelClassName = get_called_class();
        //判断当前模型类对象是否存在 $arrModelObj['\Home\Model\StudentModel'] = 模型类的对象
        if (!isset(self::$arrModelObj[$modelClassName])) {//判断当前对象是否为NULL
            self::$arrModelObj[$modelClassName] = new $modelClassName();
        }
        return self::$arrModelObj[$modelClassName];
    }
}