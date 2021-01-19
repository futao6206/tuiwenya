<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/19
 * Time: 6:30 PM
 */

namespace Frame\Libs;
use \Frame\Vendor\Smarty;

abstract class baseController {
    protected $smarty = NULL;

    public function __construct()
    {
        $this->initSmarty();//Smarty对象初始化

        //1、后台2、未登录3、当前不是privilege控制器
        if (PLAT=="Admin"&&!(isset($_GET['c'])&&$_GET['c']=='privilege')) {
            $this->denyAccess();//登录判断
        }
    }

    private function initSmarty() {
        //创建Smarty类的对象
        $smarty = new Smarty();
        //smarty配置
        $smarty->left_delimiter = "{%";//左定界符
        $smarty->right_delimiter = "%}";
        $smarty->setTemplateDir(VIEW_PATH);//设置视图文件目录
        $smarty->setCompileDir(ROOT_PATH.DS."tmp".DS);//设置编译目录 sys_get_temp_dir()操作系统临时目录 存放编译后文件 /var/tmp/
        //给$smarty属性赋值
        $this->smarty = $smarty;
    }

    //用户权限验证
    protected function denyAccess() {
        if (!isset($_SESSION['user']['username'])) {
            $this->jump("","?c=privilege&a=login",0);
        }
    }

    protected function jump($message,$url='?',$time=3) {//只在子类中调用
//        header("refresh:{$time};url={$url}");
        $this->smarty->assign("message",$message);
        $this->smarty->assign("url",$url);
        $this->smarty->assign("time",$time);
        $this->smarty->display("Public/jump.html");
        die();
    }
}











